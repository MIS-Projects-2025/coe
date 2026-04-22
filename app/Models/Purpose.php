<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $table = 'purpose';

    public $timestamps = false;

    protected $fillable = [
        'purpose',
        'created_by_emp_num',
        'date_created',
        'updated_by_emp_num',
        'date_updated',
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'date_updated' => 'datetime',
    ];

    /**
     * COE records that use this purpose.
     */
    public function coeRecords()
    {
        return $this->hasMany(CoeRecord::class, 'purpose', 'purpose');
    }
}
