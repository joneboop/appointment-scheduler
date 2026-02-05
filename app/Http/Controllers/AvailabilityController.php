<?php

namespace App\Http\Controllers;


use App\Models\Service;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request, AvailabilityService $availability)
    {
        //Basic validation
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = Service::findOrFail($validated['service_id']);

        $slots = $availability->getAvailableSlots(
            $service,
            $validated['date'],
            interval: 15
        );

        return response()->json([
            'service_id' => $service->id,
            'date' => $validated['date'],
            'slots' => $slots,
        ]);
    }
}
