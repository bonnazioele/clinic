@extends('layouts.app')

@section('title', 'Edit Doctor')

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

      try {
          return route($routeName, $params);
      } catch (\Throwable $firstError) {
          if ($secretaryClinicId) {
              try {
                  return route($routeName, array_merge(['clinic' => $secretaryClinicId], (array) $params));
              } catch (\Throwable $secondError) {
                  return url($fallback);
              }
          }

          return url($fallback);
      }
  };
@endphp

<style>
  .doctor-form-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .doctor-form-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255,255,255,.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
  }

  .doctor-form-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .doctor-form-title-wrap {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .doctor-form-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .doctor-form-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .doctor-form-subtitle {
    margin: .3rem 0 0;
    font-size: .95rem;
    font-weight: 650;
    opacity: .94;
  }

  .doctor-form-hero .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .doctor-form-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(290px, .75fr);
    gap: 1rem;
    align-items: start;
  }

  .doctor-form-card,
  .doctor-side-card {
    border-radius: 24px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .doctor-card-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
  }

  .doctor-card-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .doctor-card-title i {
    color: #0d6efd;
  }

  .doctor-form-body {
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
    font-size: .82rem;
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
    box-shadow: 0 0 0 .2rem rgba(13,110,253,.1) !important;
  }

  .field-help {
    margin-top: .4rem;
    color: #64748b;
    font-size: .76rem;
    font-weight: 650;
    line-height: 1.4;
  }

  .input-group .btn {
    border-radius: 0 14px 14px 0;
  }

  .doctor-form-footer {
    padding: 1rem 1.2rem;
    border-top: 1px solid #edf2f7;
    display: flex;
    justify-content: flex-end;
    gap: .65rem;
    flex-wrap: wrap;
    background: #f8fafc;
  }

  .doctor-form-footer .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .doctor-side-card {
    padding: 1rem;
  }

  .doctor-avatar-preview {
    width: 86px;
    height: 86px;
    border-radius: 26px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #fff;
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: 1rem;
  }

  .side-title {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    margin-bottom: .35rem;
  }

  .side-text {
    color: #64748b;
    font-size: .84rem;
    font-weight: 650;
    line-height: 1.5;
  }

  .side-list {
    margin-top: 1rem;
    display: grid;
    gap: .65rem;
  }

  .side-item {
    border-radius: 16px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    padding: .75rem;
  }

  .side-label {
    font-size: .68rem;
    font-weight: 900;
    color: #64748b;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .2rem;
  }

  .side-value {
    color: #0f172a;
    font-size: .86rem;
    font-weight: 850;
    word-break: break-word;
  }

  @media (max-width: 1100px) {
    .doctor-form-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .doctor-form-page {
      width: 100%;
    }

    .doctor-form-hero {
      padding: 1rem;
      border-radius: 22px;
    }

    .field-grid {
      grid-template-columns: 1fr;
    }

    .doctor-form-footer .btn {
      width: 100%;
    }
  }
</style>

<div class="doctor-form-page">
  @include('partials.alerts')

  <section class="doctor-form-hero">
    <div class="doctor-form-hero-row">
      <div class="doctor-form-title-wrap">
        <div class="doctor-form-title-icon">
          <i class="bi bi-person-gear"></i>
        </div>

        <div>
          <h1 class="doctor-form-title">Edit Doctor</h1>
          <p class="doctor-form-subtitle">
            Update doctor information, services, and account access.
          </p>
        </div>
      </div>

      <a href="{{ $secUrl('secretary.doctors.index') }}" class="btn btn-light text-primary">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Doctors
      </a>
    </div>
  </section>

  <div class="doctor-form-grid">
    <main class="doctor-form-card">
      <div class="doctor-card-head">
        <h2 class="doctor-card-title">
          <i class="bi bi-pencil-square"></i>
          Doctor Details
        </h2>
      </div>

      <form method="POST" action="{{ $secUrl('secretary.doctors.update', ['doctor' => $doctor->id]) }}">
        @csrf
        @method('PATCH')

        <div class="doctor-form-body">
          <div class="field-grid">
            <div>
              <label for="doctor_first_name" class="form-label">First Name</label>
              <input id="doctor_first_name"
                     type="text"
                     name="first_name"
                     class="form-control @error('first_name') is-invalid @enderror"
                     value="{{ old('first_name', $doctor->first_name ?? '') }}"
                     required>
              @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_last_name" class="form-label">Last Name</label>
              <input id="doctor_last_name"
                     type="text"
                     name="last_name"
                     class="form-control @error('last_name') is-invalid @enderror"
                     value="{{ old('last_name', $doctor->last_name ?? '') }}"
                     required>
              @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_email" class="form-label">Email Address</label>
              <input id="doctor_email"
                     type="email"
                     name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email', $doctor->email) }}"
                     required>
              @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_phone" class="form-label">Phone Number</label>
              <input id="doctor_phone"
                     type="text"
                     name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone', $doctor->phone) }}">
              @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="field-full">
              <label for="doctor_address" class="form-label">Address</label>
              <textarea id="doctor_address"
                        name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        rows="3">{{ old('address', $doctor->address) }}</textarea>
              @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">New Password <span class="text-muted">(optional)</span></label>
              <div class="input-group">
                <input type="password"
                       id="doctor_password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror">
                <button type="button"
                        class="btn btn-outline-secondary password-toggle"
                        data-target="#doctor_password"
                        aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Confirm New Password</label>
              <div class="input-group">
                <input type="password"
                       id="doctor_password_confirmation"
                       name="password_confirmation"
                       class="form-control">
                <button type="button"
                        class="btn btn-outline-secondary password-toggle"
                        data-target="#doctor_password_confirmation"
                        aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="field-full">
              <label for="doctor_services" class="form-label">Services</label>
              <select id="doctor_services"
                      name="service_ids[]"
                      class="form-select enhanced-multiselect @error('service_ids') is-invalid @enderror"
                      multiple
                      data-placeholder="Select one or more services">
                @foreach($services as $s)
                  <option value="{{ $s->id }}" @selected(in_array($s->id, old('service_ids', $doctor->services->pluck('id')->toArray())))>
                    {{ $s->name }}
                  </option>
                @endforeach
              </select>
              <div class="field-help">Select all services this doctor can perform.</div>
              @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="doctor-form-footer">
          <a href="{{ $secUrl('secretary.doctors.index') }}" class="btn btn-outline-secondary">
            Cancel
          </a>

          <button class="btn btn-primary">
            <i class="bi bi-save me-1"></i>
            Save Changes
          </button>
        </div>
      </form>
    </main>

    <aside class="doctor-side-card">
      <div class="doctor-avatar-preview">
        {{ strtoupper(substr($doctor->name ?? 'D', 0, 1)) }}
      </div>

      <div class="side-title">Dr. {{ $doctor->name }}</div>
      <div class="side-text">
        Review the current account details before saving changes.
      </div>

      <div class="side-list">
        <div class="side-item">
          <div class="side-label">Email</div>
          <div class="side-value">{{ $doctor->email }}</div>
        </div>

        <div class="side-item">
          <div class="side-label">Phone</div>
          <div class="side-value">{{ $doctor->phone ?: 'Not provided' }}</div>
        </div>

        <div class="side-item">
          <div class="side-label">Services</div>
          <div class="side-value">
            {{ $doctor->services && $doctor->services->count() ? $doctor->services->pluck('name')->join(', ') : 'No services assigned' }}
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection