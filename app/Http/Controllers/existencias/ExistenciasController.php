<?php

namespace App\Http\Controllers\existencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responsable\existencias\BajaIndex;
use App\Http\Responsable\existencias\BajaStore;
use App\Http\Responsable\existencias\StockMinimoIndex;
use App\Models\Baja;
use App\Models\BajaDetalle;
use App\Models\Producto;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;
use Exception;

class ExistenciasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return new ExistenciaIndex();
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
        // return new ExistenciaStore();
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        // return new ExistenciaUpdate($request, $id);
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

    public function bajaIndex()
    {
        return new BajaIndex();
    }

    // ======================================================================
    // ======================================================================
    
    public function bajaStore()
    {
        return new BajaStore();
    }

    // ======================================================================
    // ======================================================================

    public function bajaDetalle(Request $request, $idBaja)
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
            $bajaDetalle = BajaDetalle::leftJoin('bajas', 'bajas.id_baja', '=', 'bajas_detalle.id_baja')
                ->leftJoin('tipo_baja', 'tipo_baja.id_tipo_baja', '=', 'bajas_detalle.id_tipo_baja')
                ->leftJoin('productos', 'productos.id_producto', '=', 'bajas_detalle.id_producto')
                ->leftJoin('categorias', 'categorias.id_categoria', '=', 'productos.id_categoria')
                ->where('bajas_detalle.id_baja', $idBaja)
                ->select(
                    'bajas_detalle.id_baja',
                    'bajas_detalle.id_producto',
                    'nombre_producto',
                    'bajas_detalle.cantidad',
                    'bajas_detalle.observaciones',
                    'tipo_baja.id_tipo_baja',
                    'tipo_baja',
                    'categorias.id_categoria',
                    'categoria',
                )
                ->orderBy('nombre_producto')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($bajaDetalle);

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

    public function reporteBajasPdf(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        // $fechaInicial = request('fecha_inicial', null);
        // $fechaFinal = request('fecha_final', null);

        $fechaInicial = \Carbon\Carbon::parse($request->input('fecha_inicial'))->startOfDay();
        $fechaFinal   = \Carbon\Carbon::parse($request->input('fecha_final'))->endOfDay();

        try {
            $bajas = BajaDetalle::leftJoin('bajas', 'bajas.id_baja', '=', 'bajas_detalle.id_baja')
                ->leftJoin('productos', 'productos.id_producto', '=', 'bajas_detalle.id_producto')
                ->leftJoin('tipo_baja', 'tipo_baja.id_tipo_baja', '=', 'bajas_detalle.id_tipo_baja')
                ->leftJoin('categorias', 'categorias.id_categoria', '=', 'productos.id_categoria')
                ->whereBetween('fecha_baja', [$fechaInicial, $fechaFinal])
                ->select([
                    'productos.id_producto',
                    'productos.referencia',
                    'nombre_producto',
                    'categoria',
                    'fecha_baja',
                    'bajas_detalle.cantidad',
                    'bajas_detalle.observaciones',
                    'tipo_baja'
                ])
                ->orderByDesc('fecha_baja')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json($bajas);

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

    public function stockMinimoIndex()
    {
        return new StockMinimoIndex();
    }
    
    // ======================================================================
    // ======================================================================

    public function alertaStockMinimo(Request $request)
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
            $productosStockMinimo = Producto::where('id_estado', 1)
            ->whereColumn('cantidad', '<', 'stock_minimo')
            ->count();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            // Devolver un JSON estructurado correctamente
            return response()->json(['productos_bajo_stock' => $productosStockMinimo]);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    public function baja(Request $request, $idBaja)
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
            $baja = Baja::leftjoin('usuarios','usuarios.id_usuario','=','bajas.id_responsable_baja')
                ->leftjoin('estados','estados.id_estado','=','bajas.id_estado_baja')
                ->select(
                    'id_baja',
                    'id_usuario',
                    DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario, ' - ', identificacion) AS nombres_usuario"),
                    'fecha_baja',
                    'id_estado_baja',
                    'estado'
                )
                ->where('id_baja', $idBaja)
                ->orderByDesc('fecha_baja')
                ->first();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($baja);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
