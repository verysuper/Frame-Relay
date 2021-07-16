<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test_cron extends Model
{
    protected $table = 'test_cron';

    protected $fillable = [
        'id',
        'datetime',
    ];
}
