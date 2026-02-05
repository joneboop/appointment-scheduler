<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
        protected $fillable = [
        'user_id',
        'name',
        'duration_minutes',
        'is_active',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
