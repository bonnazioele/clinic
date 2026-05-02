<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Requests\StoreWalkInRegistrationRequest;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WalkInRegistrationController extends Controller
{
    use InteractsWithClinic;

    public function __construct(protected QueueService $queueService)
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    protected function syncPatientToUser(Patient $patient, array $data, int $clinicId): ?User
    {
        $email = $data['email_address'] ?? null;
        if (! $email) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.local';
            $slug = Str::slug($patient->full_name ?: $patient->first_name ?: 'patient', '');
            $email = sprintf(
                'walkin+%s-%s@%s',
                $slug,
                strtolower($patient->patient_number),
                $domain
            );
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->fill([
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'name' => $patient->full_name,
            'phone' => $patient->mobile_number,
            'address' => $patient->complete_address,
        ]);

        if (! $user->exists) {
            $randomPassword = Str::random(12);
            $user->password = Hash::make($randomPassword);
            $user->is_admin = false;
            $user->is_secretary = false;
            $user->is_doctor = false;
        }

        $user->save();

        $user->clinicsAsPatient()->syncWithoutDetaching([
            $clinicId => ['registered_by' => Auth::id()],
        ]);

        return $user;
    }

    /**
     * Display the walk-in registration form
     */
    public function index(Request $request)
    {
        $activeClinic = $this->activeClinic($request);
        $clinicServices = $activeClinic
            ? $activeClinic->services()->orderBy('name')->get()
            : collect();

        return view('secretary.walkin.index', [
            'clinicServices' => $clinicServices,
            'activeClinic' => $activeClinic,
        ]);
    }

    protected function resolveActiveClinicId(Request $request): ?int
    {
        $clinic = $this->activeClinic($request);
        return $clinic?->id;
    }

    /**
     * Search for existing patients
     */
    public function searchPatient(Request $request)
    {
        $search = $request->input('search');

        if (empty($search)) {
            return response()->json([]);
        }

        $patients = Patient::search($search)
            ->with('latestVisit')
            ->limit(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'patient_number' => $patient->patient_number,
                    'full_name' => $patient->full_name,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'middle_name' => $patient->middle_name,
                    'sex' => $patient->sex,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'age' => $patient->age,
                    'mobile_number' => $patient->mobile_number,
                    'email_address' => $patient->email_address,
                    'complete_address' => $patient->complete_address,
                    'emergency_contact_name' => $patient->emergency_contact_name,
                    'emergency_contact_relationship' => $patient->emergency_contact_relationship,
                    'emergency_contact_number' => $patient->emergency_contact_number,
                    'last_visit' => $patient->latestVisit ? $patient->latestVisit->date_of_visit->format('F d, Y') : 'No previous visits',
                ];
            });

        return response()->json($patients);
    }

    /**
     * Store a new walk-in registration
     */
    public function store(StoreWalkInRegistrationRequest $request)
    {
        $data = $request->validated();
        $clinicId = $this->resolveActiveClinicId($request);

        if (! $clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'Select an active clinic before registering walk-in patients.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Check if this is a new patient or returning patient
            $linkedUser = null;

            if (! empty($data['patient_id'])) {
                // Returning patient - update their information if needed
                $patient = Patient::findOrFail($data['patient_id']);
                $patient->update([
                    'mobile_number' => $data['mobile_number'],
                    'email_address' => $data['email_address'] ?? null,
                    'complete_address' => $data['complete_address'],
                    'emergency_contact_name' => $data['emergency_contact_name'],
                    'emergency_contact_relationship' => $data['emergency_contact_relationship'],
                    'emergency_contact_number' => $data['emergency_contact_number'],
                ]);
            } else {
                // New patient - create patient record
                $patient = Patient::create([
                    'last_name' => $data['last_name'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'sex' => $data['sex'],
                    'date_of_birth' => $data['date_of_birth'],
                    'mobile_number' => $data['mobile_number'],
                    'email_address' => $data['email_address'] ?? null,
                    'complete_address' => $data['complete_address'],
                    'emergency_contact_name' => $data['emergency_contact_name'],
                    'emergency_contact_relationship' => $data['emergency_contact_relationship'],
                    'emergency_contact_number' => $data['emergency_contact_number'],
                ]);
            }

            // Sync walk-in patient to a portal user account
            $linkedUser = $this->syncPatientToUser($patient, $data, $clinicId);

            if ($linkedUser && empty($patient->email_address)) {
                $patient->update(['email_address' => $linkedUser->email]);
            }

            // Create the visit record
            $visit = PatientVisit::create([
                'patient_id' => $patient->id,
                'clinic_id' => $clinicId,
                'registration_staff_id' => Auth::id(),
                'date_of_visit' => now()->toDateString(),
                'time_in' => now(),
                'visit_type' => $data['visit_type'],
                'reason_for_visit' => $data['reason_for_visit'],
                'requested_service' => $data['requested_service'],
                'assigned_department' => $data['assigned_department'] ?? null,
                'patient_type' => $data['patient_type'],
                'priority_level' => $data['priority_level'],
                'consent_to_data_collection' => true,
                'patient_signature' => $data['patient_signature'] ?? null,
                'date_signed' => $data['date_signed'],
                'status' => 'Registered',
            ]);

            $queueEntry = QueueEntry::create([
                'clinic_id' => $clinicId,
                'patient_id' => $patient->id,
                'user_id' => $linkedUser?->id,
                'queue_number' => $this->queueService->getNextNumber($clinicId),
                'status' => 'waiting',
            ]);

            DB::commit();

            event(new QueueUpdated($queueEntry->fresh(), 'created'));

            return response()->json([
                'success' => true,
                'message' => 'Patient registered successfully',
                'data' => [
                    'patient_number' => $patient->patient_number,
                    'visit_number' => $visit->visit_number,
                    'patient_name' => $patient->full_name,
                    'visit_id' => $visit->id,
                    'queue_entry_id' => $queueEntry->id,
                    'queue_number' => $queueEntry->queue_number,
                    'queue_url' => route('secretary.queue.index', $clinicId),
                    'confirmation_url' => route('secretary.walkin.confirmation', $visit->id),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display registration confirmation
     */
    public function confirmation($visitId)
    {
        $visit = PatientVisit::with('patient', 'registrationStaff')
            ->findOrFail($visitId);

        return view('secretary.walkin.confirmation', compact('visit'));
    }

    /**
     * Print registration slip
     */
    public function printSlip($visitId)
    {
        $visit = PatientVisit::with('patient', 'registrationStaff')
            ->findOrFail($visitId);

        return view('secretary.walkin.print', compact('visit'));
    }
}
