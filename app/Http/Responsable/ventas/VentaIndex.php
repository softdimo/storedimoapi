<?php

namespace App\Http\Responsable\ventas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class VentaIndex implements Responsable
{
    public function toResponse($request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }
        
        try {
            // --- 1. Subconsulta para sumar la ganancia total por ID de Venta ---
            $gananciaTotalSubquery = DB::table('venta_productos')
                // ->select('id_venta', DB::raw('SUM(ganancia_venta) as ganancia_total_venta'))
                ->select(
                    'id_venta',
                    DB::raw("CONCAT('$', FORMAT(SUM(ganancia_venta), 0, 'de_DE')) as ganancia_total_venta"),
                    DB::raw('SUM(ganancia_venta) as ganancia_total_sin_formato') // También útil para cálculos posteriores
                )
                ->groupBy('id_venta');

            // --- 2. Consulta principal de Ventas, uniéndola con la ganancia total ---
            $ventas = Venta::query()
                ->leftjoin('tipos_pago', 'tipos_pago.id_tipo_pago', '=', 'ventas.id_tipo_pago')
                ->leftjoin('personas', 'personas.id_persona', '=', 'ventas.id_cliente')
                ->leftjoin('empresas', 'empresas.id_empresa', '=', 'ventas.id_empresa')
                // **Nuevo:** Left Join a la subconsulta de ganancia total
                ->leftjoinSub($gananciaTotalSubquery, 'ganancias', function ($join) {
                    $join->on('ganancias.id_venta', '=', 'ventas.id_venta');
                })
                // Se removieron joins a productos y venta_productos que causaban duplicación
                ->select(
                    'ventas.id_venta',
                    'fecha_venta',
                    'descuento',
                    'subtotal_venta',
                    'total_venta',
                    DB::raw("CONCAT('$', FORMAT(total_venta, 0, 'de_DE')) as total_venta_index"),
                    'tipos_pago.id_tipo_pago',
                    'tipo_pago',
                    // Campos de Cliente
                    'personas.id_persona as id_cliente',
                    'personas.identificacion',
                    DB::raw("CONCAT(nombres_persona, ' ', apellidos_persona) AS nombres_cliente"),
                    // Otros campos
                    'ventas.id_usuario',
                    'ventas.id_estado_credito',
                    'ventas.id_tipo_cliente',
                    'empresas.id_empresa',
                    // **Nuevo:** Ganancia Total Agregada
                    'ganancias.ganancia_total_venta'
                )
                ->orderByDesc('fecha_venta')
                ->get();


            // $ventas = Venta::leftjoin('tipos_pago','tipos_pago.id_tipo_pago','=','ventas.id_tipo_pago')
            //     ->leftjoin('productos','productos.id_producto','=','ventas.id_producto')
            //     ->leftjoin('personas','personas.id_persona','=','ventas.id_cliente')
            //     ->leftjoin('empresas','empresas.id_empresa','=','ventas.id_empresa')
            //     ->leftjoin('venta_productos','venta_productos.id_venta_producto','=','ventas.id_venta')
            //     ->select(
            //         'id_venta',
            //         'fecha_venta',
            //         'descuento',
            //         'subtotal_venta',
            //         'total_venta',
            //         DB::raw("CONCAT('$', FORMAT(total_venta, 0, 'de_DE')) as total_venta_index"),
            //         'tipos_pago.id_tipo_pago',
            //         'tipo_pago',
            //         'productos.id_producto',
            //         'nombre_producto',
            //         'precio_unitario',
            //         'cantidad',
            //         'personas.id_persona as id_cliente',
            //         'personas.identificacion',
            //         DB::raw("CONCAT(nombres_persona, ' ', apellidos_persona) AS nombres_cliente"),
            //         'ventas.id_usuario',
            //         'ventas.id_estado_credito',
            //         'id_tipo_cliente',
            //         'empresas.id_empresa'
            //     )
            //     ->orderByDesc('fecha_venta')
            //     ->get();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                // ------------------------------------------------------------
                // Traer catálogos desde la BD principal en una sola consulta
                // ------------------------------------------------------------
                $usuarios = DB::connection('mysql')
                    ->table('usuarios')
                    ->select('id_usuario', DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                    ->get()
                    ->keyBy('id_usuario');

                $estados = DB::connection('mysql')
                    ->table('estados')
                    ->select('id_estado', 'estado')
                    ->get()
                    ->keyBy('id_estado');

                $tipoPersona = DB::connection('mysql')
                    ->table('tipo_persona')
                    ->select('id_tipo_persona', 'tipo_persona')
                    ->get()
                    ->keyBy('id_tipo_persona');

                // 3. Agregar nombre completo del usuario y estado sin consultas por registro
                foreach ($ventas as $venta) {
                    $venta->nombres_usuario = $usuarios[$venta->id_usuario]->nombres_usuario ?? 'Sin usuario';
                    $venta->estado = $estados[$venta->id_estado_credito]->estado ?? 'Sin estado';
                    $venta->tipo_persona = $tipoPersona[$venta->id_tipo_cliente]->tipo_persona ?? 'Sin Tipo Persona';
                    // Usar un valor por defecto si no hay productos (o no hay ganancia)
                    $venta->ganancia_total_venta = $venta->ganancia_total_venta ?? 0;
                }

                // 3. Agregar nombre completo del usuario desde la base principal
                // foreach ($ventas as $venta) {
                //     $usuario = DB::connection('mysql') // o la conexión principal que uses
                //         ->table('usuarios')
                //         ->where('id_usuario', $venta->id_usuario)
                //         ->select(DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                //         ->first();

                //     $venta->nombres_usuario = $usuario->nombres_usuario ?? 'Sin usuario';
                // }

                return response()->json($ventas);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
