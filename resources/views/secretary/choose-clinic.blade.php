@extends('layouts.app')

@section('title', 'Choose Clinic')

@section('content')
@php
    $user = auth()->user();
    $isDoctor = (bool) ($user?->is_doctor);
    $action = $isDoctor ? route('doctor.choose-clinic.select') : route('secretary.choose-clinic.select');
    $dashboardRoute = $isDoctor ? 'doctor.dashboard' : 'secretary.dashboard';
@endphp

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="medical-card p-4">
        <div class="mb-4">
          <h2 class="fw-bold text-primary mb-1">
            <i class="bi bi-building-check medical-icon me-2"></i>Choose Clinic
          </h2>
          <p class="text-muted mb-0">Select the clinic you want to work in.</p>
        </div>

        <form method="POST" action="{{ $action }}">
          @csrf
          <div class="mb-3">
            <label for="clinic_id" class="form-label fw-semibold">Clinic</label>
            <select id="clinic_id" name="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
              <option value="">Select clinic</option>
              @foreach($clinics as $clinic)
                <option value="{{ $clinic->id }}" @selected((int) $currentClinicId === (int) $clinic->id)>
                  {{ $clinic->name }}
                </option>
              @endforeach
            </select>
            @error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route($dashboardRoute) }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i>Continue
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
