<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Notifications\ServiceDetachedAppointmentCancelled;

class ClinicServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class]);
    }

    public function index(Request $request)
    {
        $clinic = $this->activeClinic($request);

        $clinic->load('services');
        $attachedIds = $clinic->services->pluck('id');
        $availableServices = Service::whereNotIn('id', $attachedIds)->orderBy('name')->get();

        $serviceIds = $attachedIds->values();
        $activeQueueCounts = collect();
        $activeDoctorCounts = collect();
        $doctorInQueueCounts = collect();

        if ($serviceIds->isNotEmpty()) {
            $activeQueueCounts = QueueEntry::query()
                ->where('queue_entries.clinic_id', $clinic->id)
                ->whereIn('queue_entries.status', ['waiting', 'now_serving', 'rescheduled'])
                ->join('appointments', 'appointments.id', '=', 'queue_entries.appointment_id')
                ->whereIn('appointments.service_id', $serviceIds)
                ->selectRaw('appointments.service_id as service_id, COUNT(*) as total')
                ->groupBy('appointments.service_id')
                ->pluck('total', 'service_id');

            $activeDoctorCounts = DB::table('clinic_doctor as cd')
                ->join('doctor_service as ds', 'ds.doctor_id', '=', 'cd.doctor_id')
                ->join('users as u', 'u.id', '=', 'cd.doctor_id')
                ->where('cd.clinic_id', $clinic->id)
                ->whereIn('ds.service_id', $serviceIds)
                ->where('u.is_doctor', true)
                ->where('u.is_active', true)
                ->selectRaw('ds.service_id as service_id, COUNT(DISTINCT cd.doctor_id) as total')
                ->groupBy('ds.service_id')
                ->pluck('total', 'service_id');

            $doctorInQueueCounts = DB::table('queue_entries as qe')
                ->join('appointments as a', 'a.id', '=', 'qe.appointment_id')
                ->join('users as u', 'u.id', '=', 'a.doctor_id')
                ->join('clinic_doctor as cd', function ($join) {
                    $join->on('cd.doctor_id', '=', 'a.doctor_id')
                        ->on('cd.clinic_id', '=', 'qe.clinic_id');
                })
                ->where('qe.clinic_id', $clinic->id)
                ->whereIn('qe.status', ['waiting', 'now_serving', 'rescheduled'])
                ->whereNotNull('a.doctor_id')
                ->whereIn('a.service_id', $serviceIds)
                ->where('u.is_doctor', true)
                ->where('u.is_active', true)
                ->selectRaw('a.service_id as service_id, COUNT(DISTINCT a.doctor_id) as total')
                ->groupBy('a.service_id')
                ->pluck('total', 'service_id');
        }

        return view('secretary.services.index', [
            'clinic' => $clinic,
            'services' => $clinic->services->sortBy('name'),
            'availableServices' => $availableServices,
            'activeQueueCounts' => $activeQueueCounts,
            'activeDoctorCounts' => $activeDoctorCounts,
            'doctorInQueueCounts' => $doctorInQueueCounts,
        ]);
    }

    public function attach(Request $request, Clinic $clinic)
    {
        $activeClinic = $this->activeClinic($request);
        abort_if((int) $clinic->id !== (int) $activeClinic->id, 403, 'Clinic does not match active clinic context.');

        $data = $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'duration_minutes' => 'nullable|integer|min:5|max:480'
        ]);
        $duration = $data['duration_minutes'] ?? 30;
        foreach ($data['service_ids'] as $sid) {
            if (! $clinic->services()->where('services.id',$sid)->exists()) {
                $clinic->services()->attach($sid, ['duration_minutes' => $duration]);
            }
        }
        return redirect()->route('secretary.services.index')
            ->with('status','Service(s) attached to clinic.');
    }

    public function detach(Request $request, Clinic $clinic, Service $service)
    {
        $activeClinic = $this->activeClinic($request);
        abort_if((int) $clinic->id !== (int) $activeClinic->id, 403, 'Clinic does not match active clinic context.');

        if (! $clinic->services()->where('services.id',$service->id)->exists()) {
            return back()->with('error','Service not attached to clinic.');
        }

        $activeQueueCount = $this->activeQueueCount($clinic, $service);
        $linkedDoctorIds = $this->linkedActiveDoctorIds($clinic, $service);
        $hasActiveDependencies = $activeQueueCount > 0 || $linkedDoctorIds->isNotEmpty();

        if ($hasActiveDependencies && ! $request->boolean('proceed_detach')) {
            return back()->with('warning', 'Detach requires confirmation because this service is linked to active queue entries and/or active doctors.');
        }

        try {
            $affectedAppointments = $this->proceedDetach($clinic, $service, $linkedDoctorIds);
            $this->notifyAffectedUsersForCancelledAppointments($affectedAppointments, $clinic, $service);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Unable to detach service right now. Please try again.');
        }

        $successMessage = $hasActiveDependencies
            ? 'Service detached from clinic. Related appointments are cancelled and affected users are notified.'
            : 'Service detached from clinic.';

        return back()->with('status', $successMessage);
    }

    private function activeQueueCount(Clinic $clinic, Service $service): int
    {
        return QueueEntry::query()
            ->where('queue_entries.clinic_id', $clinic->id)
            ->whereIn('queue_entries.status', ['waiting', 'now_serving', 'rescheduled'])
            ->join('appointments', 'appointments.id', '=', 'queue_entries.appointment_id')
            ->where('appointments.service_id', $service->id)
            ->count();
    }

    private function linkedActiveDoctorIds(Clinic $clinic, Service $service): Collection
    {
        return DB::table('clinic_doctor as cd')
            ->join('doctor_service as ds', function ($join) use ($service) {
                $join->on('ds.doctor_id', '=', 'cd.doctor_id')
                    ->where('ds.service_id', '=', $service->id);
            })
            ->join('users as u', 'u.id', '=', 'cd.doctor_id')
            ->where('cd.clinic_id', $clinic->id)
            ->where('u.is_doctor', true)
            ->where('u.is_active', true)
            ->distinct()
            ->pluck('cd.doctor_id');
    }

    private function proceedDetach(Clinic $clinic, Service $service, Collection $linkedDoctorIds): Collection
    {
        return DB::transaction(function () use ($clinic, $service, $linkedDoctorIds) {
            $appointmentsToCancel = Appointment::query()
                ->where('clinic_id', $clinic->id)
                ->where('service_id', $service->id)
                ->where('status', '!=', 'cancelled')
                ->with(['user:id,name', 'doctor:id,name'])
                ->lockForUpdate()
                ->get();

            foreach ($appointmentsToCancel as $appointment) {
                $appointment->update(['status' => 'cancelled']);
            }

            $clinic->services()->detach($service->id);

            if ($linkedDoctorIds->isNotEmpty()) {
                DB::table('doctor_service')
                    ->where('service_id', $service->id)
                    ->whereIn('doctor_id', $linkedDoctorIds)
                    ->delete();

                if (Schema::hasTable('doctor_service_schedule')) {
                    $scheduleQuery = DB::table('doctor_service_schedule')
                        ->where('service_id', $service->id);

                    if (Schema::hasColumn('doctor_service_schedule', 'clinic_id')) {
                        $scheduleQuery->where('clinic_id', $clinic->id);
                    }

                    if (Schema::hasColumn('doctor_service_schedule', 'doctor_id')) {
                        $scheduleQuery->whereIn('doctor_id', $linkedDoctorIds);
                    }

                    $scheduleQuery->delete();
                }
            }

            return $appointmentsToCancel;
        });
    }

    private function notifyAffectedUsersForCancelledAppointments(Collection $appointments, Clinic $clinic, Service $service): void
    {
        foreach ($appointments as $appointment) {
            $notification = new ServiceDetachedAppointmentCancelled($appointment, $clinic, $service);

            if ($appointment->user) {
                $appointment->user->notify($notification);
            }

            if ($appointment->doctor && (! $appointment->user || (int) $appointment->doctor->id !== (int) $appointment->user->id)) {
                $appointment->doctor->notify($notification);
            }
        }
    }

    private function activeClinic(Request $request): Clinic
    {
        $activeClinic = $request->attributes->get('active_clinic');

        abort_if(! $activeClinic instanceof Clinic, 403, 'Active clinic context is required.');

        return $activeClinic;
    }
}
