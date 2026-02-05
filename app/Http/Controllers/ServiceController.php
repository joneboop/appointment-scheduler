<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes']);

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }
}
