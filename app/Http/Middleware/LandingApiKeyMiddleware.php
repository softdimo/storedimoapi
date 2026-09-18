<?php

namespace App\Http\Middleware;

use Closure;

class LandingApiKeyMiddleware
{
    public function handle($request, Closure $next)
    {
        // 1. Leemos la clave desde la configuración (no desde env directamente)
        $apiKeyLocal = config('services.landing_key');

        // 2. Obtenemos el header que envía la App Laravel
        $apiKeyEnviada = $request->header('X-Landing-Api-Key');

        // 3. Validamos que exista la clave local y que coincida con la enviada
        if (!$apiKeyLocal || $apiKeyEnviada !== $apiKeyLocal) {
            return response()->json([
                'error' => 'Acceso denegado: Firma de servicio no válida.'
            ], 401);
        }

        return $next($request);
    }
}
