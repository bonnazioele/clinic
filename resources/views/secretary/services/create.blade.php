@extends('layouts.app')

@section('title', 'Create Service')

@section('content')
@php
  $user = auth()->user();

  $secretaryClinicRouteValue = request()->route('clinic');
  $secretaryClinicId = null;

  if (is_object($secretaryClinicRouteValue) && isset($secretaryClinicRouteValue->id)) {
      $secretaryClinicId = $secretaryClinicRouteValue->id;
  }

  if (!$secretaryClinicId && is_numeric($secretaryClinicRouteValue)) {
      $secretaryClinicId = $secretaryClinicRouteValue;
  }

  if (!$secretaryClinicId && isset($clinic) && isset($clinic->id)) {
      $secretaryClinicId = $clinic->id;
  }

  if (!$secretaryClinicId && session('active_clinic_id')) {
      $secretaryClinicId = session('active_clinic_id');
  }

  if (!$secretaryClinicId && isset($user->clinic_id)) {
      $secretaryClinicId = $user->clinic_id;
  }

  if (!$secretaryClinicId && isset($user->clinics) && $user->clinics->count()) {
      $secretaryClinicId = $user->clinics->first()->id;
  }

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) {
          return url($fallback);
      }

      $params = (array) $params;

      if ($secretaryClinicId && !array_key_exists('clinic', $params)) {
          $params = array_merge(['clinic' => $secretaryClinicId], $params);
      }

      try {
          return route($routeName, $params);
      } catch (\Throwable $error) {
          return url($fallback);
      }
  };

  $storeUrl = $secUrl('secretary.services.store');
  $backUrl = $secUrl('secretary.services.index');
@endphp

<style>
  .service-create-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .service-create-hero {
    border-radius: 26px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      radial-gradient(circle at 18% 92%, rgba(125,211,252,.22), transparent 24%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
  }

  .service-create-hero::after {
    content: "";
    position: absolute;
    right: -55px;
    bottom: -65px;
    width: 180px;
    height: 180px;
    border-radius: 55px;
    background: rgba(255,255,255,0.12);
    transform: rotate(12deg);
  }

  .service-create-hero-row {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .service-create-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .service-create-icon {
    width: 62px;
    height: 62px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 1.7rem;
    flex: 0 0 62px;
  }

  .service-create-title {
    margin: 0;
    font-size: clamp(1.55rem, 2.6vw, 2.25rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .service-create-subtitle {
    margin: 0.35rem 0 0;
    font-size: 0.97rem;
    font-weight: 650;
    opacity: 0.94;
    max-width: 720px;
  }

  .service-create-hero .btn {
    border-radius: 15px;
    font-weight: 900;
    min-height: 44px;
  }

  .service-create-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.75fr);
    gap: 1rem;
    align-items: start;
  }

  .service-form-card,
  .service-side-card {
    border-radius: 26px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .service-card-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    background: rgba(248,250,252,.85);
  }

  .service-card-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .service-card-title i {
    color: #0d6efd;
  }

  .service-form-body {
    padding: 1.2rem;
  }

  .field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .field-full {
    grid-column: 1 / -1;
  }

  .form-label {
    font-size: 0.82rem;
    font-weight: 850;
    color: #334155;
  }

  .form-control,
  .form-select {
    border-radius: 14px !important;
    border-color: #dbe3ef !important;
    font-weight: 650;
    box-shadow: none !important;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(13,110,253,.55) !important;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.1) !important;
  }

  .field-help {
    margin-top: 0.4rem;
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 650;
    line-height: 1.4;
  }

  .service-form-footer {
    padding: 1rem 1.2rem;
    border-top: 1px solid #edf2f7;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    flex-wrap: wrap;
    background: #f8fafc;
  }

  .service-form-footer .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .service-side-card {
    padding: 1rem;
  }

  .service-preview-icon {
    width: 86px;
    height: 86px;
    border-radius: 26px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    font-size: 2rem;
    margin-bottom: 1rem;
  }

  .side-title {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    margin-bottom: 0.35rem;
  }

  .side-text {
    color: #64748b;
    font-size: 0.84rem;
    font-weight: 650;
    line-height: 1.5;
  }

  .side-list {
    margin-top: 1rem;
    display: grid;
    gap: 0.65rem;
  }

  .side-item {
    border-radius: 16px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    padding: 0.75rem;
    display: flex;
    gap: 0.65rem;
    align-items: flex-start;
    color: #475569;
    font-weight: 650;
    font-size: 0.82rem;
  }

  .side-item i {
    color: #0d6efd;
    font-size: 1rem;
  }

  @media (max-width: 1100px) {
    .service-create-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .service-create-page {
      width: 100%;
    }

    .service-create-hero,
    .service-form-card,
    .service-side-card {
      border-radius: 22px;
    }

    .field-grid {
      grid-template-columns: 1fr;
    }

    .service-form-footer .btn {
      width: 100%;
    }
  }
</style>

<div class="service-create-page">
  @include('partials.alerts')

  <section class="service-create-hero">
    <div class="service-create-hero-row">
      <div class="service-create-title-wrap">
        <div class="service-create-icon">
          <i class="bi bi-plus-circle"></i>
        </div>

        <div>
          <h1 class="service-create-title">Create New Service</h1>
          <p class="service-create-subtitle">
            Add a service that belongs only to {{ $clinic->name ?? 'your clinic' }}.
          </p>
        </div>
      </div>

      <a href="{{ $backUrl }}" class="btn btn-light text-primary">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Services
      </a>
    </div>
  </section>

  <div class="service-create-grid">
    <main class="service-form-card">
      <div class="service-card-head">
        <h2 class="service-card-title">
          <i class="bi bi-clipboard2-pulse"></i>
          Service Information
        </h2>
      </div>

      <form method="POST" action="{{ $storeUrl }}">
        @csrf

        <div class="service-form-body">
          <div class="field-grid">
            <div class="field-full">
              <label class="form-label">Service Name</label>
              <input type="text"
                     name="name"
                     value="{{ old('name') }}"
                     class="form-control @error('name') is-invalid @enderror"
                     placeholder="e.g. Dental Checkup, Consultation, X-Ray"
                     required>

              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="field-full">
              <label class="form-label">Description</label>
              <textarea name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="4"
                        placeholder="Briefly describe what this service includes...">{{ old('description') }}</textarea>

              <div class="field-help">
                This helps patients understand what the service is for.
              </div>

              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <label class="form-label">Duration Minutes</label>
              <input type="number"
                     name="duration_minutes"
                     value="{{ old('duration_minutes', 30) }}"
                     class="form-control @error('duration_minutes') is-invalid @enderror"
                     min="5"
                     max="480"
                     required>

              <div class="field-help">
                Default duration is 30 minutes.
              </div>

              @error('duration_minutes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <label class="form-label">Clinic</label>
              <input type="text"
                     class="form-control"
                     value="{{ $clinic->name ?? 'Current Clinic' }}"
                     disabled>

              <div class="field-help">
                This service will only be attached to this clinic.
              </div>
            </div>
          </div>
        </div>

        <div class="service-form-footer">
          <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
            Cancel
          </a>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>
            Create Service
          </button>
        </div>
      </form>
    </main>

    <aside class="service-side-card">
      <div class="service-preview-icon">
        <i class="bi bi-clipboard2-pulse"></i>
      </div>

      <div class="side-title">Service Setup Guide</div>
      <div class="side-text">
        The new service will be created and automatically attached to the active clinic only.
      </div>

      <div class="side-list">
        <div class="side-item">
          <i class="bi bi-hospital"></i>
          <span>This service will belong to {{ $clinic->name ?? 'your selected clinic' }}.</span>
        </div>

        <div class="side-item">
          <i class="bi bi-clock"></i>
          <span>Duration controls how appointment slots are estimated.</span>
        </div>

        <div class="side-item">
          <i class="bi bi-person-badge"></i>
          <span>Assign doctors to this service after creating it if needed.</span>
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection