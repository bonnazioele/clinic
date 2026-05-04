<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class StoreWalkInRegistrationRequest extends FormRequest
{
    protected ?array $clinicServiceNamesCache = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceRule = ['required', 'string', 'max:255'];

        $availableServiceNames = $this->clinicServiceNames();

        if (! empty($availableServiceNames)) {
            $serviceRule[] = Rule::in($availableServiceNames);
        }

        return [
            'patient_id' => ['nullable', 'exists:patients,id'],

            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],

            'email_address' => ['required', 'email', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],

            'requested_service' => $serviceRule,
            'doctor_id' => ['required', 'integer', 'exists:users,id'],

            'priority_level' => ['nullable', 'in:Normal,Urgent,Emergency'],
            'reason_for_visit' => ['nullable', 'string', 'max:1000'],

            'visit_type' => ['nullable', 'in:Walk-In,Follow-Up'],
            'patient_type' => ['nullable', 'in:New,Returning'],
            'assigned_department' => ['nullable', 'string', 'max:255'],

            'sex' => ['nullable', 'in:Male,Female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'complete_address' => ['nullable', 'string', 'max:1000'],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],

            'consent_to_data_collection' => ['sometimes', 'accepted'],
            'patient_signature' => ['nullable', 'string'],
            'date_signed' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => 'Patient last name is required.',
            'first_name.required' => 'Patient first name is required.',

            'email_address.required' => 'Patient email address is required.',
            'email_address.email' => 'Patient email address must be valid.',

            'requested_service.required' => 'Requested service is required.',
            'requested_service.in' => 'The selected service is not available in this clinic.',

            'doctor_id.required' => 'Please select the doctor who will handle this walk-in patient.',
            'doctor_id.exists' => 'The selected doctor is invalid.',

            'mobile_number.regex' => 'Mobile number format is invalid.',
            'emergency_contact_number.regex' => 'Emergency contact number format is invalid.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'consent_to_data_collection.accepted' => 'The consent to data collection field must be accepted when provided.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'patient',

            'last_name' => 'last name',
            'first_name' => 'first name',
            'middle_name' => 'middle name',

            'email_address' => 'email address',
            'mobile_number' => 'mobile number',

            'requested_service' => 'requested service',
            'doctor_id' => 'doctor',

            'priority_level' => 'priority level',
            'reason_for_visit' => 'reason for visit',

            'visit_type' => 'visit type',
            'patient_type' => 'patient type',
            'assigned_department' => 'assigned department',

            'sex' => 'sex',
            'date_of_birth' => 'date of birth',
            'complete_address' => 'complete address',

            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'emergency_contact_number' => 'emergency contact number',

            'consent_to_data_collection' => 'consent to data collection',
            'patient_signature' => 'patient signature',
            'date_signed' => 'date signed',
        ];
    }

    protected function clinicServiceNames(): array
    {
        if ($this->clinicServiceNamesCache !== null) {
            return $this->clinicServiceNamesCache;
        }

        $user = $this->user();

        if (! $user || ! $user->is_secretary) {
            return $this->clinicServiceNamesCache = [];
        }

        $sharedClinic = View::shared('activeClinic');

        if ($sharedClinic) {
            $sharedClinic->loadMissing([
                'services' => fn ($query) => $query->orderBy('name'),
            ]);

            return $this->clinicServiceNamesCache = $sharedClinic->services
                ->pluck('name')
                ->toArray();
        }

        $activeClinicId = (int) ($this->session()->get('active_clinic_id') ?: 0);

        if (! $activeClinicId) {
            $activeClinicId = (int) $user->secretaryClinics()
                ->orderBy('name')
                ->pluck('clinics.id')
                ->first();
        }

        if (! $activeClinicId) {
            return $this->clinicServiceNamesCache = [];
        }

        $clinic = $user->secretaryClinics()
            ->with([
                'services' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->where('clinics.id', $activeClinicId)
            ->first();

        if (! $clinic) {
            return $this->clinicServiceNamesCache = [];
        }

        return $this->clinicServiceNamesCache = $clinic->services
            ->pluck('name')
            ->toArray();
    }
}