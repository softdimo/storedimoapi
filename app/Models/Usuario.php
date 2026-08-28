<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable; // Trait para autenticación
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract; // Interfaz autenticación
use Tymon\JWTAuth\Contracts\JWTSubject; // Interfaz JWT

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait


// class Usuario extends Model
// class Usuario extends Model implements Auditable
class Usuario extends Model implements AuthenticatableContract, JWTSubject, Auditable
{
    use SoftDeletes;
    use Authenticatable; // Permite a Lumen tratar este modelo como usuario autenticable
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

    /*Ocultar la clave para que no viaje en los arrays/JSONs del modelo*/
    protected $hidden = [
        'clave',
    ];

    /*Le indica a Lumen qué campo almacena la contraseña*/
    public function getAuthPassword()
    {
        return $this->clave;
    }

    /*Le indica a Lumen qué campo es el identificador único (Primary Key)*/
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    // ==========================================
    // MÉTODOS REQUERIDOS POR JWT (JWTSubject)
    // ==========================================

    /*Le indica a Lumen qué campo es el identificador único (Primary Key),
    Retorna la clave primaria que se guardará en el 'sub' (subject) del JWT.*/
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Devuelve el valor de id_usuario
    }

    /*Retorna un array con datos personalizados para incluir dentro del Payload del JWT.*/
    public function getJWTCustomClaims()
    {
        return [
            'id_empresa' => $this->id_empresa,
            'id_rol'     => $this->id_rol,
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
}
