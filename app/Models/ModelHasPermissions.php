<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

class ModelHasPermissions extends Model implements Auditable
{
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'model_has_permissions';
    public $timestamps = false;
    protected $fillable = [
        'permission_id',
        'model_type',
        'model_id'
    ];
}
