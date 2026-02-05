<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function home() {
        return Inertia::render('Welcome', 
        [
            'paskaa'=>'paskaa2'
        ]);

    }
}
