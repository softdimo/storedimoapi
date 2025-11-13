<?php

namespace App\Http\Responsable\ventas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Venta;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class VentaDetalle implements Responsable
{
    protected $idVenta;

    public function __construct($idVenta)
    {
        $this->idVenta = $idVenta;
    }

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
            $ventas = Venta::leftjoin('tipos_pago','tipos_pago.id_tipo_pago','=','ventas.id_tipo_pago')
                ->leftjoin('productos','productos.id_producto','=','ventas.id_producto')
                ->leftjoin('personas','personas.id_persona','=','ventas.id_cliente')
                // ->leftjoin('estados','estados.id_estado','=','ventas.id_estado_credito')
                // ->leftjoin('tipo_persona','tipo_persona.id_tipo_persona','=','ventas.id_tipo_cliente')
                ->leftjoin('empresas','empresas.id_empresa','=','ventas.id_empresa')
                ->select(
                    'id_venta',
                    'fecha_venta',
                    'descuento',
                    'subtotal_venta',
                    'total_venta',
                    DB::raw("CONCAT('$', FORMAT(total_venta, 0, 'de_DE')) as total_venta_index"),
                    'tipos_pago.id_tipo_pago',
                    'tipo_pago',
                    'productos.id_producto',
                    'nombre_producto',
                    'precio_unitario',
                    'cantidad',
                    'personas.id_persona as id_cliente',
                    'personas.identificacion',
                    DB::raw("CONCAT(nombres_persona, ' ', apellidos_persona) AS nombres_cliente"),
                    'ventas.id_usuario',
                    'ventas.id_estado_credito',
                    // 'estado',
                    'id_tipo_cliente',
                    // 'tipo_persona',
                    'empresas.id_empresa'
                )
                ->where('id_venta', $this->idVenta)
                ->orderByDesc('fecha_venta')
                ->first();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                // 3. Agregar nombre completo del usuario desde la base principal
                if ($ventas) {
                    // Traer usuarios y estados una sola vez
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

                    // Asignar datos correspondientes
                    $ventas->nombres_usuario = $usuarios[$ventas->id_usuario]->nombres_usuario ?? 'Sin usuario';
                    $ventas->estado = $estados[$ventas->id_estado_credito]->estado ?? 'Sin estado';
                    $ventas->tipo_persona = $tipoPersona[$ventas->id_tipo_persona]->tipo_persona ?? 'Sin Tipo Persona';
                }

                // if ($ventas) {
                //     $usuario = DB::connection('mysql')
                //         ->table('usuarios')
                //         ->where('id_usuario', $ventas->id_usuario)
                //         ->select(DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                //         ->first();
                
                //     $ventas->nombres_usuario = $usuario->nombres_usuario ?? 'Sin usuario';
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
