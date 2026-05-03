<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Notifications\ServiceDetachedAppointmentCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClinicServiceController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\SecretaryMiddleware::class,
            EnsureSelectedClinic::class,
        ]);
    }

    private function getActiveClinic(Request $request): Clinic
    {
        $clinic = $request->attributes->get('active_clinic');

        if ($clinic instanceof Clinic) {
            return $clinic;
        }

        $routeClinic = $request->route('clinic');

        if ($routeClinic instanceof Clinic) {
            return $routeClinic;
        }

        if (is_numeric($routeClinic)) {
            $found = Clinic::find($routeClinic);

            if ($found) {
                return $found;
            }
        }

        abort(403, 'Active clinic context is required.');
    }

    public function index(Request $request)
    {
        $clinic = $this->getActiveClinic($request);

        $services = Service::query()
            ->whereHas('clinics', function ($query) use ($clinic) {
                $query->where('clinics.id', $clinic->id);
            })
            ->with([
                'clinics' => function ($query) use ($clinic) {
                    $query->where('clinics.id', $clinic->id);
                },
            ])
            ->orderBy('name')
            ->paginate(12);

        $attachedIds = $clinic->services()->pluck('services.id');
        $serviceIds = $attachedIds->values();

        $availableServices = Service::query()
            ->whereNotIn('id', $attachedIds)
            ->orderBy('name')
            ->get();

        $todayQueueCounts = collect();
        $activeDoctorCounts = collect();
        $doctorInQueueCounts = collect();

        if ($serviceIds->isNotEmpty()) {
            $todayQueueCounts = QueueEntry::query()
                ->where('queue_entries.clinic_id', $clinic->id)
                ->whereIn('queue_entries.status', ['waiting', 'now_serving', 'rescheduled'])
                ->join('appointments', 'appointments.id', '=', 'queue_entries.appointment_id')
                ->whereDate('appointments.appointment_date', today())
                ->whereIn('appointments.service_id', $serviceIds)
                ->selectRaw('appointments.service_id as service_id, COUNT(*) as total')
                ->groupBy('appointments.service_id')
                ->pluck('total', 'service_id');

            $activeDoctorCounts = DB::table('clinic_doctor as cd')
                ->join('doctor_service as ds', function ($join) {
                    $join->on('ds.doctor_id', '=', 'cd.doctor_id')
                        ->on('ds.clinic_id', '=', 'cd.clinic_id');
                })
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
                ->whereDate('a.appointment_date', today())
                ->whereIn('qe.status', ['waiting', 'now_serving'])
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
            'services' => $services,
            'availableServices' => $availableServices,
            'todayQueueCounts' => $todayQueueCounts,
            'activeDoctorCounts' => $activeDoctorCounts,
            'doctorInQueueCounts' => $doctorInQueueCounts,
        ]);
    }

    public function create(Request $request)
    {
        $clinic = $this->getActiveClinic($request);

        return view('secretary.services.create', [
            'clinic' => $clinic,
        ]);
    }

    public function store(Request $request)
    {
        $clinic = $this->getActiveClinic($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
        ]);

        $service = Service::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $clinic->services()->syncWithoutDetaching([
            $service->id => [
                'duration_minutes' => $data['duration_minutes'] ?? 30,
            ],
        ]);

        return redirect()
            ->route('secretary.services.index', ['clinic' => $clinic->id])
            ->with('status', 'Service created and attached to your clinic.');
    }

    public function edit(Request $request, Clinic $clinic, Service $service)
    {
        $activeClinic = $this->getActiveClinic($request);

        abort_if(
            (int) $clinic->id !== (int) $activeClinic->id,
            403,
            'Clinic does not match active clinic context.'
        );

        $belongsToClinic = $clinic->services()
            ->where('services.id', $service->id)
            ->exists();

        abort_if(! $belongsToClinic, 403, 'This service does not belong to your clinic.');

        $service->load([
            'clinics' => function ($query) use ($clinic) {
                $query->where('clinics.id', $clinic->id);
            },
        ]);

        return view('secretary.services.edit', [
            'clinic' => $clinic,
            'service' => $service,
            'clinics' => collect([$clinic]),
        ]);
    }

    public function update(Request $request, Clinic $clinic, Service $service)
    {
        $activeClinic = $this->getActiveClinic($request);

        abort_if(
            (int) $clinic->id !== (int) $activeClinic->id,
            403,
            'Clinic does not match active clinic context.'
        );

        $belongsToClinic = $clinic->services()
            ->where('services.id', $service->id)
            ->exists();

        abort_if(! $belongsToClinic, 403, 'This service does not belong to your clinic.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
        ]);

        $service->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $clinic->services()->syncWithoutDetaching([
            $service->id => [
                'duration_minutes' => $data['duration_minutes'] ?? 30,
            ],
        ]);

        return redirect()
            ->route('secretary.services.index', ['clinic' => $clinic->id])
            ->with('status', 'Service updated successfully.');
    }

    public function destroy(Request $request, Clinic $clinic, Service $service)
    {
        return $this->detach($request, $clinic, $service);
    }

    public function attach(Request $request, Clinic $clinic)
    {
        $activeClinic = $this->getActiveClinic($request);

        abort_if(
            (int) $clinic->id !== (int) $activeClinic->id,
            403,
            'Clinic does not match active clinic context.'
        );

        $data = $request->validate([
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['exists:services,id'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'duration_minutes_by_service' => ['nullable', 'array'],
            'duration_minutes_by_service.*' => ['nullable', 'integer', 'min:5', 'max:480'],
        ]);

        $defaultDuration = (int) ($data['duration_minutes'] ?? 30);
        $durationsByService = $data['duration_minutes_by_service'] ?? [];

        foreach ($data['service_ids'] as $serviceId) {
            if (! $clinic->services()->where('services.id', $serviceId)->exists()) {
                $duration = (int) ($durationsByService[$serviceId] ?? $defaultDuration);

                if ($duration < 5 || $duration > 480) {
                    $duration = $defaultDuration;
                }

                $clinic->services()->attach($serviceId, [
                    'duration_minutes' => $duration,
                ]);
            }
        }

        return redirect()
            ->route('secretary.services.index', ['clinic' => $clinic->id])
            ->with('status', 'Service(s) attached to clinic.');
    }

    public function search(Request $request)
    {
        $clinic = $this->getActiveClinic($request);

        $term = trim((string) $request->query('q', ''));
        $attachedIds = $clinic->services()->pluck('services.id');

        $results = Service::query()
            ->whereNotIn('id', $attachedIds)
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends(['q' => $term]);

        return response()->json([
            'data' => $results->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                ];
            })->values(),
            'next_page_url' => $results->nextPageUrl(),
        ]);
    }

    public function detach(Request $request, Clinic $clinic, Service $service)
    {
        $activeClinic = $this->getActiveClinic($request);

        abort_if(
            (int) $clinic->id !== (int) $activeClinic->id,
            403,
            'Clinic does not match active clinic context.'
        );

        if (! $clinic->services()->where('services.id', $service->id)->exists()) {
            return redirect()
                ->route('secretary.services.index', ['clinic' => $clinic->id])
                ->with('error', 'Service is not attached to this clinic.');
        }

        $activeQueueCount = $this->activeQueueCount($clinic, $service);
        $linkedDoctorIds = $this->linkedActiveDoctorIds($clinic, $service);

        $hasActiveDependencies = $activeQueueCount > 0 || $linkedDoctorIds->isNotEmpty();

        if ($hasActiveDependencies && ! $request->boolean('proceed_detach')) {
            return redirect()
                ->route('secretary.services.index', ['clinic' => $clinic->id])
                ->with('warning', 'Detach requires confirmation because this service is linked to today’s queue entries and/or active doctors.');
        }

        try {
            $affectedAppointments = $this->proceedDetach($clinic, $service, $linkedDoctorIds);
            $this->notifyAffectedUsersForCancelledAppointments($affectedAppointments, $clinic, $service);
        } catch (\Throwable $error) {
            report($error);

            return redirect()
                ->route('secretary.services.index', ['clinic' => $clinic->id])
                ->with('error', 'Unable to detach service right now. Please try again.');
        }

        $successMessage = $hasActiveDependencies
            ? 'Service detached from clinic. Related appointments are cancelled and affected users are notified.'
            : 'Service detached from clinic.';

        return redirect()
            ->route('secretary.services.index', ['clinic' => $clinic->id])
            ->with('status', $successMessage);
    }

    private function activeQueueCount(Clinic $clinic, Service $service): int
    {
        return QueueEntry::query()
            ->where('queue_entries.clinic_id', $clinic->id)
            ->whereIn('queue_entries.status', ['waiting', 'now_serving', 'rescheduled'])
            ->join('appointments', 'appointments.id', '=', 'queue_entries.appointment_id')
            ->whereDate('appointments.appointment_date', today())
            ->where('appointments.service_id', $service->id)
            ->count();
    }

    private function linkedActiveDoctorIds(Clinic $clinic, Service $service): Collection
    {
        return DB::table('clinic_doctor as cd')
            ->join('doctor_service as ds', function ($join) use ($service) {
                $join->on('ds.doctor_id', '=', 'cd.doctor_id')
                    ->on('ds.clinic_id', '=', 'cd.clinic_id')
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
                $appointment->update([
                    'status' => 'cancelled',
                ]);
            }

            $clinic->services()->detach($service->id);

            DB::table('doctor_service')
                ->where('clinic_id', $clinic->id)
                ->where('service_id', $service->id)
                ->delete();

            if ($linkedDoctorIds->isNotEmpty() && Schema::hasTable('doctor_service_schedule')) {
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

            if (
                $appointment->doctor &&
                (! $appointment->user || (int) $appointment->doctor->id !== (int) $appointment->user->id)
            ) {
                $appointment->doctor->notify($notification);
            }
        }
    }
}