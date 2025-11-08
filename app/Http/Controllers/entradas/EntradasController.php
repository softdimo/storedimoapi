<?php

namespace App\Http\Controllers\entradas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Responsable\entradas\EntradaIndex;
use App\Http\Responsable\entradas\DetalleEntrada;
use App\Http\Responsable\entradas\EntradaStore;
use App\Http\Responsable\entradas\EntradaUpdate;
use App\Models\Empresa;
use App\Models\Compra;
use App\Models\CompraProducto;
use App\Models\Producto;
use App\Helpers\DatabaseConnectionHelper;


class EntradasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new EntradaIndex();
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
        return new EntradaStore();
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

    
    public function entrada($idEntrada)
    {
        return new DetalleEntrada($idEntrada);
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
        return new EntradaUpdate($request, $id);
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

    public function entradaConsulta($idCompra)
    {
        try {
            return Compra::where('id_compra', $idCompra)->first();

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ======================================================================
    // ======================================================================

    public function anularCompra(Request $request, $idCompra)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $compra = Compra::find($idCompra);

        if (isset($compra) && !is_null($compra) && !empty($compra)) {

            try {
                $compra->id_estado = 2;
                $compra->update();

                $productosCompra = CompraProducto::where('id_compra', $idCompra)->get();

                foreach ($productosCompra as $item) {
                
                    $producto = Producto::find($item->id_producto);
                
                    if ($producto) {
                        $nuevaCantidad = $producto->cantidad - $item->cantidad;
                        $producto->cantidad = max($nuevaCantidad, 0); // evita cantidades negativas
                        $producto->save();
                    }
                }

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

    // ===================================================================
    // ===================================================================

    public function reporteComprasPdf(Request $request)
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
            $compras = Compra::leftJoin('proveedores', 'proveedores.id_proveedor', '=', 'compras.id_proveedor')
                ->whereBetween('fecha_compra', [$fechaInicial, $fechaFinal])
                ->whereIn('proveedores.id_tipo_persona', [3, 4]) // Filtra solo si hay una persona
                ->select([
                    'compras.id_compra',
                    'compras.fecha_compra',
                    'compras.valor_compra',
                    'proveedores.id_proveedor',
                    DB::raw("
                        CASE
                            WHEN proveedores.proveedor_juridico IS NOT NULL THEN proveedores.proveedor_juridico
                            ELSE CONCAT(proveedores.nombres_proveedor, ' ', proveedores.apellidos_proveedor)
                        END AS nombre_proveedor
                    ")
                ])
                ->orderByDesc('fecha_compra')
                ->get();

            $total = $compras->sum('valor_compra');

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json([
                'compras' => $compras,
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
    
    // ===================================================================
    // ===================================================================

    public function detalleCompra(Request $request, $idCompra)
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
            $detalleCompra = CompraProducto::leftJoin('compras', 'compras.id_compra', '=', 'compra_productos.id_compra')
                ->leftJoin('productos', 'productos.id_producto', '=', 'compra_productos.id_producto')
                ->where('compra_productos.id_compra', $idCompra)
                ->select(
                    'compra_productos.id_compra',
                    'compra_productos.id_producto',
                    'nombre_producto',
                    'compra_productos.cantidad',
                    DB::raw("CONCAT('$', FORMAT(precio_unitario_compra, 0, 'de_DE')) as precio_unitario_compra"),
                    DB::raw("CONCAT('$', FORMAT(subtotal, 0, 'de_DE')) as subtotal"),
                )
                ->orderBy('nombre_producto')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($detalleCompra);

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

    public function detalleCompraProductoPdf(Request $request, $idCompra)
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
            $detalleCompraProductoPdf = Compra::leftJoin('compra_productos', 'compra_productos.id_compra', '=', 'compras.id_compra')
                ->leftJoin('productos', 'productos.id_producto', '=', 'compra_productos.id_producto')
                ->leftJoin('proveedores', 'proveedores.id_proveedor', '=', 'compras.id_proveedor')
                ->where('compras.id_compra', $idCompra)
                ->select(
                    'compras.id_compra',
                    'compras.fecha_compra',
                    'compras.valor_compra',
                    'compras.id_proveedor',
                    'proveedores.proveedor_juridico',
                    'proveedores.nombres_proveedor',
                    'proveedores.apellidos_proveedor',
                    'compra_productos.id_producto',
                    'compra_productos.cantidad',
                    'compra_productos.precio_unitario_compra',
                    'compra_productos.subtotal',
                    'productos.nombre_producto'
                )
                ->orderBy('nombre_producto')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($detalleCompraProductoPdf);

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

    public function entradaDiaMes(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);

        // 3. Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $hoy = request('fecha_entrada_dia');
        $inicioMes = request('fecha_entrada_inicio_mes');

        try {
            $entradasDia = Compra::whereDate('fecha_compra', $hoy)->sum('valor_compra');
            $entradasMes = Compra::whereBetween('fecha_compra', [$inicioMes, Carbon::now()])->sum('valor_compra');

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return [
                'entradasDia' => $entradasDia,
                'entradasMes' => $entradasMes
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
