<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name', 'surname', 'dni', 'postal_code', 'birth_date', 'address', 
        'phone', 'email', 'qr_token', 'active',
        'pref_space', 'pref_food', 'pref_drink1', 'pref_drink2', 'pref_time', 'how_knew_us'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }
}
