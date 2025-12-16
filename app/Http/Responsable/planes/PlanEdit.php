<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Plan;

class PlanEdit implements Responsable
{
    protected $idPlan;

    // =========================================

    public function __construct($idPlan)
    {
        $this->idPlan = $idPlan;
    }

    // =========================================

    public function toResponse($request)
    {
        try
        {
            $plan = Plan::leftjoin('estados', 'estados.id_estado', '=', 'planes.id_estado_plan')
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
                ->where('id_plan', $this->idPlan)
                ->first();

            return response()->json($plan);
            
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
