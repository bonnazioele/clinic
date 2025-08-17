@extends('layouts.app')

@section('title', 'Create Appointment')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card medical-card">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">
                        <i class="bi bi-calendar-plus me-2"></i>Create Appointment for Patient
                    </h2>
                </div>

                <div class="card-body">
                    @include('partials.alerts')

                    <form method="POST" action="{{ route('secretary.appointments.store') }}" id="appointmentForm">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Patient
                            </label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Select Patient</option>
                                @foreach(\App\Models\User::where('is_doctor', false)->where('is_admin', false)->where('is_secretary', false)->get() as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Clinic Selection -->
                        <div class="mb-3">
                            <label for="clinic_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Clinic
                            </label>
                            <select name="clinic_id" id="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
                                <option value="">Select Clinic</option>
                                @foreach($clinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->name }} - {{ $clinic->branch_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('clinic_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Service Selection -->
                        <div class="mb-3">
                            <label for="service_id" class="form-label fw-semibold">
                                <i class="bi bi-tools me-1"></i>Service
                            </label>
                            <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                                <option value="">Select Service</option>
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label fw-semibold">
                                <i class="bi bi-person-badge me-1"></i>Doctor
                            </label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">Select Doctor</option>
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date and Time -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label fw-semibold">
                                        <i class="bi bi-calendar me-1"></i>Date
                                    </label>
                                    <input type="date"
                                           name="appointment_date"
                                           id="appointment_date"
                                           class="form-control @error('appointment_date') is-invalid @enderror"
                                           value="{{ old('appointment_date') }}"
                                           min="{{ date('Y-m-d') }}"
                                           required>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_time" class="form-label fw-semibold">
                                        <i class="bi bi-clock me-1"></i>Time
                                    </label>
                                    <input type="time"
                                           name="appointment_time"
                                           id="appointment_time"
                                           class="form-control @error('appointment_time') is-invalid @enderror"
                                           value="{{ old('appointment_time') }}"
                                           required>
                                    @error('appointment_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-sticky me-1"></i>Notes (Optional)
                            </label>
                            <textarea name="notes"
                                      id="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes about the appointment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-2"></i>Create Appointment
                            </button>
                            <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clinicSelect = document.getElementById('clinic_id');
    const serviceSelect = document.getElementById('service_id');
    const doctorSelect = document.getElementById('doctor_id');
    const clinics = @json($clinics);

    // Update services when clinic changes
    clinicSelect.addEventListener('change', function() {
        const clinicId = this.value;
        serviceSelect.innerHTML = '<option value="">Select Service</option>';
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';

        if (clinicId) {
            const clinic = clinics.find(c => c.id == clinicId);
            if (clinic && clinic.services) {
                clinic.services.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    serviceSelect.appendChild(option);
                });
            }
        }
    });

    // Update doctors when clinic changes
    clinicSelect.addEventListener('change', function() {
        const clinicId = this.value;
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';

        if (clinicId) {
            const clinic = clinics.find(c => c.id == clinicId);
            if (clinic && clinic.doctors) {
                clinic.doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.id;
                    option.textContent = doctor.name;
                    doctorSelect.appendChild(option);
                });
            }
        }
    });

    // Form submission loading state
    const form = document.getElementById('appointmentForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating...';
    });
});
</script>
@endpush
@endsection
