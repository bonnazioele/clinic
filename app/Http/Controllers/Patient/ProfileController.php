<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function redirect()
    {
        $role = $this->resolveRole(Auth::user());

        return redirect()->route($role . '.profile.show');
    }

    /*
    |--------------------------------------------------------------------------
    | Patient Profile
    |--------------------------------------------------------------------------
    */

    public function patientShow()
    {
        return $this->showForRole('patient');
    }

    public function patientEdit()
    {
        return $this->editForRole('patient');
    }

    public function patientUpdate(Request $request)
    {
        return $this->updateForRole($request, 'patient');
    }

    /*
    |--------------------------------------------------------------------------
    | Secretary Profile
    |--------------------------------------------------------------------------
    */

    public function secretaryShow()
    {
        return $this->showForRole('secretary');
    }

    public function secretaryEdit()
    {
        return $this->editForRole('secretary');
    }

    public function secretaryUpdate(Request $request)
    {
        return $this->updateForRole($request, 'secretary');
    }

    /*
    |--------------------------------------------------------------------------
    | Doctor Profile
    |--------------------------------------------------------------------------
    */

    public function doctorShow()
    {
        return $this->showForRole('doctor');
    }

    public function doctorEdit()
    {
        return $this->editForRole('doctor');
    }

    public function doctorUpdate(Request $request)
    {
        return $this->updateForRole($request, 'doctor');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Profile
    |--------------------------------------------------------------------------
    */

    public function adminShow()
    {
        return $this->showForRole('admin');
    }

    public function adminEdit()
    {
        return $this->editForRole('admin');
    }

    public function adminUpdate(Request $request)
    {
        return $this->updateForRole($request, 'admin');
    }

    /*
    |--------------------------------------------------------------------------
    | Shared Role Methods
    |--------------------------------------------------------------------------
    */

    private function showForRole(string $role)
    {
        $user = Auth::user();

        abort_unless($this->resolveRole($user) === $role, 403);

        return view($this->showViewForRole($role), [
            'user' => $user,
            'role' => $role,
            'meta' => $this->meta($role),
        ]);
    }

    private function editForRole(string $role)
    {
        $user = Auth::user();

        abort_unless($this->resolveRole($user) === $role, 403);

        return view($this->editViewForRole($role), [
            'user' => $user,
            'role' => $role,
            'meta' => $this->meta($role),
        ]);
    }

    private function updateForRole(Request $request, string $role)
    {
        $user = Auth::user();

        abort_unless($this->resolveRole($user) === $role, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],

            'medical_document' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        if ($request->hasFile('medical_document')) {
            if (
                !empty($user->medical_document) &&
                Storage::disk('public')->exists($user->medical_document)
            ) {
                Storage::disk('public')->delete($user->medical_document);
            }

            $validated['medical_document'] = $request
                ->file('medical_document')
                ->store('profile-documents', 'public');
        } else {
            unset($validated['medical_document']);
        }

        $user->update($validated);

        return redirect()
            ->route($role . '.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | View Resolver
    |--------------------------------------------------------------------------
    | Patient uses your existing resources/views/profile folder.
    | Other roles use their own role-based folders.
    |--------------------------------------------------------------------------
    */

    private function showViewForRole(string $role): string
    {
        return match ($role) {
            'secretary' => 'secretary.profile.show',
            'doctor' => 'doctor.profile.show',
            'admin' => 'admin.profile.show',
            default => 'profile.show',
        };
    }

    private function editViewForRole(string $role): string
    {
        return match ($role) {
            'secretary' => 'secretary.profile.edit',
            'doctor' => 'doctor.profile.edit',
            'admin' => 'admin.profile.edit',
            default => 'profile.edit',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Role Detection
    |--------------------------------------------------------------------------
    | Your users table uses:
    | is_admin
    | is_secretary
    | is_doctor
    |
    | If all three are 0, the account is a patient.
    |--------------------------------------------------------------------------
    */

    private function resolveRole($user): string
    {
        if ((int) ($user->is_admin ?? 0) === 1) {
            return 'admin';
        }

        if ((int) ($user->is_secretary ?? 0) === 1) {
            return 'secretary';
        }

        if ((int) ($user->is_doctor ?? 0) === 1) {
            return 'doctor';
        }

        return 'patient';
    }

    private function meta(string $role): array
    {
        $metas = [
            'patient' => [
                'label' => 'Patient Account',
                'icon' => 'bi-person-heart',
                'hero_show' => 'View your personal information, contact details, and uploaded medical document.',
                'hero_edit' => 'Update your personal information, contact details, address, and medical document.',
                'document_title' => 'Medical Document',
                'document_empty' => 'No medical document has been uploaded yet.',
                'document_help' => 'Upload a new file only if you want to replace your current medical document.',
                'reminders' => [
                    'Keep your phone number updated so clinics can contact you.',
                    'Make sure your address is correct for clinic records.',
                    'Upload or update your medical document if the clinic requires it.',
                ],
            ],

            'secretary' => [
                'label' => 'Secretary Account',
                'icon' => 'bi-clipboard2-pulse',
                'hero_show' => 'View your secretary profile, contact details, and clinic staff account information.',
                'hero_edit' => 'Update your secretary profile, contact details, and clinic staff account information.',
                'document_title' => 'Profile Document',
                'document_empty' => 'No profile document has been uploaded yet.',
                'document_help' => 'Upload a new file only if you want to replace your current profile document.',
                'reminders' => [
                    'Keep your phone number updated for clinic coordination.',
                    'Review your account details regularly.',
                    'Contact the clinic admin if your assigned clinic is incorrect.',
                ],
            ],

            'doctor' => [
                'label' => 'Doctor Account',
                'icon' => 'bi-heart-pulse',
                'hero_show' => 'View your doctor profile, contact details, and professional account information.',
                'hero_edit' => 'Update your doctor profile, contact details, and professional account information.',
                'document_title' => 'Professional Document',
                'document_empty' => 'No professional document has been uploaded yet.',
                'document_help' => 'Upload a new file only if you want to replace your current professional document.',
                'reminders' => [
                    'Keep your contact details updated for clinic coordination.',
                    'Review your professional profile regularly.',
                    'Contact the clinic admin if your assigned clinic or service is incorrect.',
                ],
            ],

            'admin' => [
                'label' => 'Admin Account',
                'icon' => 'bi-shield-lock',
                'hero_show' => 'View your administrator profile, contact details, and system account information.',
                'hero_edit' => 'Update your administrator profile, contact details, and system account information.',
                'document_title' => 'Profile Document',
                'document_empty' => 'No profile document has been uploaded yet.',
                'document_help' => 'Upload a new file only if you want to replace your current profile document.',
                'reminders' => [
                    'Keep your admin contact information updated.',
                    'Review your account details regularly.',
                    'Use admin privileges carefully when managing users and clinics.',
                ],
            ],
        ];

        return $metas[$role] ?? $metas['patient'];
    }
}