<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\QueueEntry;
use App\Models\User;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', AdminMiddleware::class]);
    }

    public function index()
    {
        $stats = [
            'clinics' => Clinic::count(),
            'services' => Service::count(),
            'users' => User::count(),
            'appointments' => Appointment::count(),
        ];

        // Pending clinic applicants (status = 'pending')
        $pendingClinics = Clinic::where('status', 'pending')
            ->latest()
            ->take(6)
            ->get(['id','name','address','email','contact_number','created_at']);
        $pendingCount = Clinic::where('status', 'pending')->count();

        return view('admin.dashboard', array_merge($stats, [
            'pendingClinics' => $pendingClinics,
            'pendingCount' => $pendingCount,
        ]));
    }
}
