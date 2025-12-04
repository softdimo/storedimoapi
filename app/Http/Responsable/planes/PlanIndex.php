<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Plan;

class PlanIndex implements Responsable
{
    public function toResponse($request)
    {
        try
        {
            $planes = Plan::leftjoin('estados', 'estados.id_estado', '=', 'planes.id_estado_plan')
                ->select(
                    'id_plan',
                    'nombre_plan',
                    'valor_mensual',
                    'valor_trimestral',
                    'valor_semestral',
                    'valor_anual',
                    'descripcion_plan',
                    'id_estado_plan',
                    'estado'
                )
                ->orderBy('id_plan')
                ->get();

            return response()->json($planes);
            
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
