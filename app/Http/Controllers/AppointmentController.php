<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        // 1) Validate user input (this is your first line of defense)
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // 2) Normalize date inputs to Carbon (reliable comparisons)
        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt   = Carbon::parse($validated['ends_at']);

        // 3) Optional: enforce service duration from the backend (prevents tampering)
        $expectedEnd = $startsAt->copy()->addMinutes((int) $service->duration_minutes);
        if (!$expectedEnd->equalTo($endsAt)) {
            return back()->withErrors([
                'starts_at' => 'Invalid slot length for this service.',
            ]);
        }

        // 4) Conflict check (the key rule)
        // Overlap condition:
        // existing.starts_at < requested.ends_at AND existing.ends_at > requested.starts_at
        $conflictExists = Appointment::query()
            ->booked()
            ->where('service_id', $service->id)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflictExists) {
            return back()->withErrors([
                'starts_at' => 'That slot was just booked. Please choose another time.',
            ]);
        }

        // 5) Create the appointment
        Appointment::create([
            'service_id' => $service->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'booked',
        ]);

        // 6) Redirect back with a flash message
        return back()->with('success', 'Appointment booked successfully!');
    }
}
