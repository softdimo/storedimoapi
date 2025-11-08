<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class Producto extends Model
class Producto extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'id_empresa',
        'id_persona',
        'id_proveedor',
        'id_tipo_persona',
        'imagen_producto',
        'nombre_producto',
        'id_categoria',
        'precio_unitario',
        'precio_detal',
        'precio_por_mayor',
        'descripcion',
        'stock_minimo',
        'id_estado',
        'tamano',
        'cantidad',
        'referencia',
        'fecha_vencimiento',
        'id_umd'
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
