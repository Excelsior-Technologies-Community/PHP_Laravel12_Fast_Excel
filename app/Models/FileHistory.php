<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileHistory extends Model
{

    protected $fillable = [

        'file_name',

        'type',

        'total_rows',

        'success_rows',

        'failed_rows',

        'errors'

    ];


    protected $casts = [

        'errors' => 'array'

    ];

}