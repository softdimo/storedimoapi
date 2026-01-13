<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class PeriodoPago extends Model
class PeriodoPago extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'periodos_pago';
    protected $primaryKey = 'id_periodo_pago';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'periodo_pago'
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
