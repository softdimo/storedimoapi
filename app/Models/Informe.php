<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    protected $connection = 'mysql';
    protected $table = 'informe';
    protected $primaryKey = 'informe_codigo';
    protected $fillable = [
        'informe_descripcion',
        'tabla_principal',
        'where_principal'
    ];
    public $timestamps = true;
}