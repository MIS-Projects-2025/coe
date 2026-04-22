<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoeRecord extends Model
{
    protected $table = 'coe_record';

    public $timestamps = false;

    protected $fillable = [
        'empid',
        'purpose',
        'date_request',
        'coe_type',
        'approver1_emp_num',
        'approver2_emp_num',
        'status',
        'remarks',
        'pcn_status',
    ];

    protected $casts = [
        'date_request' => 'datetime',
    ];
}
