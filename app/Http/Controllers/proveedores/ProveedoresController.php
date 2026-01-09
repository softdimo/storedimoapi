<?php

namespace App\Http\Controllers\proveedores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responsable\proveedores\ProveedorIndex;
use App\Http\Responsable\proveedores\ProveedorStore;
use App\Http\Responsable\proveedores\ProveedorUpdate;
use App\Http\Responsable\proveedores\ProveedorEdit;
use App\Models\Proveedor;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Empresa;

class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ProveedorIndex();
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
        return new ProveedorStore($request);
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
    public function edit($idProveedor)
    {
        return new ProveedorEdit($idProveedor);
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
    public function update(Request $request, $idProveedor)
    {
        return new ProveedorUpdate($request, $idProveedor);
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

    public function consultarIdentificacionProveedor(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $identificacion = request('identificacion', null);
        
        // Consultamos si ya existe un proveedor con la identificación ingresada
        $proveedor = Proveedor::where('identificacion', $identificacion)->first();

        // Restaurar conexión principal si se usó tenant
        if ($empresaActual) {
            DatabaseConnectionHelper::restaurarConexionPrincipal();
        }

        if ($proveedor) {
            return response()->json($proveedor);
        } else {
            return response(null, 200);
        }
    }

    // ======================================================================
    // ======================================================================

    public function consultarNitProveedor(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $nitProveedor = request('nit_proveedor', null);
        
        // Consultamos si ya existe un proveedor con el nit ingresado
        $proveedor = Proveedor::where('nit_proveedor', $nitProveedor)->first();

        // Restaurar conexión principal si se usó tenant
        if ($empresaActual) {
            DatabaseConnectionHelper::restaurarConexionPrincipal();
        }

        if ($proveedor) {
            return response()->json($proveedor);
        } else {
            return response(null, 200);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function proveedoresTrait(Request $request)
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
        
        try {
            $proveedoresCompra = Proveedor::leftJoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'proveedores.id_tipo_persona')
                ->select(
                    'proveedores.id_proveedor',
                    'proveedores.identificacion',
                    'proveedores.nit_proveedor',
                    'proveedores.id_tipo_persona',
                    DB::raw("
                        CASE
                            WHEN proveedores.id_tipo_persona = 4 THEN CONCAT(nit_proveedor, ' - ', proveedor_juridico, ' (', tipo_persona.tipo_persona, ')')
                            ELSE CONCAT(identificacion, ' - ', nombres_proveedor, ' ', apellidos_proveedor, ' (', tipo_persona.tipo_persona, ')')
                        END AS nombre_proveedor
                    ")
                )
                ->whereIn('proveedores.id_tipo_persona', [3,4])
                ->where('proveedores.id_estado', 1)
                ->orderBy('tipo_persona.tipo_persona')
                ->get() // Usamos get() en lugar de pluck()
                ->mapWithKeys(function($item) {
                    return [$item->id_proveedor => $item->nombre_proveedor]; // Usamos id_persona como clave única
                });

            // Retornamosel proveedor si existe, de lo contrario retornamos null
            if (isset($proveedoresCompra) && !is_null($proveedoresCompra) && !empty($proveedoresCompra)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($proveedoresCompra);
                
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

    public function validarCorreoProveedor(Request $request)
    {
        $correoProveedor = $request->input('email_proveedor');

        try {
            $correoExiste = Proveedor::where('email_proveedor', $correoProveedor)->first();

            return response()->json($correoExiste);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
} // FIN clase ProveedoresController
