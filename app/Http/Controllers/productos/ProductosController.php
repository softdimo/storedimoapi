<?php

namespace App\Http\Controllers\productos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Responsable\productos\ProductoIndex;
use App\Http\Responsable\productos\ProductoStore;
use App\Http\Responsable\productos\ProductoShow;
use App\Http\Responsable\productos\ProductoEdit;
use App\Http\Responsable\productos\ProductoUpdate;
use App\Http\Responsable\productos\ProductoDestroy;
use App\Http\Responsable\productos\ReporteProductosPdf;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ProductoIndex();
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
        return new ProductoStore();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($idProducto)
    {
        return new ProductoShow($idProducto);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idProducto)
    {
        return new ProductoEdit($idProducto);
    }

    // ======================================================================
    // =====================================================================c=

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idProducto)
    {
        return new ProductoUpdate($request, $idProducto);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idProducto)
    {
        return new ProductoDestroy($idProducto);
    }

    // ======================================================================
    // ======================================================================

    public function verificarProducto(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $nombreProducto = request('nombre_producto', null);
        $idCategoria = request('id_categoria', null);

        try {
            $validarNombreProducto = Producto::where('nombre_producto', $nombreProducto)
                    ->where('id_categoria', $idCategoria)
                    ->first();

            if (isset($validarNombreProducto) && !is_null($validarNombreProducto) && !empty($validarNombreProducto)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($validarNombreProducto);
            }
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

    public function queryProducto(Request $request, $idProducto)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        try
        {
            $validarNombreProducto = Producto::leftjoin('categorias','categorias.id_categoria','=','productos.id_categoria')
                ->select(
                    'id_producto',
                    'id_empresa',
                    'imagen_producto',
                    'nombre_producto',
                    'categorias.id_categoria',
                    'categoria',
                    'precio_unitario',
                    'precio_detal',
                    'precio_por_mayor',
                    'descripcion',
                    'stock_minimo',
                    'categorias.id_estado',
                    'tamano',
                    'cantidad',
                    'referencia',
                    'fecha_vencimiento',
                    'id_umd',
                    'id_proveedor'
                )
                ->where('id_producto', $idProducto)
                ->where('cantidad', '>', 0)
                ->first();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual)
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            if ($validarNombreProducto)
            {
                return response()->json($validarNombreProducto);
            } else
            {
                return response(null, 200);
            }

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

    public function queryProductoUpdate(Request $request, $idProducto)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        try
        {
            $queryProductoUpdate = Producto::leftjoin('categorias','categorias.id_categoria','=','productos.id_categoria')
                ->select(
                    'id_producto',
                    'id_empresa',
                    'imagen_producto',
                    'nombre_producto',
                    'categorias.id_categoria',
                    'categoria',
                    'precio_unitario',
                    'precio_detal',
                    'precio_por_mayor',
                    'descripcion',
                    'stock_minimo',
                    'categorias.id_estado',
                    'tamano',
                    'cantidad',
                    'referencia',
                    'fecha_vencimiento',
                    'id_umd',
                    'id_proveedor'
                )
                ->where('id_producto', $idProducto)
                ->first();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual)
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            if ($queryProductoUpdate)
            {
                return response()->json($queryProductoUpdate);
            } else
            {
                return response(null, 200);
            }

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

    public function reporteProductosPdf()
    {
        return new ReporteProductosPdf();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Valida que la referencia del producto no exista a la hora de crear un nuevo producto
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function referenceValidator(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $referencia = $request->input('referencia');
        $existe = Producto::where('referencia', $referencia)->exists();

        // Restaurar conexión principal si se usó tenant
        if ($empresaActual) {
            DatabaseConnectionHelper::restaurarConexionPrincipal();
        }

        return response()->json([
            'valido' => !$existe
        ]);
    }

    // ======================================================================
    // ======================================================================

    public function productosTraitVentas(Request $request)
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
            $productos = Producto::where('cantidad', '>', 0)
                ->select(
                    DB::raw("CONCAT(referencia, ' - ', nombre_producto) AS nombre_producto"),
                    'id_producto'
                )
                ->where('id_estado', 1)
                ->orderBy('nombre_producto')
                ->pluck('nombre_producto', 'id_producto');

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($productos) && !is_null($productos) && !empty($productos)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($productos);
                
            } else {
                return response(null, 200);
            }

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

    public function productosTraitCompras(Request $request)
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
            $productosCompra = Producto::orderBy('nombre_producto')
                ->select(
                    DB::raw("CONCAT(referencia, ' - ', nombre_producto) AS nombre_producto"),
                    'id_producto'
                )
                ->where('id_estado', 1)
                ->orderBy('nombre_producto')
                ->pluck('nombre_producto', 'id_producto');

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($productosCompra) && !is_null($productosCompra) && !empty($productosCompra)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($productosCompra);
                
            } else {
                return response(null, 200);
            }

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

    public function productosTraitExistencias(Request $request)
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
            $productosExistencias = Producto::leftJoin('categorias', 'categorias.id_categoria', '=', 'productos.id_categoria')
                ->select(
                    'id_producto',
                    DB::raw("CONCAT(referencia, ' - ', nombre_producto) AS nombre_producto"),
                    'categorias.id_categoria',
                    'categorias.categoria'
                )
                ->where('cantidad', '>', 0)
                ->where('productos.id_estado', 1)
                ->orderBy('nombre_producto')
                ->get();
                // ->pluck('nombre_producto', 'id_producto');

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($productosExistencias) && !is_null($productosExistencias) && !empty($productosExistencias)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($productosExistencias);
                
            } else {
                return response(null, 200);
            }

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

    public function consultarUmd(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual)
        {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }
        
        try
        {
            $umd = UnidadMedida::select(DB::raw("CONCAT(descripcion, ' (', abreviatura, ')') AS umd"), 'id')
                                ->where('estado_id', 1)
                                ->orderBy('id')
                                ->pluck('umd', 'id');

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($umd) && !is_null($umd) && !empty($umd))
            {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($umd);
                
            } else
            {
                return response(null, 200);
            }

        } catch (Exception $e)
        {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual))
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ======================================================================
    // ======================================================================

    public function productosPorProveedor(Request $request, $idProveedor)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        try
        {
            $productosPorProveedor = Producto::leftjoin('categorias','categorias.id_categoria','=','productos.id_categoria')
                ->select(
                    'id_producto',
                    'id_empresa',
                    'imagen_producto',
                    'nombre_producto',
                    'categorias.id_categoria',
                    'categoria',
                    'precio_unitario',
                    'precio_detal',
                    'precio_por_mayor',
                    'descripcion',
                    'stock_minimo',
                    'categorias.id_estado',
                    'tamano',
                    'cantidad',
                    'referencia',
                    'fecha_vencimiento',
                    'id_umd',
                    'id_proveedor'
                )
                ->where('id_proveedor', $idProveedor)
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual)
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            if ($productosPorProveedor)
            {
                return response()->json($productosPorProveedor);
            }
            //  else
            // {
            //     return response(null, 200);
            // }

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
