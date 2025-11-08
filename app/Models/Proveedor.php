<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class Persona extends Model
class Proveedor extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_tipo_persona',
        'id_tipo_documento',
        'identificacion',
        'nombres_proveedor',
        'apellidos_proveedor',
        'telefono_proveedor',
        'celular_proveedor',
        'email_proveedor',
        'id_genero',
        'direccion_proveedor',
        'id_estado',
        'nit_proveedor',
        'proveedor_juridico',
        'telefono_juridico'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Si estamos en una conexión tenant, usar esa conexión
        if (config('database.default') === 'tenant') {
            $this->connection = 'tenant';
        }
    }
}
