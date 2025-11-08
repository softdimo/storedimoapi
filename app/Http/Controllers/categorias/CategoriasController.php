<?php

namespace App\Http\Controllers\categorias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responsable\categorias\CategoriaIndex;
use App\Http\Responsable\categorias\CategoriaStore;
use App\Http\Responsable\categorias\CategoriaUpdate;
use App\Http\Responsable\categorias\CategoriaDestroy;
use App\Http\Responsable\categorias\CategoriaEdit;
use App\Models\Categoria;
use Exception;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\Log;
use App\Models\Empresa;

class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new CategoriaIndex();
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
        return new CategoriaStore();
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
    public function edit($idCategoria)
    {
        return new CategoriaEdit($idCategoria);
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
        return new CategoriaUpdate($request, $id);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idCategoria)
    {
        return new CategoriaDestroy($idCategoria);
    }

    // ======================================================================
    // ======================================================================

    public function consultaCategoria(Request $request)
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
        
        $categoria = request('categoria', null);

        try
        {
            $categoria = Categoria::where('categoria', $categoria)
                        ->first();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual)
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if ($categoria)
            {
                return response()->json($categoria);
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

    public function categoriasTrait(Request $request)
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
            $categorias = Categoria::where('id_estado', 1)->orderBy('categoria')->pluck('categoria', 'id_categoria');

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($categorias) && !is_null($categorias) && !empty($categorias)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($categorias);
                
            } else {
                return response(null, 200);
            }

        } catch (Exception $e)
        {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
