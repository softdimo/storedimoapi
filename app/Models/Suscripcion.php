<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

// class Usuario extends Model
class Suscripcion extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'suscripciones';
    protected $primaryKey = 'id_suscripcion';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa_suscrita',
        'id_plan_suscrito',
        'dias_trial',
        'id_tipo_pago_suscripcion',
        'valor_suscripcion',
        'fecha_inicial',
        'fecha_final',
        'id_estado_suscripcion',
        'fecha_cancelacion',
        'renovacion_automatica',
        'observaciones_suscripcion'
    ];
}
