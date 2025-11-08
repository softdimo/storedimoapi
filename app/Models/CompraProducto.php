<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class CompraProducto extends Model
class CompraProducto extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'compra_productos';
    protected $primaryKey = 'id_compra_producto';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario_compra',
        'subtotal'
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
