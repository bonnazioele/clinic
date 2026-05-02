@extends('layouts.patient-dashboard')

@section('title', 'Edit Appointment')

@push('styles')
<style>
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .edit-appointment-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .edit-appointment-shell {
    width: 100%;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.95);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
  }

  .edit-appointment-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .edit-appointment-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .edit-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .edit-title-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    color: #ffffff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
    font-size: 1.4rem;
  }

  .edit-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .edit-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .edit-back-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .edit-appointment-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .edit-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.85fr);
    gap: 1rem;
    align-items: start;
  }

  .edit-form-card,
  .edit-summary-card,
  .edit-help-card {
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
    overflow: hidden;
  }

  .edit-form-card {
    position: relative;
  }

  .edit-form-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .edit-card-header {
    padding: 1rem 1.15rem 0.85rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .edit-card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #0f172a;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .edit-card-title i {
    color: #0d6efd;
  }

  .edit-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.4rem 0.65rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .edit-form-body {
    padding: 1.15rem;
  }

  .form-section {
    margin-bottom: 1rem;
  }

  .form-section:last-child {
    margin-bottom: 0;
  }

  .section-label {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.7rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 800;
  }

  .section-label i {
    color: #0d6efd;
  }

  .readonly-clinic {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    background:
      linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.95));
  }

  .readonly-clinic-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.2rem;
  }

  .readonly-clinic-label {
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.1rem;
  }

  .readonly-clinic-name {
    color: #0f172a;
    font-size: 0.96rem;
    font-weight: 800;
  }

  .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 0.4rem;
  }

  .form-control,
  .form-select {
    border-radius: 14px;
    border-color: #dbe3ef;
    padding: 0.68rem 0.8rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: none;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(13, 110, 253, 0.55);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
  }

  .field-help {
    margin-top: 0.38rem;
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
  }

  .date-time-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.8rem;
  }

  .edit-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    padding-top: 0.25rem;
  }

  .edit-actions .btn {
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 800;
    padding: 0.58rem 0.9rem;
  }

  .side-stack {
    display: grid;
    gap: 1rem;
  }

  .edit-summary-card {
    padding: 1rem;
  }

  .summary-head {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 0.85rem;
  }

  .summary-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.25rem;
  }

  .summary-title {
    margin: 0;
    color: #0f172a;
    font-size: 0.98rem;
    font-weight: 800;
  }

  .summary-sub {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .summary-list {
    display: grid;
    gap: 0.6rem;
  }

  .summary-item {
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .summary-label {
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.14rem;
  }

  .summary-value {
    color: #0f172a;
    font-size: 0.88rem;
    font-weight: 800;
    line-height: 1.3;
  }

  .edit-help-card {
    padding: 1rem;
    background:
      linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.95));
  }

  .help-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 800;
  }

  .help-title i {
    color: #0d6efd;
  }

  .help-list {
    margin: 0;
    padding-left: 1.05rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.55;
  }

  @media (max-width: 1100px) {
    .edit-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .edit-appointment-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .edit-appointment-hero,
    .edit-appointment-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .edit-appointment-hero-row,
    .edit-card-header {
      flex-direction: column;
      align-items: stretch;
    }

    .edit-title {
      font-size: 1.35rem;
    }

    .edit-subtitle {
      font-size: 0.82rem;
    }

    .edit-back-btn,
    .edit-actions .btn {
      width: 100%;
    }

    .edit-actions {
      flex-direction: column;
    }

    .date-time-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="patient-tab-shell">
  <div class="patient-tab-content">
    <div class="container-fluid edit-appointment-page px-0">
      @include('partials.alerts')

      <div class="edit-appointment-shell">
        <div class="edit-appointment-hero">
          <div class="edit-appointment-hero-row">
            <div class="edit-title-wrap">
              <div class="edit-title-icon">
                <i class="bi bi-pencil-square"></i>
              </div>

              <div>
                <h1 class="edit-title">Edit Appointment</h1>
                <p class="edit-subtitle">
                  Update your clinic service, doctor, date, and time before saving your changes.
                </p>
              </div>
            </div>

            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary edit-back-btn">
              <i class="bi bi-arrow-left me-1"></i>
              Back to Appointments
            </a>
          </div>
        </div>

        <div class="edit-appointment-body">
          <div class="edit-grid">
            <div class="edit-form-card">
              <div class="edit-card-header">
                <h5 class="edit-card-title">
                  <i class="bi bi-calendar2-check"></i>
                  Appointment Information
                </h5>

                <span class="edit-pill">
                  <i class="bi bi-info-circle"></i>
                  Editable
                </span>
              </div>

              <form method="POST" action="{{ route('appointments.update', $appointment) }}">
                @csrf
                @method('PUT')

                <div class="edit-form-body">
                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-hospital"></i>
                      Selected Clinic
                    </div>

                    <div class="readonly-clinic">
                      <div class="readonly-clinic-icon">
                        <i class="bi bi-building"></i>
                      </div>

                      <div>
                        <div class="readonly-clinic-label">Clinic</div>
                        <div class="readonly-clinic-name">{{ $clinic->name }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-clipboard2-pulse"></i>
                      Service and Doctor
                    </div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="service_id" class="form-label">Service</label>
                        <select id="service_id"
                                name="service_id"
                                class="form-select @error('service_id') is-invalid @enderror"
                                required>
                          @foreach($clinic->services as $service)
                            <option value="{{ $service->id }}"
                              @selected(old('service_id', $appointment->service_id) == $service->id)>
                              {{ $service->name }}
                            </option>
                          @endforeach
                        </select>

                        <div class="field-help">
                          Choose the service you want to update for this appointment.
                        </div>

                        @error('service_id')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <label for="doctor_id" class="form-label">Doctor</label>
                        <select id="doctor_id"
                                name="doctor_id"
                                class="form-select @error('doctor_id') is-invalid @enderror"
                                required>
                          @foreach($clinic->doctors as $doc)
                            <option value="{{ $doc->id }}"
                              @selected(old('doctor_id', $appointment->doctor_id) == $doc->id)>
                              Dr. {{ $doc->name }}
                            </option>
                          @endforeach
                        </select>

                        <div class="field-help">
                          Select the doctor who will handle your visit.
                        </div>

                        @error('doctor_id')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-clock-history"></i>
                      Schedule
                    </div>

                    <div class="date-time-grid">
                      <div>
                        <label for="appointment_date" class="form-label">Date</label>
                        <input type="date"
                               id="appointment_date"
                               name="appointment_date"
                               class="form-control @error('appointment_date') is-invalid @enderror"
                               value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d')) }}"
                               required>

                        <div class="field-help">
                          Pick the new date for your appointment.
                        </div>

                        @error('appointment_date')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div>
                        <label for="appointment_time" class="form-label">Time</label>
                        <input type="time"
                               id="appointment_time"
                               name="appointment_time"
                               class="form-control @error('appointment_time') is-invalid @enderror"
                               value="{{ old('appointment_time', substr($appointment->getRawOriginal('appointment_time'), 0, 5)) }}"
                               required>

                        <div class="field-help">
                          Select the preferred time for your visit.
                        </div>

                        @error('appointment_time')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <div class="edit-actions">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-save me-2"></i>
                      Save Changes
                    </button>

                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                      <i class="bi bi-x-circle me-2"></i>
                      Cancel
                    </a>
                  </div>
                </div>
              </form>
            </div>

            <div class="side-stack">
              <aside class="edit-summary-card">
                <div class="summary-head">
                  <div class="summary-icon">
                    <i class="bi bi-calendar-event"></i>
                  </div>

                  <div>
                    <h6 class="summary-title">Current Appointment</h6>
                    <div class="summary-sub">Before saving changes</div>
                  </div>
                </div>

                <div class="summary-list">
                  <div class="summary-item">
                    <div class="summary-label">Clinic</div>
                    <div class="summary-value">{{ $clinic->name }}</div>
                  </div>

                  <div class="summary-item">
                    <div class="summary-label">Service</div>
                    <div class="summary-value">
                      {{ $appointment->service->name ?? '—' }}
                    </div>
                  </div>

                  <div class="summary-item">
                    <div class="summary-label">Doctor</div>
                    <div class="summary-value">
                      {{ $appointment->doctor ? 'Dr. ' . $appointment->doctor->name : '—' }}
                    </div>
                  </div>

                  <div class="summary-item">
                    <div class="summary-label">Date</div>
                    <div class="summary-value">
                      {{ $appointment->appointment_date ? $appointment->appointment_date->format('M j, Y') : '—' }}
                    </div>
                  </div>

                  <div class="summary-item">
                    <div class="summary-label">Time</div>
                    <div class="summary-value">
                      {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : '—' }}
                    </div>
                  </div>
                </div>
              </aside>

              <aside class="edit-help-card">
                <h6 class="help-title">
                  <i class="bi bi-lightbulb"></i>
                  Reminder
                </h6>

                <ul class="help-list">
                  <li>Review the service, doctor, date, and time before saving.</li>
                  <li>Changing the schedule may affect your clinic queue timing.</li>
                  <li>After saving, check your appointments page for the updated details.</li>
                </ul>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection