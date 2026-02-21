<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ApiTrafficLogger
{
    public function handle($request, Closure $next)
    {
        // Ejecutar la petición primero
        $response = $next($request);

        try {
            /**
             * En Lumen, el nombre de la conexión tenant suele estar en Config.
             * Si no hay un tenant activo (ej. rutas de administración),
             * usamos 'Storedimo_API_Principal'.
             */
            $tenantName = Config::get('database.connections.tenant.database') ?? 'Storedimo_API';

            /**
             * IMPORTANTE: En Lumen debes asegurarte de tener definida la conexión 'mysql' 
             * en config/database.php que apunte a la base de datos Storedimo principal.
             */
            DB::connection('mysql')->table('traffic_logs')->insert([
                'tenant_db'   => $tenantName,
                'source'      => 'LUMEN_API',
                'method'      => $request->method(),
                'path'        => $request->path(),
                'ip'          => $request->ip(),
                'status_code' => $response->status(), // En Lumen es status(), no getStatusCode()
                'user_agent'  => $request->header('User-Agent'),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // No interrumpir la API si falla el log
        }

        return $response;
    }
}
