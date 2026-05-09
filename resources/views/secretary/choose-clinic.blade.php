@extends('layouts.app')

@section('title', 'Choose Clinic')

@section('content')
@php
    $user = auth()->user();
    $isDoctor = (bool) ($user?->is_doctor);
    $action = $isDoctor ? route('doctor.choose-clinic.select') : route('secretary.choose-clinic.select');
    $dashboardRoute = $isDoctor ? 'doctor.dashboard' : 'secretary.dashboard';
@endphp

<style>
  .choose-clinic-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
  }

  .choose-clinic-card {
    width: min(640px, 100%);
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .choose-clinic-hero {
    padding: 1.35rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
  }

  .choose-clinic-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .choose-clinic-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.5rem;
    flex: 0 0 56px;
  }

  .choose-clinic-title {
    margin: 0;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .choose-clinic-subtitle {
    margin: 0.3rem 0 0;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 650;
  }

  .choose-clinic-body {
    padding: 1.35rem;
  }

  .choose-clinic-body .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 850;
  }

  .choose-clinic-body .form-select {
    min-height: 48px;
    border-radius: 15px;
    border-color: #dbe3ef;
    background-color: #f8fafc;
    font-weight: 650;
    box-shadow: none;
  }

  .choose-clinic-body .btn {
    border-radius: 15px;
    font-weight: 900;
    min-height: 44px;
  }
</style>

<div class="choose-clinic-page">
  <section class="choose-clinic-card">
    <div class="choose-clinic-hero">
      <div class="choose-clinic-title-wrap">
        <span class="choose-clinic-icon">
          <i class="bi bi-building-check"></i>
        </span>

        <div>
          <h1 class="choose-clinic-title h3">Choose Clinic</h1>
          <p class="choose-clinic-subtitle">Select the clinic workspace you want to use.</p>
        </div>
      </div>
    </div>

    <div class="choose-clinic-body">
      <form method="POST" action="{{ $action }}">
        @csrf

        <div class="mb-4">
          <label for="clinic_id" class="form-label">Clinic</label>
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

        <div class="d-flex gap-2 justify-content-end flex-wrap">
          <a href="{{ route($dashboardRoute) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
          </a>

          <button class="btn btn-primary" data-loading-text="Switching...">
            <i class="bi bi-check2-circle me-1"></i>
            Continue
          </button>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection
