<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminList extends Model
{
    protected $table = 'admin_list';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'created_by_emp_num',
        'date_created',
        'updated_by_emp_num',
        'date_updated',
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'date_updated' => 'datetime',
    ];
}
