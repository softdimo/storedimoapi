<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
// use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

use App\Traits\AuditableTrait;

// class Genero extends Model
class Genero extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $table = 'generos';
    protected $primaryKey = 'id_genero';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'genero',
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
