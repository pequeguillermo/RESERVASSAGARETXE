<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['member_id', 'name', 'phone', 'email', 'date', 'people', 'notes', 'discount_applied', 'status'];

    protected $casts = [
        'date' => 'datetime',
        'discount_applied' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
