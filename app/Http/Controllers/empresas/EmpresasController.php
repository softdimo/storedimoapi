<?php

namespace App\Http\Controllers\empresas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Http\Responsable\empresas\EmpresaIndex;
use App\Http\Responsable\empresas\EmpresaStore;
use App\Http\Responsable\empresas\EmpresaUpdate;
use App\Http\Responsable\empresas\EmpresaEdit;
use App\Http\Responsable\empresas\EmpresaDatosConexion;
use App\Models\Empresa;

class EmpresasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new EmpresaIndex();
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
        return new EmpresaStore();
    }

    // ======================================================================
    // ======================================================================    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($idEmpresa)
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
    public function edit($idEmpresa)
    {
        return new EmpresaEdit($idEmpresa);
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
    public function update(Request $request, $idEmpresa)
    {
        return new EmpresaUpdate($request, $idEmpresa);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idEmpresa)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    public function consultarEmpresa(Request $request)
    {
        $nitEmpresa = request('nit_empresa', null);
        $nombreEmpresa = request('nombre_empresa', null);

        try {
            $consultarEmpresa = Empresa::where('nit_empresa', $nitEmpresa)
                    ->where('nombre_empresa', $nombreEmpresa)
                    ->first();

            if (isset($consultarEmpresa) && !is_null($consultarEmpresa) && !empty($consultarEmpresa)) {
                return response()->json($consultarEmpresa);
            }
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }

    // ======================================================================
    // ======================================================================

    public function empresaDatosConexion($idEmpresa)
    {
        return new EmpresaDatosConexion($idEmpresa);
    }

    
    public function validar_nit(Request $request)
    {
        $nitEmpresa = $request->input('nit_empresa', null);
        try {
            $nitExist = Empresa::where('nit_empresa', $nitEmpresa)->first();

            if ($nitExist) {
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El NIT ya está registrado.',
                    'empresa' => $nitExist
                ]);
            }

            return response()->json([
                'valido' => true,
                'mensaje' => 'El NIT está disponible.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error_bd' => $e->getMessage(),
                'valido' => false
            ], 500);
        }
    }

}
