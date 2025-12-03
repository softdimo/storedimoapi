<?php

namespace App\Http\Responsable\suscripciones;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Suscripcion;


class SuscripcionUpdate implements Responsable
{
    protected $request;
    protected $idSuscripcion;

    public function __construct(Request $request, $idSuscripcion)
    {
        $this->request = $request;
        $this->idSuscripcion = $idSuscripcion;
    }

    // ===================================================================
    // ===================================================================

    public function toResponse($request)
    {
        try {
            $suscripcionUpdate = Suscripcion::find($this->idSuscripcion);

            $suscripcionUpdate->id_plan_suscrito = $this->request->input('id_plan_suscrito');
            $suscripcionUpdate->dias_trial = $this->request->input('dias_trial');
            $suscripcionUpdate->id_tipo_pago_suscripcion = $this->request->input('id_tipo_pago_suscripcion');
            $suscripcionUpdate->valor_suscripcion = $this->request->input('valor_suscripcion');
            $suscripcionUpdate->fecha_inicial = $this->request->input('fecha_inicial');
            $suscripcionUpdate->fecha_final = $this->request->input('fecha_final');
            $suscripcionUpdate->id_estado_suscripcion = $this->request->input('id_estado_suscripcion');
            $suscripcionUpdate->fecha_cancelacion = $this->request->input('fecha_cancelacion');
            $suscripcionUpdate->renovacion_automatica = $this->request->input('renovacion_automatica');
            $suscripcionUpdate->observaciones_suscripcion = $this->request->input('observacionesSuscripcion');
            $suscripcionUpdate->update();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción actualizada correctamente'
            ]);
                
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
