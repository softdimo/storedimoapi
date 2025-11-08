<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable;
// use OwenIt\Auditing\Auditable as AuditableTrait;

use App\Traits\AuditableTrait;

// class Persona extends Model
class Persona extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_tipo_persona',
        'id_tipo_documento',
        'identificacion',
        'nombres_persona',
        'apellidos_persona',
        'numero_telefono',
        'celular',
        'email',
        'id_genero',
        'direccion',
        'id_estado',
        'nit_empresa',
        'nombre_empresa',
        'telefono_empresa'
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
