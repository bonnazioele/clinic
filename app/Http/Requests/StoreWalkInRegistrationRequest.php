<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class StoreWalkInRegistrationRequest extends FormRequest
{
    protected ?array $clinicServiceNamesCache = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serviceRule = ['required', 'string', 'max:255'];
        $availableServiceNames = $this->clinicServiceNames();
        if (! empty($availableServiceNames)) {
            $serviceRule[] = Rule::in($availableServiceNames);
        }

        return [
            // Patient Information
            'patient_id' => 'nullable|exists:patients,id',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'sex' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date|before:today',

            // Contact Information
            'mobile_number' => 'required|string|max:15|regex:/^[0-9+\-\s()]+$/',
            'email_address' => 'nullable|email|max:255',
            'complete_address' => 'required|string|max:1000',

            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_relationship' => 'required|string|max:50',
            'emergency_contact_number' => 'required|string|max:15|regex:/^[0-9+\-\s()]+$/',

            // Visit Information
            'visit_type' => 'required|in:Walk-In,Follow-Up',
            'reason_for_visit' => 'required|string|max:1000',
            'requested_service' => $serviceRule,
            'assigned_department' => 'nullable|string|max:255',

            // Patient Classification
            'patient_type' => 'required|in:New,Returning',
            'priority_level' => 'required|in:Normal,Urgent,Emergency',

            // Consent & Verification
            'consent_to_data_collection' => 'required|accepted',
            'patient_signature' => 'nullable|string',
            'date_signed' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'last_name.required' => 'Patient last name is required.',
            'first_name.required' => 'Patient first name is required.',
            'sex.required' => 'Patient sex is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be a date before today.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.regex' => 'Mobile number format is invalid.',
            'complete_address.required' => 'Complete address is required.',
            'emergency_contact_name.required' => 'Emergency contact name is required.',
            'emergency_contact_relationship.required' => 'Emergency contact relationship is required.',
            'emergency_contact_number.required' => 'Emergency contact number is required.',
            'visit_type.required' => 'Visit type is required.',
            'reason_for_visit.required' => 'Reason for visit is required.',
            'requested_service.required' => 'Requested service is required.',
            'patient_type.required' => 'Patient type is required.',
            'priority_level.required' => 'Priority level is required.',
            'consent_to_data_collection.accepted' => 'You must consent to data collection and medical processing.',
            'date_signed.required' => 'Date signed is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'date_of_birth' => 'date of birth',
            'mobile_number' => 'mobile number',
            'email_address' => 'email address',
            'complete_address' => 'complete address',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'emergency_contact_number' => 'emergency contact number',
            'visit_type' => 'visit type',
            'reason_for_visit' => 'reason for visit',
            'requested_service' => 'requested service',
            'assigned_department' => 'assigned department',
            'patient_type' => 'patient type',
            'priority_level' => 'priority level',
            'consent_to_data_collection' => 'consent to data collection',
            'patient_signature' => 'patient signature',
            'date_signed' => 'date signed',
        ];
    }
    /**
     * Retrieve the services available to the secretary's active clinic.
     */
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
            $sharedClinic->loadMissing(['services' => fn ($query) => $query->orderBy('name')]);
            return $this->clinicServiceNamesCache = $sharedClinic->services->pluck('name')->toArray();
        }

        $activeClinicId = (int) ($this->session()->get('active_clinic_id') ?: 0);
        if (! $activeClinicId) {
            $activeClinicId = (int) $user->secretaryClinics()->orderBy('name')->pluck('clinics.id')->first();
        }

        if (! $activeClinicId) {
            return $this->clinicServiceNamesCache = [];
        }

        $clinic = $user->secretaryClinics()
            ->with(['services' => function ($query) {
                $query->orderBy('name');
            }])
            ->where('clinics.id', $activeClinicId)
            ->first();

        if (! $clinic) {
            return $this->clinicServiceNamesCache = [];
        }

        $clinic->loadMissing(['services' => fn ($query) => $query->orderBy('name')]);

        return $this->clinicServiceNamesCache = $clinic->services->pluck('name')->toArray();
    }
}
