<?php

namespace App\Http\Responsable\metricas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Metrica;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

class MetricaIndex implements Responsable
{
    public function toResponse($request)
    {
        try {
            $metricas = Metrica::select(
                'id_log',
                'tenant_db',
                'source',
                'method',
                'path',
                'ip',
                'status_code',
                'user_agent',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('created_at')
            ->take(1000)
            ->get();

            return response()->json($metricas);
            
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
