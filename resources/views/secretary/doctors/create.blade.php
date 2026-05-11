@extends('layouts.app')

@section('title', 'Add Doctor')

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
    display: flex;
    gap: .65rem;
    align-items: flex-start;
    color: #475569;
    font-weight: 650;
    font-size: .82rem;
  }

  .side-item i {
    color: #0d6efd;
    font-size: 1rem;
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

  .service-assign-list {
    border-radius: 18px;
    border: 1px solid #dbe3ef;
    background: #f8fafc;
    overflow: hidden;
  }

  .service-assign-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .75rem 1rem;
    border-bottom: 1px solid #edf2f7;
    transition: background .15s ease;
  }

  .service-assign-row:last-child {
    border-bottom: 0;
  }

  .service-assign-row.is-checked {
    background: #eff6ff;
  }

  .service-assign-check {
    display: flex;
    align-items: center;
    gap: .65rem;
    cursor: pointer;
    flex: 1;
    margin: 0;
  }

  .service-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #0d6efd;
    flex: 0 0 auto;
  }

  .service-assign-name {
    font-size: .88rem;
    font-weight: 750;
    color: #0f172a;
  }

  .service-assign-duration {
    display: flex;
    align-items: center;
    gap: .45rem;
    flex: 0 0 auto;
  }

  .service-duration-input {
    width: 80px !important;
    border-radius: 12px !important;
    text-align: center;
    font-weight: 750;
    padding: .35rem .5rem !important;
  }

  .duration-unit {
    font-size: .78rem;
    font-weight: 700;
    color: #64748b;
  }

</style>

<div class="doctor-form-page">
  @include('partials.alerts')

  <section class="doctor-form-hero">
    <div class="doctor-form-hero-row">
      <div class="doctor-form-title-wrap">
        <div class="doctor-form-title-icon">
          <i class="bi bi-person-plus"></i>
        </div>

        <div>
          <h1 class="doctor-form-title">Add New Doctor</h1>
          <p class="doctor-form-subtitle">
            Create a doctor profile and assign services they can perform.
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
          <i class="bi bi-person-lines-fill"></i>
          Doctor Information
        </h2>
      </div>

      <form method="POST" action="{{ $secUrl('secretary.doctors.store') }}">
        @csrf

        <div class="doctor-form-body">
          <div class="field-grid">
            <div>
              <label for="doctor_first_name" class="form-label">First Name</label>
              <input id="doctor_first_name"
                     type="text"
                     name="first_name"
                     class="form-control @error('first_name') is-invalid @enderror"
                     value="{{ old('first_name') }}"
                     required>
              @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_last_name" class="form-label">Last Name</label>
              <input id="doctor_last_name"
                     type="text"
                     name="last_name"
                     class="form-control @error('last_name') is-invalid @enderror"
                     value="{{ old('last_name') }}"
                     required>
              @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_email" class="form-label">Email Address</label>
              <input id="doctor_email"
                     type="email"
                     name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}"
                     required>
              <div class="field-help">Use the doctor's real email. Existing doctor accounts can be reused across clinics.</div>
              @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="doctor_phone" class="form-label">Phone Number</label>
              <input id="doctor_phone"
                     type="text"
                     name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone') }}">
              @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="field-full">
              <label for="doctor_address" class="form-label">Address</label>
              <textarea id="doctor_address"
                        name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        rows="3">{{ old('address') }}</textarea>
              @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Password</label>
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
              <div class="field-help">Required for new doctor accounts. Leave blank when assigning an existing doctor account.</div>
              @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Confirm Password</label>
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
              <label class="form-label">Assign Services &amp; Duration</label>
              <div class="field-help mb-2">Check a service to assign it, then set how many minutes this doctor takes per appointment for that service.</div>
              @error('service_ids')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

              <div class="service-assign-list">
                @forelse($services as $service)
                  @php $checked = in_array($service->id, old('service_ids', [])); @endphp
                  <div class="service-assign-row {{ $checked ? 'is-checked' : '' }}" data-service-row>
                    <label class="service-assign-check">
                      <input type="checkbox"
                             name="service_ids[]"
                             value="{{ $service->id }}"
                             class="service-checkbox"
                             {{ $checked ? 'checked' : '' }}>
                      <span class="service-assign-name">{{ $service->name }}</span>
                    </label>
                    <div class="service-assign-duration {{ $checked ? '' : 'd-none' }}" data-duration-wrap>
                      <input type="number"
                             name="duration_minutes[{{ $service->id }}]"
                             value="{{ old('duration_minutes.' . $service->id, 30) }}"
                             min="5"
                             max="480"
                             class="form-control service-duration-input"
                             placeholder="mins">
                      <span class="duration-unit">mins</span>
                    </div>
                  </div>
                @empty
                  <div class="text-muted small p-3">No services available for this clinic yet.</div>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <div class="doctor-form-footer">
          <a href="{{ $secUrl('secretary.doctors.index') }}" class="btn btn-outline-secondary">
            Cancel
          </a>

          <button class="btn btn-primary">
            <i class="bi bi-save me-1"></i>
            Add Doctor
          </button>
        </div>
      </form>
    </main>

    <aside class="doctor-side-card">
      <div class="doctor-avatar-preview">
        <i class="bi bi-person-badge"></i>
      </div>

      <div class="side-title">Doctor Setup Guide</div>
      <div class="side-text">
        Add the doctor's information, then assign services so patients can book appointments properly.
      </div>

      <div class="side-list">
        <div class="side-item">
          <i class="bi bi-envelope-check"></i>
          <span>Using the same email should represent the same doctor identity.</span>
        </div>

        <div class="side-item">
          <i class="bi bi-clipboard2-pulse"></i>
          <span>Assign at least one service so the doctor appears in booking options.</span>
        </div>

        <div class="side-item">
          <i class="bi bi-shield-lock"></i>
          <span>Create a secure password only for new doctor accounts.</span>
        </div>
      </div>
    </aside>
  </div>
</div>

@push('scripts')
<script>
  document.querySelectorAll('.service-checkbox').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      const row = this.closest('[data-service-row]');
      const durationWrap = row.querySelector('[data-duration-wrap]');
      if (this.checked) {
        row.classList.add('is-checked');
        durationWrap.classList.remove('d-none');
      } else {
        row.classList.remove('is-checked');
        durationWrap.classList.add('d-none');
      }
    });
  });
</script>
@endpush
@endsection