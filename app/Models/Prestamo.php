<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class Prestamo extends Model
class Prestamo extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'prestamos';
    protected $primaryKey = 'id_prestamo';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_empresa',
        'id_estado_prestamo',
        'id_usuario',
        'valor_prestamo',
        'fecha_prestamo',
        'fecha_limite',
        'descripcion'
    ];
}
