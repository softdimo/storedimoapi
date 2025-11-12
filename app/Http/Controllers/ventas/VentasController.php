<?php

namespace App\Http\Controllers\ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Responsable\ventas\VentaIndex;
use App\Http\Responsable\ventas\VentaDetalle;
use App\Http\Responsable\ventas\VentaStore;
use App\Http\Responsable\ventas\VentaUpdate;
use App\Models\Empresa;
use App\Models\Venta;
use App\Models\VentaProducto;
use App\Models\Persona;
use App\Helpers\DatabaseConnectionHelper;
use Exception;


class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new VentaIndex();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new VentaStore();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    
    public function ventaDetalle($idVenta)
    {
        return new VentaDetalle($idVenta);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return new VentaUpdate($request, $id);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    public function consultaVenta(Request $request, $idVenta)
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
            $venta = Venta::where('id_venta', $idVenta)->first();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($venta);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ======================================================================
    // ======================================================================

    public function anularVenta(Request $request, $idVenta)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $venta = Venta::find($idVenta);

        if (isset($venta) && !is_null($venta) && !empty($venta)) {

            try {
                $venta->id_venta = 2;
                $venta->update();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json(['success' => true]);
    
            } catch (Exception $e) {
                // Asegurar restauración de conexión principal en caso de error
                if (isset($empresaActual)) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }
                
                return response()->json(['error_bd' => $e->getMessage()]);
            }
        }
    }

    // ======================================================================
    // ======================================================================

    public function reporteVentasPdf(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $fechaInicial = request('fecha_inicial', null);
        $fechaFinal = request('fecha_final', null);

        try {
            $ventas = Venta::leftJoin('empresas', 'empresas.id_empresa', '=', 'ventas.id_empresa')
                ->leftJoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'ventas.id_tipo_cliente')
                ->leftJoin('tipos_pago', 'tipos_pago.id_tipo_pago', '=', 'ventas.id_tipo_pago')
                ->leftJoin('productos', 'productos.id_producto', '=', 'ventas.id_producto')
                ->leftJoin('personas', 'personas.id_persona', '=', 'ventas.id_cliente')
                ->leftJoin('usuarios', 'usuarios.id_usuario', '=', 'ventas.id_usuario')
                // ->leftJoin('estados', 'estados.id_estado', '=', 'ventas.id_estado_credito')
                ->whereBetween('fecha_venta', [$fechaInicial, $fechaFinal])
                ->whereIn('ventas.id_tipo_cliente', [5, 6]) // Filtra solo si hay una persona
                ->select([
                    'ventas.id_venta',
                    'ventas.fecha_venta',
                    'ventas.subtotal_venta',
                    'ventas.descuento',
                    'ventas.total_venta',
                    'personas.id_persona',
                    DB::raw("CONCAT(nombres_persona, ' ', apellidos_persona) AS nombres_cliente"),
                    DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) AS vendedor"),
                    'tipo_pago'
                ])
                ->orderByDesc('fecha_venta')
                ->get();

            $total = $ventas->sum('total_venta');

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json([
                'ventas' => $ventas,
                'total' => $total,
            ], 200);


        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ======================================================================
    // ======================================================================
    
    public function detalleVenta(Request $request, $idVenta)
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
            $detalleVenta = VentaProducto::leftJoin('ventas', 'ventas.id_venta', '=', 'venta_productos.id_venta')
                ->leftJoin('productos', 'productos.id_producto', '=', 'venta_productos.id_producto')
                ->where('venta_productos.id_venta', $idVenta)
                ->select(
                    'venta_productos.id_venta',
                    'venta_productos.id_producto',
                    'nombre_producto',
                    'venta_productos.cantidad',
                    'subtotal',
                    DB::raw("CONCAT('$', FORMAT(subtotal, 0, 'de_DE')) as subtotal_detalle"),
                    DB::raw("
                        CASE
                            WHEN precio_detal_venta IS NOT NULL THEN precio_detal_venta
                            ELSE CONCAT(precio_x_mayor_venta)
                        END AS precio_venta
                    "),
                    DB::raw("
                        CONCAT('$', FORMAT(
                            CASE
                                WHEN precio_detal_venta IS NOT NULL THEN precio_detal_venta
                                ELSE precio_x_mayor_venta
                            END
                        , 0, 'de_DE')) as precio_venta_detalle
                    ")
                )
                ->orderBy('nombre_producto')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($detalleVenta);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ===================================================================
    // ===================================================================

    public function ventaDiaMes(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);

        // 3. Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $hoy = request('fecha_venta_dia');
        $inicioMes = request('fecha_venta_inicio_mes');

        try {
            $ventasDia = Venta::whereDate('fecha_venta', $hoy)->sum('total_venta');
            $ventasMes = Venta::whereBetween('fecha_venta', [$inicioMes, Carbon::now()])->sum('total_venta');

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return [
                'ventasDia' => $ventasDia,
                'ventasMes' => $ventasMes
            ];
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
