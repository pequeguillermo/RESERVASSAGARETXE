<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialSchedule extends Model
{
    protected $fillable = [
        'date',
        'open_time',
        'close_time',
        'open_time_2',
        'close_time_2',
        'is_closed',
        'is_permanent',
        'max_diners'
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
        'is_permanent' => 'boolean',
    ];
}
