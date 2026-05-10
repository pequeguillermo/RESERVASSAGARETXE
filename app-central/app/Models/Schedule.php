<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'open_time',
        'close_time',
        'open_time_2',
        'close_time_2',
        'is_closed'
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];
}
