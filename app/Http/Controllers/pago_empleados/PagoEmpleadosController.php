<?php

namespace App\Http\Controllers\pago_empleados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Responsable\pago_empleados\PagoEmpleadoIndex;
use App\Http\Responsable\pago_empleados\PagoEmpleadoCreate;
use App\Http\Responsable\pago_empleados\PagoEmpleadoStore;
use App\Http\Responsable\pago_empleados\PagoEmpleadoUpdate;
use App\Http\Responsable\pago_empleados\PagoEmpleadoShow;
use App\Http\Responsable\pago_empleados\PagoEmpleadoEdit;
use App\Http\Responsable\pago_empleados\PagoEmpleadoDestroy;
use App\Models\PagoEmpleado;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class PagoEmpleadosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new PagoEmpleadoIndex();
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
        return new PagoEmpleadoCreate();
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
        return new PagoEmpleadoStore();
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
        // return new PagoEmpleadoShow($idProducto);
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
        // return new PagoEmpleadoEdit($idProducto);
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
        // return new PagoEmpleadoUpdate($request, $idProducto);
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
        // return new PagoEmpleadoDestroy($idProducto);
    }

    // ======================================================================
    // ======================================================================

    public function verificarPagoEmpleado(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $nombrePagoEmpleado = request('nombre_producto', null);
        $idCategoria = request('id_categoria', null);

        try {
            $validarNombrePagoEmpleado = PagoEmpleado::where('nombre_producto', $nombrePagoEmpleado)
                    ->where('id_categoria', $idCategoria)
                    ->first();

            if (isset($validarNombrePagoEmpleado) && !is_null($validarNombrePagoEmpleado) && !empty($validarNombrePagoEmpleado)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($validarNombrePagoEmpleado);
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

    public function queryPagoEmpleado(Request $request, $idPagoEmpleado)
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
            return PagoEmpleado::where('id_producto', $idPagoEmpleado)->first();

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
