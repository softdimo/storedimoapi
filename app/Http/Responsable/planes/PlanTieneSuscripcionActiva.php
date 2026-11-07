<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Suscripcion;

class PlanTieneSuscripcionActiva implements Responsable
{
    protected $idPlan;

    public function __construct($idPlan)
    {
        $this->idPlan = $idPlan;
    }

    public function toResponse($request)
    {
        try {
            $tieneSuscripcionActiva = Suscripcion::where('id_plan_suscrito', $this->idPlan)
                ->where('id_estado_suscripcion', 1)
                ->exists();

            return response()->json([
                'tiene_suscripcion_activa' => $tieneSuscripcionActiva
            ]);
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
