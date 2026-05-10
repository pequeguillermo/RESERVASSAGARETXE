<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    protected $fillable = ['member_id', 'method'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
