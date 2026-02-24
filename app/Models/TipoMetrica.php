<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

// use App\Traits\AuditableTrait;

// class TipoPersona extends Model
class TipoMetrica extends Model implements Auditable
{
    // use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'tipos_metrica';
    protected $primaryKey = 'id_tipo_metrica';
    // protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'tipo_metrica',
    ];
}
