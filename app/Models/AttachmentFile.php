<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachmentFile extends Model
{
    protected $table = 'attachment_files';

    public $timestamps = false;

    protected $fillable = [
        'file_id',
        'employid',
        'original_file_name',
        'file_location',
        'file_name',
        'file_type',
        'file_size',
        'date_filed',
    ];

    protected $casts = [
        'date_filed' => 'datetime',
        'file_size'  => 'integer',
    ];
}
