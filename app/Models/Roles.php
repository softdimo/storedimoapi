<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

class Roles extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'name',
        'guard_name'
    ];
}
