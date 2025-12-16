<?php

namespace App\Http\Controllers\suscripciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Responsable\suscripciones\SuscripcionIndex;
use App\Http\Responsable\suscripciones\SuscripcionStore;
use App\Http\Responsable\suscripciones\SuscripcionEdit;
use App\Http\Responsable\suscripciones\SuscripcionUpdate;
use App\Models\Suscripcion;
use App\Helpers\DatabaseConnectionHelper;

class SuscripcionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new SuscripcionIndex();
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
        return new SuscripcionStore();
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
    public function edit($idSuscripcion)
    {
        return new SuscripcionEdit($idSuscripcion);
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
    public function update(Request $request, $idSuscripcion)
    {
        return new SuscripcionUpdate($request, $idSuscripcion);
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
        //
    }
    
    // ======================================================================
    // ======================================================================

    public function suscripcionEmpresaEstadoLogin($idEmpresa)
    {
        try {
            $suscripcionEmpresa = Suscripcion::where('id_empresa_suscrita', $idEmpresa)->first();

            if (is_null($suscripcionEmpresa)) {
                // Devuelve un array vacío o un valor específico que indique "no existe"
                return response()->json(null);
            }

            return response()->json($suscripcionEmpresa);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
        
    // ======================================================================
    // ======================================================================

    public function suscripcionActualizarEstadoAutomatico(Request $request, $idSuscripcion)
    {
        // 1. Obtener el nuevo estado (2 = Inactivo)
        $nuevoEstado = $request->input('id_estado_suscripcion', 2);
        
        // Si necesitas auditoría, puedes obtener el ID del usuario que dispara la acción
        // $idAudit = $request->input('id_audit');


        try {
            // 2. Buscar y actualizar la suscripción en un solo paso (o en dos para verificar la existencia)
            $suscripcion = Suscripcion::where('id_suscripcion', $idSuscripcion)->first();

            if (is_null($suscripcion)) {
                // No se encontró la suscripción
                return response()->json(['success' => false, 'message' => 'Suscripción no encontrada.'], 404);
            }

            // 3. Ejecutar la actualización (solo si el estado actual es diferente al nuevo estado)
            if ($suscripcion->id_estado_suscripcion != $nuevoEstado) {

                $suscripcion->id_estado_suscripcion = $nuevoEstado;
                // $suscripcion->id_audit = $idAudit; // Si tienes campos de auditoría
                $suscripcion->save();
                
                return response()->json(['success' => true, 'message' => 'Estado de suscripción actualizado con éxito.'], 200);
            }

            // Ya estaba en el estado deseado
            return response()->json(['success' => true, 'message' => 'Suscripción ya estaba en el estado solicitado.'], 200);

        } catch (Exception $e) {
            // return response()->json(['error_bd' => $e->getMessage()], 500);
            return response()->json(['success' => false, 'error_bd' => $e->getMessage()], 500);
        }
    }
}
