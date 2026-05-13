<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'member_id', 'name', 'phone', 'email', 'date', 'people', 'notes', 'discount_applied', 'status',
        'allergies', 'celiac', 'strollers', 'reduced_mobility', 'wheelchairs', 'adults', 'children'
    ];

    protected $casts = [
        'date' => 'datetime',
        'discount_applied' => 'boolean',
        'allergies' => 'boolean',
        'celiac' => 'boolean',
        'strollers' => 'boolean',
        'reduced_mobility' => 'boolean',
        'wheelchairs' => 'boolean',
        'adults' => 'integer',
        'children' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
