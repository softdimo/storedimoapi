<?php

namespace App\Http\Responsable\suscripciones;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Suscripcion;

class SuscripcionStore implements Responsable
{
    public function toResponse($request)
    {
        try {
            $nuevaSuscripcion = Suscripcion::create([
                'id_empresa_suscrita'       => $request->input('id_empresa_suscrita'),
                'id_plan_suscrito'          => $request->input('id_plan_suscrito'),
                'dias_trial'                => $request->input('dias_trial'),
                'id_tipo_pago_suscripcion'  => $request->input('id_tipo_pago_suscripcion'),
                'valor_suscripcion'         => $request->input('valor_suscripcion'),
                'fecha_inicial'             => $request->input('fecha_inicial'),
                'fecha_final'               => $request->input('fecha_final'),
                'id_estado_suscripcion'     => $request->input('id_estado_suscripcion'),
                'fecha_cancelacion'         => $request->input('fecha_cancelacion'),
                'renovacion_automatica'     => $request->input('renovacion_automatica', 0), // Default 0
                'observaciones_suscripcion' => $request->input('observaciones_suscripcion'),
            ]);

            if ($nuevaSuscripcion) {
                return response()->json(['success' => true]);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
