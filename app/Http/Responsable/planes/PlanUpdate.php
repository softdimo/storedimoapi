<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Plan;


class PlanUpdate implements Responsable
{
    protected $request;
    protected $idPlan;

    public function __construct(Request $request, $idPlan)
    {
        $this->request = $request;
        $this->idPlan = $idPlan;
    }

    // ===================================================================
    // ===================================================================

    public function toResponse($request)
    {
        try {
            $planUpdate = Plan::find($this->idPlan);

            $planUpdate->nombre_plan = $this->request->input('nombre_plan');
            $planUpdate->valor_mensual = $this->request->input('valor_mensual');
            $planUpdate->valor_trimestral = $this->request->input('valor_trimestral');
            $planUpdate->valor_semestral = $this->request->input('valor_semestral');
            $planUpdate->valor_anual = $this->request->input('valor_anual');
            $planUpdate->descripcion_plan = $this->request->input('descripcion_plan');
            $planUpdate->id_estado_plan = $this->request->input('id_estado_plan');
            $planUpdate->update();

            return response()->json([
                'success' => true,
                'message' => 'Plan actualizado correctamente'
            ]);
                
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
