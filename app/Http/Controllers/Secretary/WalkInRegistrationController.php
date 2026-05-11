<?php

namespace App\Http\Controllers\Secretary;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Requests\StoreWalkInRegistrationRequest;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalkInRegistrationController extends Controller
{
    use InteractsWithClinic;

    public function __construct(protected QueueService $queueService)
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\SecretaryMiddleware::class,
            EnsureSelectedClinic::class,
        ]);
    }

    public function index(Request $request)
    {
        $activeClinic = $this->activeClinic($request);

        $clinicServices = $activeClinic
            ? $activeClinic->services()->orderBy('name')->get()
            : collect();

        $clinicDoctors = collect();

        if ($activeClinic) {
            $clinicDoctors = User::query()
                ->where('is_doctor', true)
                ->whereExists(function ($query) use ($activeClinic) {
                    $query->select(DB::raw(1))
                        ->from('clinic_doctor')
                        ->whereColumn('clinic_doctor.doctor_id', 'users.id')
                        ->where('clinic_doctor.clinic_id', $activeClinic->id);
                })
                ->orderBy('name')
                ->get();

            $doctorServiceRows = DB::table('doctor_service')
                ->join('services', 'services.id', '=', 'doctor_service.service_id')
                ->where('doctor_service.clinic_id', $activeClinic->id)
                ->select(
                    'doctor_service.doctor_id',
                    'services.id',
                    'services.name',
                    'services.description'
                )
                ->orderBy('services.name')
                ->get()
                ->groupBy('doctor_id');

            $clinicDoctors->each(function (User $doctor) use ($doctorServiceRows) {
                $services = ($doctorServiceRows->get($doctor->id) ?? collect())
                    ->map(function ($row) {
                        $service = new Service();

                        $service->id = $row->id;
                        $service->name = $row->name;
                        $service->description = $row->description;

                        return $service;
                    })
                    ->values();

                $doctor->setRelation('services', $services);
            });
        }

        return view('secretary.walkin.index', [
            'clinicServices' => $clinicServices,
            'clinicDoctors' => $clinicDoctors,
            'activeClinic' => $activeClinic,
        ]);
    }

    public function searchPatient(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $patients = Patient::search($search)
            ->with('latestVisit')
            ->limit(10)
            ->get()
            ->map(function (Patient $patient) {
                return [
                    'id' => $patient->id,
                    'patient_number' => $patient->patient_number,
                    'full_name' => $patient->full_name,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'middle_name' => $patient->middle_name,
                    'sex' => $patient->sex,
                    'date_of_birth' => $patient->date_of_birth?->format('Y-m-d'),
                    'age' => $patient->age,
                    'mobile_number' => $patient->mobile_number,
                    'email_address' => $patient->email_address,
                    'complete_address' => $patient->complete_address,
                    'emergency_contact_name' => $patient->emergency_contact_name,
                    'emergency_contact_relationship' => $patient->emergency_contact_relationship,
                    'emergency_contact_number' => $patient->emergency_contact_number,
                    'status' => $patient->status,
                    'last_visit' => $patient->latestVisit
                        ? $patient->latestVisit->date_of_visit?->format('F d, Y')
                        : 'No previous visits',
                ];
            });

        return response()->json($patients);
    }

    public function store(StoreWalkInRegistrationRequest $request)
    {
        $data = $request->validated();

        $activeClinic = $this->activeClinic($request);
        $clinicId = $activeClinic?->id;

        if (! $clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'Select an active clinic before registering walk-in patients.',
            ], 422);
        }

        $doctorId = (int) ($data['doctor_id'] ?? 0);

        $doctor = User::query()
            ->where('id', $doctorId)
            ->where('is_doctor', true)
            ->first();

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'The selected doctor is invalid.',
            ], 422);
        }

        $doctorBelongsToClinic = DB::table('clinic_doctor')
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctor->id)
            ->exists();

        if (! $doctorBelongsToClinic) {
            return response()->json([
                'success' => false,
                'message' => 'The selected doctor is not assigned to the active clinic.',
            ], 422);
        }

        $service = DB::table('doctor_service')
            ->join('services', 'services.id', '=', 'doctor_service.service_id')
            ->where('doctor_service.clinic_id', $clinicId)
            ->where('doctor_service.doctor_id', $doctor->id)
            ->where('services.name', $data['requested_service'])
            ->select('services.id', 'services.name')
            ->first();

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'The selected doctor does not handle this service in the active clinic.',
            ], 422);
        }

        $slot = $this->queueService->findEarliestWalkInSlot(
            $clinicId,
            (int) $doctor->id,
            (int) $service->id,
            now(),
            0
        );

        if (! $slot) {
            return response()->json([
                'success' => false,
                'message' => 'No available time slot remains for this doctor today.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $patient = $this->resolveGuestPatient($data);

            $visit = PatientVisit::create([
                'patient_id' => $patient->id,
                'clinic_id' => $clinicId,
                'registration_staff_id' => Auth::id(),

                'date_of_visit' => now()->toDateString(),
                'time_in' => now(),

                'visit_type' => $data['visit_type'] ?? 'Walk-In',
                'reason_for_visit' => $data['reason_for_visit'] ?? 'Walk-in queue registration',
                'requested_service' => $data['requested_service'],
                'assigned_department' => $data['assigned_department'] ?? $data['requested_service'],
                'patient_type' => $data['patient_type'] ?? ($patient->wasRecentlyCreated ? 'New' : 'Returning'),
                'priority_level' => 'Normal',

                'consent_to_data_collection' => (bool) ($data['consent_to_data_collection'] ?? false),
                'patient_signature' => $data['patient_signature'] ?? null,
                'date_signed' => $data['date_signed'] ?? null,

                'status' => 'Registered',
            ]);

            $queueEntry = $this->queueService->createOrReuseSlotEntry(
                (int) $clinicId,
                (int) $doctor->id,
                now()->toDateString(),
                $slot['time_with_seconds'],
                [
                'patient_id' => $patient->id,
                'user_id' => null,
                'appointment_id' => null,
                'queue_number' => $this->queueService->getSlotQueueNumber(
                    (int) $clinicId,
                    (int) $doctor->id,
                    (int) $service->id,
                    now(),
                    $slot['time_with_seconds']
                ),
                'status' => 'waiting',
                'priority_level' => 'regular',
                'priority_rank' => 5,
                ]
            );

            DB::commit();

            event(new QueueUpdated($queueEntry->fresh(['patient', 'doctor', 'clinic']), 'created'));

            return response()->json([
                'success' => true,
                'message' => 'Guest walk-in patient has been added to the doctor queue.',
                'data' => [
                    'patient_number' => $patient->patient_number,
                    'visit_number' => $visit->visit_number,
                    'patient_name' => $patient->full_name,
                    'patient_status' => $patient->status,
                    'doctor_name' => $doctor->name,
                    'scheduled_slot_time' => $slot['time'],
                    'visit_id' => $visit->id,
                    'queue_entry_id' => $queueEntry->id,
                    'queue_number' => $queueEntry->queue_number,
                    'queue_url' => route('secretary.queue.index', $clinicId),
                    'confirmation_url' => route('secretary.walkin.confirmation', $visit->id),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function confirmation($visitId)
    {
        $visit = PatientVisit::with(['patient', 'registrationStaff', 'clinic'])
            ->findOrFail($visitId);

        return view('secretary.walkin.confirmation', compact('visit'));
    }

    public function printSlip($visitId)
    {
        $visit = PatientVisit::with(['patient', 'registrationStaff', 'clinic'])
            ->findOrFail($visitId);

        return view('secretary.walkin.print', compact('visit'));
    }

    protected function resolveGuestPatient(array $data): Patient
    {
        if (! empty($data['patient_id'])) {
            $patient = Patient::findOrFail($data['patient_id']);

            $patient->update([
                'first_name' => $data['first_name'] ?? $patient->first_name,
                'last_name' => $data['last_name'] ?? $patient->last_name,
                'middle_name' => $data['middle_name'] ?? $patient->middle_name,
                'email_address' => $data['email_address'] ?? $patient->email_address,
                'mobile_number' => $data['mobile_number'] ?? $patient->mobile_number,
                'sex' => $data['sex'] ?? $patient->sex,
                'date_of_birth' => $data['date_of_birth'] ?? $patient->date_of_birth,
                'complete_address' => $data['complete_address'] ?? $patient->complete_address,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? $patient->emergency_contact_name,
                'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? $patient->emergency_contact_relationship,
                'emergency_contact_number' => $data['emergency_contact_number'] ?? $patient->emergency_contact_number,
            ]);

            return $patient;
        }

        $existingPatient = Patient::where('email_address', $data['email_address'])
            ->whereNull('user_id')
            ->first();

        if ($existingPatient) {
            $existingPatient->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? $existingPatient->middle_name,
                'mobile_number' => $data['mobile_number'] ?? $existingPatient->mobile_number,
                'sex' => $data['sex'] ?? $existingPatient->sex,
                'date_of_birth' => $data['date_of_birth'] ?? $existingPatient->date_of_birth,
                'complete_address' => $data['complete_address'] ?? $existingPatient->complete_address,
                'status' => Patient::STATUS_GUEST,
                'registration_token' => $existingPatient->registration_token ?: Str::random(64),
                'registration_token_expires_at' => $existingPatient->registration_token_expires_at ?: now()->addDays(7),
                'registration_invited_at' => $existingPatient->registration_invited_at ?: now(),
            ]);

            return $existingPatient;
        }

        return Patient::create([
            'user_id' => null,
            'status' => Patient::STATUS_GUEST,
            'registration_token' => Str::random(64),
            'registration_token_expires_at' => now()->addDays(7),
            'registration_invited_at' => now(),

            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'email_address' => $data['email_address'],
            'mobile_number' => $data['mobile_number'] ?? null,

            'sex' => $data['sex'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'complete_address' => $data['complete_address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
            'emergency_contact_number' => $data['emergency_contact_number'] ?? null,
        ]);
    }

}