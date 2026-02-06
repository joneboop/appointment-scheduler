# Appointment Scheduler Lite

A simple appointment booking application built with **Laravel, Inertia.js, and Vue 3**.  
The project demonstrates server-side availability calculation, conflict-safe booking, and a clean full-stack architecture.

---

## Features

- Service-based appointment booking
- Server-side availability calculation
- Conflict prevention to avoid double bookings
- Reactive booking UI with Vue + Inertia
- Appointment persistence with SQLite
- Simple admin view to list booked appointments

---

## Tech Stack

- **Backend:** PHP 8+, Laravel
- **Frontend:** Vue 3, Inertia.js
- **Styling:** Tailwind CSS
- **Database:** SQLite

---

## How It Works

1. The backend calculates available time slots for a service based on:
   - Business hours (hardcoded: 09:00–17:00)
   - Service duration
   - Existing appointments

2. The frontend fetches availability dynamically when a user selects a date.

3. When a user books a slot:
   - The backend re-checks for overlapping appointments
   - The booking is only saved if no conflict exists
   - The slot disappears from availability after booking

Availability is treated as a suggestion; the server is always authoritative.

---