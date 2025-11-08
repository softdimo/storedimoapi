<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class Venta extends Model
class Venta extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_tipo_cliente',
        'fecha_venta',
        'descuento',
        'subtotal_venta',
        'total_venta',
        'id_tipo_pago',
        'id_producto',
        'id_cliente',
        'id_usuario',
        'id_estado_credito',
        'fecha_limite_credito'
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
