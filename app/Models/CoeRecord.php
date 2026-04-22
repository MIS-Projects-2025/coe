<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoeRecord extends Model
{
    protected $table = 'coe_record';

    public $timestamps = false;

    protected $fillable = [
        'employid',
        'emp_position',
        'emp_class',
        'emp_sex',
        'purpose',
        'date_request',
        'coe_type',
        'status',
        'remarks',
        'pcn_status',
    ];

    protected $casts = [
        'date_request' => 'datetime',
    ];
}
