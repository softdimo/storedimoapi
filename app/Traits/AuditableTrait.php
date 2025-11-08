<?php

namespace App\Traits;

use OwenIt\Auditing\Auditable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait AuditableTrait
{
    use Auditable {
        Auditable::toAudit as parentToAudit;
    }

    public function toAudit(): array
    {
        // Guardar la conexión actual
        $currentConnection = $this->getConnectionName();
        
        // Cambiar a la conexión mysql
        $this->setConnection('mysql');
        
        try {
            // Forzar la conexión mysql en la configuración
            Config::set('database.default', 'mysql');
            
            // Forzar la conexión mysql en la base de datos
            DB::setDefaultConnection('mysql');
            
            // Llamar al método original
            $result = $this->parentToAudit();
            
            // Restaurar la conexión original
            $this->setConnection($currentConnection);
            Config::set('database.default', $currentConnection);
            DB::setDefaultConnection($currentConnection);
            
            return $result;
            
        } catch (\Exception $e) {
            // Restaurar la conexión original en caso de error
            $this->setConnection($currentConnection);
            Config::set('database.default', $currentConnection);
            DB::setDefaultConnection($currentConnection);
            throw $e;
        }
    }
}
