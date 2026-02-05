<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'customer_name',
        'customer_email',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $attributes = [
        'status' => 'booked',
    ];


    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];


    public function service()
    {
        return $this->belongsTo(Service::class);
    }


    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }


    public function overlaps($start, $end): bool
    {
        return $this->starts_at < $end && $this->ends_at > $start;
    }
}
