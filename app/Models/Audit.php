<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Audit extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'audits';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        Config::set('database.default', 'mysql');
        $this->setConnection('mysql');
    }
}
