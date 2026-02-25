<?php

namespace App\Http\Responsable\metricas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Metrica;

class MetricaStore implements Responsable
{
    public function toResponse($request)
    {
        try {
            // Usamos DB::table o el modelo Metrica si apunta a traffic_logs
            Metrica::on('mysql')->create([
                'tenant_db'   => $request->input('tenant_db'),
                'source'      => $request->input('source'),
                'method'      => $request->input('method'),
                'path'        => $request->input('path'),
                'ip'          => $request->input('ip'),
                'status_code' => $request->input('status_code'),
                'user_agent'  => $request->input('user_agent'),
                // Con create(), Eloquent maneja created_at y updated_at automáticamente
            ]);

            return response()->json(['res' => 'Log guardado'], 201);
            
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
