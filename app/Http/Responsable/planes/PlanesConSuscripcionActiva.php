<?php

namespace App\Http\Responsable\planes;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Suscripcion;

class PlanesConSuscripcionActiva implements Responsable
{
    public function toResponse($request)
    {
        try {
            $planes = Suscripcion::where('id_estado_suscripcion', 1)
                ->whereNotNull('id_plan_suscrito')
                ->distinct()
                ->pluck('id_plan_suscrito')
                ->map(function ($idPlan) {
                    return (int) $idPlan;
                })
                ->values()
                ->all();

            return response()->json($planes);
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
