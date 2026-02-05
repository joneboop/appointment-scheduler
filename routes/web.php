<?php


use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//    return Inertia::render('Welcome');
// });


Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/book/{service}', [BookingController::class, 'create'])->name('booking.create');

//Route::get('/', [ScheduleController::class, 'home'])->name('homer');
Route::get('/availability', AvailabilityController::class);

Route::get('/paskaa', function () {
    return Inertia::render('Test');
});

//Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

require __DIR__.'/auth.php';
