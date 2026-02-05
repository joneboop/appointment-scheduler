<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function create(Service $service)
    {
        return Inertia::render('Booking/Create', [
            'service' => $service->only(['id', 'name', 'duration_minutes']),
        ]);
    }
}
