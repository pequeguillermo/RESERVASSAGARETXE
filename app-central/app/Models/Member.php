<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['name', 'phone', 'qr_token', 'active'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }
}
