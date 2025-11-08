<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Exception;

class DatabaseConnectionHelper
{
    /**
     * Configura dinámicamente la conexión a base de datos de un tenant (empresa).
     *
     * @param array $empresa  Datos de conexión (db_host, db_database, db_username, db_password, etc.)
     * @return bool           true si la conexión se establece correctamente.
     * @throws Exception      Si ocurre un error durante la configuración o conexión.
     */
    public static function configurarConexionTenant(array $empresa)
    {
        try {
            // 1️⃣ Limpiar conexión anterior (evita que quede cacheada o colisione)
            Config::set('database.connections.tenant', null);
            DB::purge('tenant');

            // 2️⃣ Validar que existan los datos mínimos requeridos
            if (!isset($empresa['db_database']) || !isset($empresa['db_username']) ||
                !isset($empresa['db_password'])
            ) {
                throw new Exception('Datos de conexión incompletos para la empresa API_DB');
            }

            // 3️⃣ Determinar el host según el entorno
            // En local → usa el host del servidor remoto
            // En producción → desencripta el host guardado en base de datos (si existe)
            if (app()->environment('local')) {
                // Entorno local → servidor remoto de desarrollo
                $host = 'srv1999.hstgr.io';
            } elseif (isset($empresa['db_host'])) {
                // Entorno producción → desencripta el host almacenado en BD
                $host = self::safeDecrypt($empresa['db_host']);
            } else {
                // Valor por defecto definido en .env o 'localhost'
                $host = env('DB_HOST', 'localhost');
            }

            // 4️⃣ Crear la configuración dinámica del tenant
            $config = [
                'driver'    => 'mysql',
                'host'      => $host,
                'port'      => env('DB_PORT', '3306'),
                'database'  => self::safeDecrypt($empresa['db_database']),
                'username'  => self::safeDecrypt($empresa['db_username']),
                'password'  => self::safeDecrypt($empresa['db_password']),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
                'options'   => [
                    \PDO::ATTR_PERSISTENT => false, // No mantener conexiones persistentes
                ],
            ];

            // 5️⃣ Registrar la configuración en tiempo de ejecución
            Config::set('database.connections.tenant', $config);

            // 6️⃣ Refrescar y reconectar la conexión tenant
            DB::purge('tenant');
            DB::reconnect('tenant');

            // 7️⃣ Verificar que la conexión realmente funcione
            DB::connection('tenant')->getPdo();

            // 8️⃣ Establecer la conexión tenant como la conexión por defecto
            Config::set('database.default', 'tenant');
            DB::reconnect('tenant');

            return true; // ✅ Éxito

        } catch (Exception $e) {
            // 🔁 Restaurar conexión principal en caso de error
            Config::set('database.default', 'mysql');
            DB::reconnect('mysql');
            throw new Exception('Error Exception configurando conexión tenant APP');
        }
    }

    /**
     * Restaura la conexión principal (por defecto).
     */
    public static function restaurarConexionPrincipal()
    {
        Config::set('database.default', 'mysql');
        DB::reconnect('mysql');
    }

    /**
     * Desencripta de forma segura un valor.
     * Si el valor no está cifrado o la desencriptación falla, devuelve el valor original.
     *
     * @param string|null $value
     * @return string|null
     */
    private static function safeDecrypt($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : $value;
        } catch (\Exception $e) {
            return $value; // Retorna el valor tal cual si no se puede desencriptar
        }
    }
}
