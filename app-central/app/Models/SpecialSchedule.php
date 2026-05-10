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
        'is_closed'
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];
}
