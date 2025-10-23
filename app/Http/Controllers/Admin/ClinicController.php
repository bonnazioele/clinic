<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ClinicApprovedMail;
use App\Mail\ClinicDeclinedMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ClinicStatusLog;
use App\Models\ClinicUpdateLog;

class ClinicController extends Controller
{

    public function index(Request $request)
    {
        // Show all clinics by default; filter by status if provided
        $query = Clinic::with(['services', 'secretaries']);

        if ($request->filled('name')) {
            $name = trim((string) $request->input('name'));
            $query->where('name', 'like', "%{$name}%");
        }
        if ($request->filled('status')) {
            $status = strtolower((string) $request->input('status'));
            $query->where('status', $status);
        }

        $clinics = $query->latest()->paginate(10)->withQueryString();

        foreach ($clinics as $clinic) {
            $clinic->doctor_count = User::where('is_doctor', true)->whereHas('clinics', fn($q) => $q->where('clinic_id', $clinic->id))->count();
            $clinic->secretary_count = $clinic->secretaries()->count();
    
            $clinic->contact_name = trim(($clinic->contact_first_name ?? '') . ' ' . ($clinic->contact_last_name ?? ''));
            $clinic->contact_email = $clinic->contact_person_email ?: $clinic->email;
            $user = User::where('email', $clinic->contact_email)->first();
            $clinic->user_status = $user ? ($user->is_active ? 'Active' : 'Not Active') : 'No Account';
        }

        return view('admin.clinics.index', [
            'clinics' => $clinics,
        ]);
    }

    public function create()
    {
        $services = Service::orderBy('name')->get(['id', 'name']);
        return view('admin.clinics.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],
            'contact_person_email' => ['required', 'email', 'max:255'],
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_address' => ['required', 'string', 'max:1024'],
            'clinic_contact' => ['required', 'string', 'max:50'],
            'branch_code' => ['required', 'string', 'max:255', Rule::unique('clinics', 'branch_code')->whereNull('deleted_at')],
            'clinic_email' => ['required', 'email', 'max:255', Rule::unique('clinics', 'email')->whereNull('deleted_at')],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic = \DB::transaction(function () use ($data, $logoPath) {
            $clinic = Clinic::create([
                'name' => $data['clinic_name'],
                'branch_code' => $data['branch_code'],
                'address' => $data['clinic_address'],
                'contact_number' => $data['clinic_contact'],
                'email' => $data['clinic_email'],
                'contact_first_name' => $data['contact_first_name'],
                'contact_last_name' => $data['contact_last_name'],
                'contact_person_email' => $data['contact_person_email'],
                'logo' => $logoPath,
                'gps_latitude' => $data['latitude'],
                'gps_longitude' => $data['longitude'],
                'status' => 'approved',
            ]);

            $clinic->services()->sync($data['service_ids'] ?? []);

            return $clinic;
        });

        [$owner, $tempPasswordPlain, $contactEmail, $emailName] = $this->finalizeClinicApproval($clinic);

        $loginUrl = route('login');
        try {
            Mail::to($contactEmail)->send(new ClinicApprovedMail(
                clinicName: $clinic->name,
                name: $emailName,
                email: $contactEmail,
                password: $tempPasswordPlain,
                loginUrl: $loginUrl
            ));
        } catch (\Throwable $e) {
            Log::error('Failed sending ClinicApprovedMail', [
                'clinic_id' => $clinic->id,
                'email' => $contactEmail,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.clinics.index')->with('status', 'Clinic added and approved. Account credentials emailed to the contact person.');
    }

    public function show(Clinic $clinic)
    {
        $clinic->load(['services', 'secretaries:id,name,email,phone', 'doctors:id,name,email,phone']);

        $todayAppointments = $clinic->appointments()->whereDate('appointment_date', today())->count();
        $totalAppointments = $clinic->appointments()->count();
        $waitingCount = $clinic->queueEntries()->where('status','waiting')->count();
        $servedToday = $clinic->queueEntries()->where('status','served')->whereDate('served_at', today())->count();

        return view('admin.clinics.show', [
            'clinic' => $clinic,
            'todayAppointments' => $todayAppointments,
            'totalAppointments' => $totalAppointments,
            'waitingCount' => $waitingCount,
            'servedToday' => $servedToday,
        ]);
    }

    public function showApplication(Clinic $clinic)
    {
        // Load related data for the application detail view
        $clinic->load(['services']);

        return view('admin.clinics.application-detail', compact('clinic'));
    }

    public function edit(Clinic $clinic)
    {
        $services = Service::all();
        return view('admin.clinics.edit', compact('clinic','services'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => [
                'required','string','max:255',
                Rule::unique('clinics','branch_code')->whereNull('deleted_at')->ignore($clinic->id)
            ],
            'contact_number' => 'required|string|max:50',
            'email'          => [
                'required','email','max:255',
                Rule::unique('clinics','email')->whereNull('deleted_at')->ignore($clinic->id)
            ],
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'nullable|array',
            'service_ids.*'  => 'exists:services,id',
        ]);


        if ($request->hasFile('logo')) {
            if ($clinic->logo && \Storage::disk('public')->exists($clinic->logo)) {
                \Storage::disk('public')->delete($clinic->logo);
            }
            $data['logo'] = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic->update([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
            'branch_code'    => $data['branch_code'],
            'contact_number' => $data['contact_number'],
            'email'          => $data['email'],
            'logo'           => $data['logo'] ?? $clinic->logo,
        ]);


        $clinic->services()->sync($data['service_ids'] ?? []);

        return redirect()->route('admin.clinics.index')->with('status', 'Clinic updated successfully.');
    }

    public function destroy(Clinic $clinic)
    {
        $id = $clinic->id;
        $clinic->delete();
        ClinicStatusLog::create([
            'clinic_id'    => $id,
            'action'       => 'deleted',
            'reason'       => null,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
        ]);
        return back()->with('status','Clinic removed.');
    }

    public function approve(Clinic $clinic)
    {
        if ($clinic->isApprovedLike()) {
            return back()->with('error', 'This clinic application has already been approved.');
        }

        [$owner, $tempPasswordPlain, $contactEmail, $emailName] = $this->finalizeClinicApproval($clinic);

        $loginUrl = route('login');
        try {
            Mail::to($contactEmail)->send(new ClinicApprovedMail(
                clinicName: $clinic->name,
                name: $emailName,
                email: $contactEmail,
                password: $tempPasswordPlain,
                loginUrl: $loginUrl
            ));
        } catch (\Throwable $e) {
            Log::error('Failed sending ClinicApprovedMail', [
                'clinic_id' => $clinic->id,
                'contact_email' => $clinic->contact_person_email,
                'clinic_email' => $clinic->email,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Clinic application approved successfully! Account details have been sent to the owner\'s email.');
    }

    private function finalizeClinicApproval(Clinic $clinic): array
    {
        return \DB::transaction(function () use ($clinic) {
            $contactEmail = $clinic->contact_person_email ?: $clinic->email;
            $contactName = trim(($clinic->contact_first_name ?? '') . ' ' . ($clinic->contact_last_name ?? ''));
            $contactPhone = $clinic->contact_number;

            $tempPasswordPlain = method_exists(Str::class, 'password') ? Str::password(12) : Str::random(12);
            $owner = User::where('email', $contactEmail)->first();

            if (!$owner) {
                $owner = User::create([
                    'name'        => $contactName ?: 'Clinic Owner',
                    'first_name'  => $clinic->contact_first_name,
                    'last_name'   => $clinic->contact_last_name,
                    'email'       => $contactEmail,
                    'phone'       => $contactPhone,
                    'password'    => $tempPasswordPlain,
                    'is_secretary'=> true,
                    'is_initial_login' => true,
                    'is_active'   => false,
                ]);
            } else {
                $owner->password = $tempPasswordPlain;
                $owner->is_initial_login = true;
                $owner->is_active = false;
                if (!$owner->is_secretary) {
                    $owner->is_secretary = true;
                }
                if (empty($owner->phone) && !empty($contactPhone)) {
                    $owner->phone = $contactPhone;
                }
                if ($contactName) {
                    $owner->name = $contactName;
                }
                $owner->save();
            }

            $clinic->status = 'approved';
            $clinic->created_by_user_id = $owner->id;
            $clinic->save();

            // Log approval action
            ClinicStatusLog::create([
                'clinic_id'    => $clinic->id,
                'action'       => 'approved',
                'reason'       => null,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);

            $clinic->secretaries()->syncWithoutDetaching([$owner->id]);

            $emailName = $contactName ?: ($clinic->user?->name ?? $owner->name ?? 'Clinic Owner');

            return [$owner, $tempPasswordPlain, $contactEmail, $emailName];
        });
    }

    public function decline(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'decline_reason' => 'required|string|min:10|max:1000'
        ]);

        $reason = trim($data['decline_reason']);

        // Update status and log in a transaction first
        \DB::beginTransaction();
        try {
            $clinic->update(['status' => 'rejected']);

            ClinicStatusLog::create([
                'clinic_id'    => $clinic->id,
                'action'       => 'rejected',
                'reason'       => $reason,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            Log::error('Failed updating clinic status to rejected', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to decline the application at the moment. Please try again.');
        }

        // Send decline notification email (non-fatal if it fails)
        try {
            $contactName = trim(($clinic->contact_first_name ?? '') . ' ' . ($clinic->contact_last_name ?? '')) ?: 'Clinic Owner';
            $contactEmail = $clinic->contact_person_email ?: $clinic->email;
            $reapplyUrl = route('owner.apply');

            Mail::to($contactEmail)->send(new ClinicDeclinedMail(
                clinicName: $clinic->name,
                name: $contactName,
                email: $contactEmail,
                reason: $reason,
                reapplyUrl: $reapplyUrl
            ));
        } catch (\Throwable $e) {
            Log::error('Declined clinic but failed sending email', [
                'clinic_id' => $clinic->id,
                'contact_email' => $clinic->contact_person_email,
                'clinic_email' => $clinic->email,
                'error' => $e->getMessage(),
            ]);
            return back()->with('warning', 'Application declined, but email could not be sent. Please notify the applicant manually.');
        }

        return back()->with('status', 'Clinic application has been declined and notification email sent to the contact person.');
    }

}
