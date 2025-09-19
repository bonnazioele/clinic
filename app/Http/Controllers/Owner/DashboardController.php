<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clinic = $request->attributes->get('ownerClinic');

        $metrics = [
            'doctors' => $clinic->doctors()->count(),
            'secretaries' => $clinic->secretaries()->count(),
            'services' => $clinic->services()->count(),
            'appointments_today' => $clinic->appointments()
                ->whereDate('appointment_date', now()->toDateString())
                ->count(),
        ];

        return view('owner.dashboard', compact('clinic','metrics'));
    }
}
