<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Plan;

class PlanStore implements Responsable
{
    public function toResponse($request)
    {
        try {
            $nuevoPlan = Plan::create([
                'nombre_plan' => $request->input('nombre_plan'),
                'valor_mensual' => $request->input('valor_mensual'),
                'valor_trimestral' => $request->input('valor_trimestral'),
                'valor_semestral' => $request->input('valor_semestral'),
                'valor_anual' => $request->input('valor_anual'),
                'descripcion_plan' => $request->input('descripcion_plan'),
                'id_estado_plan' => $request->input('id_estado_plan')
            ]);

            if ($nuevoPlan) {
                return response()->json(['success' => true]);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
