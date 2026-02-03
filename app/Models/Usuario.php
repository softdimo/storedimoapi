<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use Spatie\Permission\Traits\HasRoles;

// class Usuario extends Model
class Usuario extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_tipo_persona',
        'nombre_usuario',
        'apellido_usuario',
        'usuario',
        'id_tipo_documento',
        'identificacion',
        'numero_telefono',
        'celular',
        'id_genero',
        'email',
        'direccion',
        'fecha_contrato',
        'fecha_terminacion_contrato',
        'clave',
        'session_token',
        'clave_fallas',
        'id_estado',
        'id_rol'
    ];

    public function getAuthIdentifier()
    {
        return $this->id_usuario;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
}
