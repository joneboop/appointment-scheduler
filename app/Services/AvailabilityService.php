<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    /**
     * Hardcoded business hours
     */
    private const OPEN_TIME = '09:00';
    private const CLOSE_TIME = '17:00';

    /**
     * Returns available start times
     *
     * @param  Service  $service
     * @param  string   $date       YYYY-MM-DD
     * @param  int      $interval   slot step in minutes (e.g. 15)
     * @return array<int, array{start:string,end:string}>
     */
    public function getAvailableSlots(Service $service, string $date, int $interval = 15): array
    {
        //Parse the day boundaries using an immutable date-time (safer)
        $day = CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();

        // Create opening and closing DateTime for that specific day
        $open = CarbonImmutable::parse($day->format('Y-m-d') . ' ' . self::OPEN_TIME);
        $close = CarbonImmutable::parse($day->format('Y-m-d') . ' ' . self::CLOSE_TIME);

        // 2) The service duration determines the slot length
        $duration = (int) $service->duration_minutes;

        logger()->info('Availability debug', [
        'service_id' => $service->id,
        'date' => $date,
        'open' => $open->toDateTimeString(),
        'close' => $close->toDateTimeString(),
        'now' => now()->toDateTimeString(),
        'duration_minutes' => $duration,
                    ]);


        // If duration is invalid or business hours are impossible, return no slots
        if ($duration <= 0 || $open->greaterThanOrEqualTo($close)) {
            return [];
        }

        // 3) Fetch existing appointments for that service on that day (booked only)
        // We take anything that overlaps the business-hour window.
        $existing = Appointment::query()
            ->booked()
            ->where('service_id', $service->id)
            ->where('starts_at', '<', $close)  // starts before closing
            ->where('ends_at', '>', $open)     // ends after opening
            ->orderBy('starts_at')
            ->get(['starts_at', 'ends_at']);

        // Convert appointments to simple intervals in memory for fast checks
        $busyIntervals = $existing->map(function ($appt) {
            return [
                'start' => Carbon::parse($appt->starts_at),
                'end'   => Carbon::parse($appt->ends_at),
            ];
        });

        // 4) Generate candidate slots from open -> close, stepping by $interval
        $slots = [];
        $cursor = $open;

        // Last possible start time is close - duration
        $lastStart = $close->subMinutes($duration);

        while ($cursor->lessThanOrEqualTo($lastStart)) {
            $slotStart = $cursor;
            $slotEnd = $cursor->addMinutes($duration);

            // 5) Exclude slots in the past if the date is today
            // (This is UX-friendly and avoids booking past times.)
            if ($slotStart->isSameDay(now()) && $slotStart->lt(now())) {
                $cursor = $cursor->addMinutes($interval);
                continue;
            }


            // 6) Check overlap with any existing appointment
            if (!$this->overlapsAny($slotStart, $slotEnd, $busyIntervals)) {
                $slots[] = [
                    'start' => $slotStart->toIso8601String(),
                    'end'   => $slotEnd->toIso8601String(),
                ];
            }

            $cursor = $cursor->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * Determines if a slot interval overlaps any busy interval.
     *
     * Overlap rule:
     * busy.start < slotEnd AND busy.end > slotStart
     */
    private function overlapsAny(CarbonImmutable $slotStart, CarbonImmutable $slotEnd, $busyIntervals): bool
    {
        foreach ($busyIntervals as $busy) {
            $busyStart = $busy['start'];
            $busyEnd = $busy['end'];

            // Convert to comparable CarbonImmutable
            $busyStart = CarbonImmutable::instance($busyStart);
            $busyEnd = CarbonImmutable::instance($busyEnd);

            $overlaps = $busyStart->lt($slotEnd) && $busyEnd->gt($slotStart);

            if ($overlaps) {
                return true;
            }
        }

        return false;
    }

    /**
     * Exclude past slots if booking "today".
     */
    private function isInPast(CarbonImmutable $slotStart): bool
    {
        $now = CarbonImmutable::now();
        return $slotStart->lt($now);
    }
}
