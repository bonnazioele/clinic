<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\DoctorMiddleware::class,
            EnsureSelectedClinic::class,
        ]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);
        $activeClinic = $this->activeClinic($request);
        $today = now()->toDateString();

        $services = $doctor->servicesForClinic($activeClinicId)
            ->orderBy('services.name')
            ->get(['services.id', 'services.name']);

        $serviceId = (int) $request->query('service_id', 0);
        if ($serviceId > 0 && ! $services->pluck('id')->contains($serviceId)) {
            $serviceId = 0;
        }

        $appointments = Appointment::with(['clinic', 'user', 'service'])
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $activeClinicId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->when($serviceId > 0, function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->orderBy('appointment_time')
            ->get();

        $queue = QueueEntry::query()
            ->withDashboardRelations()
            ->where('clinic_id', $activeClinicId)
            ->forDoctor($doctor->id)
            ->whereIn('status', QueueEntry::doctorQueueVisibleStatuses())
            ->when($serviceId > 0, function ($query) use ($serviceId) {
                $query->whereHas('appointment', function ($appointmentQuery) use ($serviceId) {
                    $appointmentQuery->where('service_id', $serviceId);
                });
            })
            ->where(function ($query) use ($today) {
                $query->where(function ($appointmentQueue) use ($today) {
                    $appointmentQueue->whereNotNull('appointment_id')
                        ->whereHas('appointment', function ($appointmentQuery) use ($today) {
                            $appointmentQuery->whereDate('appointment_date', $today);
                        });
                })->orWhere(function ($walkInQueue) use ($today) {
                    $walkInQueue->whereNull('appointment_id')
                        ->whereNotNull('patient_id')
                        ->whereDate('created_at', $today);
                });
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'now_serving' THEN 0
                    WHEN status = 'in_progress' THEN 0
                    WHEN status = 'called' THEN 1
                    WHEN status = 'waiting' THEN 2
                    WHEN status = 'rescheduled' THEN 3
                    WHEN status IN ('served', 'completed') THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('queue_number')
            ->get();

        return view('doctor.dashboard', compact(
            'doctor',
            'appointments',
            'queue',
            'activeClinic',
            'services',
            'serviceId'
        ));
    }
}
