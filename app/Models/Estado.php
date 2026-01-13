<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

// class Estado extends Model
class Estado extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'estados';
    protected $primaryKey = 'id_estado';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'estado',
    ];
}
