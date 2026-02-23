<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

// use App\Traits\AuditableTrait;

// class TipoPersona extends Model
class Metrica extends Model implements Auditable
{
    // use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'traffic_logs';
    protected $primaryKey = 'id_log';
    // protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'tenant_db',
        'source',
        'method',
        'path',
        'ip',
        'status_code',
        'user_agent',
    ];
}
