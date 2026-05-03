@extends('doctor.layouts.app')

@section('title', 'Doctor Schedules')

@section('doctor-content')
@php
    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    $uniqueServices = collect($services)
        ->filter()
        ->unique(function ($service) {
            return strtolower(trim($service->name ?? ''));
        })
        ->values();

    $oldBlocks = old('time_blocks', [
        ['start_time' => old('start_time'), 'end_time' => old('end_time')]
    ]);

    $calendarSchedules = $schedules->map(function ($schedule) {
        return [
            'id' => $schedule->id,
            'service_id' => $schedule->service_id,
            'service' => $schedule->service?->name ?? $schedule->clinic?->name ?? 'Clinic',
            'day_of_week' => (int) $schedule->day_of_week,
            'start_date' => optional($schedule->start_date)->format('Y-m-d'),
            'end_date' => optional($schedule->end_date)->format('Y-m-d'),
            'start_time' => substr((string) $schedule->start_time, 0, 5),
            'end_time' => substr((string) $schedule->end_time, 0, 5),
            'is_active' => (bool) $schedule->is_active,
        ];
    })->values();
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css">

<style>
  .doctor-schedule-page {
    width: 100%;
  }

  .doctor-schedule-shell {
    max-width: 1460px;
    margin: 0 auto;
  }

  .schedule-hero {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    padding: 28px;
    margin-bottom: 24px;
    background:
      radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 30%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
      rgba(255, 255, 255, 0.84);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
  }

  .schedule-hero-icon {
    width: 70px;
    height: 70px;
    border-radius: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    color: #ffffff;
    font-size: 2rem;
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.26);
    flex-shrink: 0;
  }

  .schedule-hero-title {
    font-size: clamp(2rem, 3vw, 2.75rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    color: #0f172a;
    margin: 0;
  }

  .schedule-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 1rem;
  }

  .schedule-clinic-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #ffffff;
    padding: 18px 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
  }

  .schedule-clinic-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 4px;
  }

  .schedule-clinic-value {
    color: #111827;
    font-size: 1.2rem;
    font-weight: 900;
    margin: 0;
  }

  .schedule-clinic-icon {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(16, 185, 129, 0.14);
    color: #047857;
    font-size: 1.4rem;
    flex-shrink: 0;
  }

  .schedule-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.84);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
    height: 100%;
  }

  .schedule-card-header {
    padding: 22px 24px 18px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.96));
  }

  .schedule-card-body {
    padding: 24px;
  }

  .schedule-section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #111827;
    font-size: 1.12rem;
    font-weight: 900;
    margin: 0;
  }

  .schedule-section-title span {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.11);
    color: #2563eb;
    font-size: 1.25rem;
    flex-shrink: 0;
  }

  .schedule-section-subtitle {
    color: #64748b;
    margin: 8px 0 0 58px;
    font-size: 0.95rem;
  }

  .form-label {
    color: #334155;
    font-size: 0.9rem;
    font-weight: 900;
    margin-bottom: 8px;
  }

  .form-control,
  .form-select {
    border-radius: 16px;
    border: 1px solid rgba(15, 23, 42, 0.12);
    padding: 12px 14px;
    color: #111827;
    background-color: #ffffff;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(37, 99, 235, 0.55);
    box-shadow: 0 0 0 0.22rem rgba(37, 99, 235, 0.12);
  }

  .helper-text {
    color: #64748b;
    font-size: 0.82rem;
    margin-top: 7px;
  }

  .auto-mode-box {
    border: 1px solid rgba(37, 99, 235, 0.14);
    border-radius: 20px;
    padding: 16px;
    background: rgba(37, 99, 235, 0.06);
    margin-bottom: 22px;
  }

  .auto-mode-title {
    color: #1d4ed8;
    font-weight: 900;
    margin-bottom: 4px;
  }

  .auto-mode-text {
    color: #64748b;
    margin: 0;
    font-size: 0.9rem;
  }

  .day-picker {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 10px;
  }

  .day-picker input {
    display: none;
  }

  .day-picker label {
    min-height: 58px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    border-radius: 18px;
    background: #ffffff;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 900;
    transition: 0.18s ease;
    user-select: none;
  }

  .day-picker label:hover {
    border-color: rgba(37, 99, 235, 0.35);
    color: #2563eb;
    transform: translateY(-1px);
  }

  .day-picker input:checked + label {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
  }

  .selected-days-preview {
    border-radius: 16px;
    background: #f8fafc;
    border: 1px solid rgba(15, 23, 42, 0.08);
    padding: 12px 14px;
    color: #64748b;
    font-size: 0.9rem;
    margin-top: 12px;
  }

  .date-range-box {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    background: #f8fafc;
    padding: 16px;
  }

  .time-blocks {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #f8fafc;
    padding: 16px;
  }

  .time-block-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto;
    gap: 12px;
    align-items: end;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    background: #ffffff;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.04);
  }

  .time-block-row:last-child {
    margin-bottom: 0;
  }

  .time-label {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 900;
    display: block;
    margin-bottom: 7px;
  }

  .time-divider {
    color: #94a3b8;
    font-weight: 900;
    padding-bottom: 13px;
  }

  .remove-time-block-btn {
    width: 44px;
    height: 44px;
    border-radius: 15px;
  }

  .overnight-note {
    display: none;
    grid-column: 1 / -1;
    border-radius: 14px;
    padding: 10px 12px;
    background: rgba(245, 158, 11, 0.12);
    color: #92400e;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .overnight-note.show {
    display: block;
  }

  .schedule-main-btn,
  .schedule-outline-btn {
    border-radius: 16px;
    padding: 12px 18px;
    font-weight: 900;
  }

  .calendar-wrap {
    min-height: 600px;
  }

  .fc {
    font-family: inherit;
  }

  .fc .fc-toolbar {
    gap: 10px;
    flex-wrap: wrap;
  }

  .fc .fc-toolbar-title {
    color: #111827;
    font-size: 1.35rem;
    font-weight: 900;
  }

  .fc .fc-button {
    border: 0 !important;
    border-radius: 14px !important;
    background: #2563eb !important;
    font-weight: 900 !important;
    box-shadow: none !important;
    text-transform: lowercase;
  }

  .fc .fc-button:hover {
    background: #1d4ed8 !important;
  }

  .fc .fc-button-primary:not(:disabled).fc-button-active {
    background: #0f172a !important;
  }

  .fc-theme-standard td,
  .fc-theme-standard th {
    border-color: rgba(15, 23, 42, 0.08);
  }

  .fc-theme-standard .fc-scrollgrid {
    border-color: rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    overflow: hidden;
  }

  .fc .fc-col-header-cell-cushion,
  .fc .fc-daygrid-day-number {
    color: #334155;
    text-decoration: none;
    font-weight: 900;
  }

  .fc-event {
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .fc .fc-daygrid-event {
    margin: 2px 5px !important;
    padding: 0 !important;
  }

  .fc-month-clean-event {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    max-width: 100%;
    padding: 4px 7px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.08);
    color: #1d4ed8;
    font-weight: 900;
    font-size: 0.72rem;
    line-height: 1;
    overflow: hidden;
  }

  .fc-month-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #2563eb;
    flex-shrink: 0;
  }

  .fc-week-clean-event {
    display: flex;
    flex-direction: column;
    gap: 2px;
    border-radius: 10px;
    padding: 6px 8px;
    background: #2563eb;
    color: #ffffff;
    font-size: 0.78rem;
    line-height: 1.2;
    overflow: hidden;
  }

  .fc-week-clean-event strong {
    font-size: 0.76rem;
    font-weight: 900;
  }

  .fc-week-clean-event span,
  .fc-week-clean-event small {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .fc-week-clean-event small {
    opacity: 0.9;
    font-size: 0.68rem;
    font-weight: 700;
  }

  .fc-daygrid-more-link {
    margin-left: 6px;
    color: #2563eb !important;
    font-weight: 900;
    font-size: 0.75rem;
  }

  .fc-daygrid-day-events {
    min-height: 18px !important;
  }

  .fc-daygrid-event-harness {
    margin-top: 1px !important;
  }

  .saved-schedule-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    transition: 0.2s ease;
  }

  .saved-schedule-card + .saved-schedule-card {
    margin-top: 14px;
  }

  .saved-schedule-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.10);
  }

  .saved-schedule-toggle {
    width: 100%;
    border: 0;
    background: #ffffff;
    padding: 18px;
    text-align: left;
    cursor: pointer;
  }

  .saved-schedule-toggle:not(.collapsed) {
    background: linear-gradient(180deg, #ffffff, #f8fbff);
  }

  .saved-schedule-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .saved-schedule-left {
    min-width: 0;
  }

  .saved-schedule-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 900;
    margin: 0 0 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .saved-schedule-subtitle {
    color: #64748b;
    font-size: 0.88rem;
    font-weight: 700;
    margin: 0;
  }

  .saved-schedule-pills {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
  }

  .saved-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 7px 11px;
    font-size: 0.76rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .saved-pill.blue {
    background: rgba(37, 99, 235, 0.10);
    color: #1d4ed8;
  }

  .saved-pill.green {
    background: rgba(16, 185, 129, 0.14);
    color: #047857;
  }

  .saved-pill.gray {
    background: rgba(148, 163, 184, 0.16);
    color: #475569;
  }

  .saved-pill.amber {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e;
  }

  .saved-chevron {
    color: #64748b;
    transition: 0.2s ease;
  }

  .saved-schedule-toggle:not(.collapsed) .saved-chevron {
    transform: rotate(180deg);
  }

  .saved-schedule-details {
    padding: 0 18px 18px;
  }

  .saved-details-panel {
    border-top: 1px solid rgba(15, 23, 42, 0.07);
    padding-top: 16px;
  }

  .saved-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .saved-detail-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 18px;
    padding: 14px;
    background: #f8fafc;
  }

  .saved-detail-label {
    color: #94a3b8;
    font-size: 0.72rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 5px;
  }

  .saved-detail-value {
    color: #111827;
    font-weight: 850;
    margin: 0;
    font-size: 0.92rem;
  }

  .saved-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
  }

  .empty-state {
    border: 1px dashed rgba(148, 163, 184, 0.55);
    border-radius: 22px;
    padding: 34px 18px;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
  }

  .schedule-modal {
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
  }

  .schedule-modal .modal-header {
    border-bottom: 0;
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #ffffff;
  }

  .schedule-modal .btn-close {
    filter: invert(1);
  }

  @media (max-width: 1199.98px) {
    .calendar-wrap {
      min-height: 540px;
    }
  }

  @media (max-width: 991.98px) {
    .schedule-hero {
      border-radius: 24px;
      padding: 22px;
    }

    .day-picker {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .time-block-row {
      grid-template-columns: 1fr 1fr;
    }

    .time-divider {
      display: none;
    }

    .saved-details-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .saved-schedule-top {
      align-items: flex-start;
      flex-direction: column;
    }

    .saved-schedule-pills {
      justify-content: flex-start;
    }
  }

  @media (max-width: 575.98px) {
    .schedule-card-header,
    .schedule-card-body {
      padding: 18px;
    }

    .day-picker {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>

<div class="doctor-schedule-page">
  <div class="doctor-schedule-shell">

    @if (session('status'))
      <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
        <i class="bi bi-check2-circle me-2"></i>
        {{ session('status') }}
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
        <i class="bi bi-check2-circle me-2"></i>
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
        <div class="fw-bold mb-1">
          <i class="bi bi-exclamation-triangle me-2"></i>
          Please fix the highlighted errors.
        </div>

        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="schedule-hero">
      <div class="row align-items-center g-4">
        <div class="col-xl-8">
          <div class="d-flex align-items-center gap-3 gap-md-4">
            <div class="schedule-hero-icon">
              <i class="bi bi-calendar2-week"></i>
            </div>

            <div>
              <h1 class="schedule-hero-title">Manage Schedule</h1>
              <p class="schedule-hero-text">
                Select a service, choose one or more days, then set consultation hours. Overnight hours like 8:00 PM to 1:00 AM are allowed.
              </p>
            </div>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="schedule-clinic-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div class="min-w-0">
                <div class="schedule-clinic-label">Active Clinic</div>
                <p class="schedule-clinic-value text-truncate">
                  {{ $activeClinic?->name ?? 'No active clinic' }}
                </p>
              </div>

              <span class="schedule-clinic-icon">
                <i class="bi bi-hospital"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 align-items-start">
      <div class="col-xl-5">
        <div class="schedule-card">
          <div class="schedule-card-header">
            <h5 class="schedule-section-title">
              <span><i class="bi bi-pencil-square"></i></span>
              Create or Update Schedule
            </h5>
            <p class="schedule-section-subtitle">
              One clean form for one day or multiple days.
            </p>
          </div>

          <div class="schedule-card-body">
            <form id="scheduleForm" method="POST" action="{{ route('doctor.schedules.store') }}">
              @csrf

              <input type="hidden" name="_method" id="formMethod" value="POST">
              <input type="hidden" name="schedule_id" id="schedule_id" value="">
              <input type="hidden" name="schedule_type" id="schedule_type" value="recurring">

              <div class="mb-4">
                <label class="form-label">Service</label>

                <select name="service_ids[]"
                        id="service_ids"
                        class="form-select @error('service_ids') is-invalid @enderror"
                        required>
                  <option value="" selected disabled>Please select a service</option>

                  @forelse($uniqueServices as $service)
                    <option value="{{ $service->id }}" @selected(collect(old('service_ids', []))->contains($service->id))>
                      {{ $service->name }}
                    </option>
                  @empty
                    <option value="" disabled>No services available</option>
                  @endforelse
                </select>

                <div class="helper-text">
                  Duplicate service names are hidden.
                </div>

                @error('service_ids')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="auto-mode-box">
                <div class="auto-mode-title">
                  <i class="bi bi-magic me-1"></i>
                  Automatic schedule mode
                </div>
                <p class="auto-mode-text">
                  Pick one day for a single weekly schedule, or pick multiple days to create schedules for each selected day.
                </p>
              </div>

              <div class="mb-4">
                <label class="form-label">Available Days</label>

                <div class="day-picker">
                  @foreach($dayNames as $i => $day)
                    <div>
                      <input type="checkbox"
                             name="days[]"
                             id="day_{{ $i }}"
                             value="{{ $i }}"
                             @checked(collect(old('days', []))->contains((string) $i) || collect(old('days', []))->contains($i))>
                      <label for="day_{{ $i }}">{{ $day }}</label>
                    </div>
                  @endforeach
                </div>

                <div class="selected-days-preview" id="selectedDaysPreview">
                  No days selected yet.
                </div>
              </div>

              <div class="mb-4">
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="limitRecurringPeriod">
                  <label class="form-check-label fw-bold" for="limitRecurringPeriod">
                    Limit schedule to a date range
                  </label>
                </div>

                <div id="recurringPeriodFields" class="date-range-box d-none">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Start Date</label>
                      <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">End Date</label>
                      <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                  <label class="form-label mb-0">Consultation Hours</label>

                  <button type="button" class="btn btn-outline-primary btn-sm schedule-outline-btn" id="addTimeBlockBtn">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add block
                  </button>
                </div>

                <div class="time-blocks" id="timeBlocksWrap">
                  @foreach($oldBlocks as $idx => $block)
                    <div class="time-block-row">
                      <div>
                        <span class="time-label">Start time</span>
                        <input type="time"
                               name="time_blocks[{{ $idx }}][start_time]"
                               class="form-control time-start"
                               value="{{ $block['start_time'] ?? '' }}"
                               required>
                      </div>

                      <div class="time-divider">to</div>

                      <div>
                        <span class="time-label">End time</span>
                        <input type="time"
                               name="time_blocks[{{ $idx }}][end_time]"
                               class="form-control time-end"
                               value="{{ $block['end_time'] ?? '' }}"
                               required>
                      </div>

                      <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger remove-time-block-btn" title="Remove block">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </div>

                      <div class="overnight-note">
                        <i class="bi bi-moon-stars me-1"></i>
                        Overnight schedule detected. This block will continue into the next day.
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="helper-text">
                  Example: 8:00 PM to 1:00 AM is allowed and will be treated as an overnight block.
                </div>

                @error('time_blocks')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label fw-bold" for="is_active">
                  Active schedule
                </label>
              </div>

              <div id="scheduleFormMessage" class="alert alert-warning rounded-4 d-none"></div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary schedule-main-btn flex-grow-1" id="submitBtn">
                  <i class="bi bi-save me-1"></i>
                  <span id="submitLabel">Save schedule</span>
                </button>

                <button type="button" class="btn btn-outline-secondary schedule-outline-btn d-none" id="cancelEdit">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="schedule-card mb-4">
          <div class="schedule-card-header">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
              <div>
                <h5 class="schedule-section-title">
                  <span><i class="bi bi-calendar-event"></i></span>
                  Calendar Preview
                </h5>
                <p class="schedule-section-subtitle">
                  Month view is compact. Use week view for full schedule details.
                </p>
              </div>

              <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                <i class="bi bi-calendar2-week me-1"></i>
                Schedule View
              </span>
            </div>
          </div>

          <div class="schedule-card-body">
            <div class="calendar-wrap" id="doctorScheduleCalendar"></div>
          </div>
        </div>

        <div class="schedule-card">
          <div class="schedule-card-header">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
              <div>
                <h5 class="schedule-section-title">
                  <span><i class="bi bi-list-check"></i></span>
                  Saved Schedules
                </h5>
                <p class="schedule-section-subtitle">
                  Click a schedule card to expand details.
                </p>
              </div>

              <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                {{ $schedules->count() }} saved
              </span>
            </div>
          </div>

          <div class="schedule-card-body">
            @if($schedules->count())
              <div id="savedSchedulesAccordion">
                @foreach($schedules as $schedule)
                  @php
                    $rawStart = substr((string) $schedule->start_time, 0, 5);
                    $rawEnd = substr((string) $schedule->end_time, 0, 5);

                    try {
                        $startFmt = \Carbon\Carbon::createFromFormat('H:i', $rawStart)->format('g:i A');
                        $endFmt = \Carbon\Carbon::createFromFormat('H:i', $rawEnd)->format('g:i A');
                    } catch (\Throwable $e) {
                        $startFmt = $rawStart;
                        $endFmt = $rawEnd;
                    }

                    $isOvernight = $rawStart > $rawEnd;
                    $dayLabel = $dayNames[$schedule->day_of_week] ?? 'Day';
                    $nextDayLabel = $dayNames[(($schedule->day_of_week ?? 0) + 1) % 7] ?? 'Next day';
                  @endphp

                  <div class="saved-schedule-card"
                       data-id="{{ $schedule->id }}"
                       data-service-id="{{ $schedule->service_id }}"
                       data-day="{{ $schedule->day_of_week }}"
                       data-start-date="{{ optional($schedule->start_date)->format('Y-m-d') }}"
                       data-end-date="{{ optional($schedule->end_date)->format('Y-m-d') }}"
                       data-start-time="{{ $rawStart }}"
                       data-end-time="{{ $rawEnd }}"
                       data-active="{{ (int) ($schedule->is_active ?? 1) }}">

                    <button class="saved-schedule-toggle collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#savedScheduleCollapse{{ $schedule->id }}"
                            aria-expanded="false"
                            aria-controls="savedScheduleCollapse{{ $schedule->id }}">

                      <div class="saved-schedule-top">
                        <div class="saved-schedule-left">
                          <p class="saved-schedule-title">
                            {{ $schedule->service?->name ?? 'Service' }}
                          </p>

                          <p class="saved-schedule-subtitle">
                            {{ $dayLabel }} · {{ $startFmt }} - {{ $endFmt }}{{ $isOvernight ? ' · Overnight' : '' }}
                          </p>
                        </div>

                        <div class="saved-schedule-pills">
                          <span class="saved-pill blue">
                            <i class="bi bi-calendar2-day"></i>
                            {{ $dayLabel }}
                          </span>

                          @if($schedule->is_active)
                            <span class="saved-pill green">
                              <i class="bi bi-check-circle"></i>
                              Active
                            </span>
                          @else
                            <span class="saved-pill gray">
                              <i class="bi bi-pause-circle"></i>
                              Inactive
                            </span>
                          @endif

                          @if($isOvernight)
                            <span class="saved-pill amber">
                              <i class="bi bi-moon-stars"></i>
                              Overnight
                            </span>
                          @endif

                          <i class="bi bi-chevron-down saved-chevron"></i>
                        </div>
                      </div>
                    </button>

                    <div id="savedScheduleCollapse{{ $schedule->id }}"
                         class="collapse"
                         data-bs-parent="#savedSchedulesAccordion">

                      <div class="saved-schedule-details">
                        <div class="saved-details-panel">
                          <div class="saved-details-grid">
                            <div class="saved-detail-card">
                              <div class="saved-detail-label">Service</div>
                              <p class="saved-detail-value">
                                {{ $schedule->service?->name ?? 'Service' }}
                              </p>
                            </div>

                            <div class="saved-detail-card">
                              <div class="saved-detail-label">Day</div>
                              <p class="saved-detail-value">
                                {{ $dayLabel }}
                              </p>
                            </div>

                            <div class="saved-detail-card">
                              <div class="saved-detail-label">Consultation Hours</div>
                              <p class="saved-detail-value">
                                {{ $startFmt }} - {{ $endFmt }}

                                @if($isOvernight)
                                  <br>
                                  <span class="text-warning-emphasis small">
                                    Ends on {{ $nextDayLabel }}
                                  </span>
                                @endif
                              </p>
                            </div>

                            <div class="saved-detail-card">
                              <div class="saved-detail-label">Effective Period</div>
                              <p class="saved-detail-value">
                                @if($schedule->start_date || $schedule->end_date)
                                  {{ $schedule->start_date ? $schedule->start_date->format('M d, Y') : 'Any' }}
                                  -
                                  {{ $schedule->end_date ? $schedule->end_date->format('M d, Y') : 'Any' }}
                                @else
                                  Open-ended
                                @endif
                              </p>
                            </div>
                          </div>

                          <div class="saved-actions">
                            <button type="button" class="btn btn-outline-primary schedule-outline-btn edit-btn">
                              <i class="bi bi-pencil me-1"></i>
                              Edit Schedule
                            </button>

                            <form method="POST" action="{{ route('doctor.schedules.destroy', $schedule) }}" class="delete-schedule-form">
                              @csrf
                              @method('DELETE')

                              <button type="button" class="btn btn-outline-danger schedule-outline-btn delete-schedule-btn">
                                <i class="bi bi-trash me-1"></i>
                                Delete
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="empty-state">
                <div class="fs-1 mb-2">
                  <i class="bi bi-calendar-x"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No schedules yet</h5>
                <p class="mb-0">Create your first availability schedule using the form.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content schedule-modal">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-trash3 me-2"></i>
          Remove schedule?
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <p class="mb-0 text-muted">
          This schedule entry will be permanently removed. This action cannot be undone.
        </p>
      </div>

      <div class="modal-footer border-0 pt-0 px-4 pb-4">
        <button type="button" class="btn btn-outline-secondary schedule-outline-btn" data-bs-dismiss="modal">
          Cancel
        </button>

        <button type="button" class="btn btn-danger schedule-main-btn" id="confirmDeleteScheduleBtn">
          <i class="bi bi-trash3 me-1"></i>
          Remove
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  const form = document.getElementById('scheduleForm');
  const formMethod = document.getElementById('formMethod');
  const scheduleId = document.getElementById('schedule_id');
  const submitLabel = document.getElementById('submitLabel');
  const cancelEdit = document.getElementById('cancelEdit');
  const messageBox = document.getElementById('scheduleFormMessage');
  const days = document.querySelectorAll('input[name="days[]"]');
  const selectedDaysPreview = document.getElementById('selectedDaysPreview');
  const limitRecurringPeriod = document.getElementById('limitRecurringPeriod');
  const recurringPeriodFields = document.getElementById('recurringPeriodFields');
  const startDate = document.getElementById('start_date');
  const endDate = document.getElementById('end_date');
  const serviceSelect = document.getElementById('service_ids');
  const isActive = document.getElementById('is_active');
  const timeBlocksWrap = document.getElementById('timeBlocksWrap');
  const addTimeBlockBtn = document.getElementById('addTimeBlockBtn');

  const storeAction = @json(route('doctor.schedules.store'));
  const updateActionBase = @json(url('/doctor/schedules'));
  const schedules = @json($calendarSchedules);

  let deleteForm = null;

  function updateSelectedDaysPreview() {
    const selected = Array.from(days)
      .filter(day => day.checked)
      .map(day => dayLabels[parseInt(day.value, 10)]);

    if (!selected.length) {
      selectedDaysPreview.textContent = 'No days selected yet.';
      return;
    }

    if (selected.length === 1) {
      selectedDaysPreview.textContent = 'Selected day: ' + selected[0] + '. This will create one weekly schedule.';
      return;
    }

    selectedDaysPreview.textContent = 'Selected days: ' + selected.join(', ') + '. This will create schedules for each selected day.';
  }

  function updateOvernightNotice(row) {
    const startInput = row.querySelector('.time-start');
    const endInput = row.querySelector('.time-end');
    const note = row.querySelector('.overnight-note');

    if (!startInput || !endInput || !note) return;

    const start = startInput.value;
    const end = endInput.value;

    if (start && end && start > end) {
      note.classList.add('show');
    } else {
      note.classList.remove('show');
    }
  }

  function reindexTimeBlocks() {
    const rows = timeBlocksWrap.querySelectorAll('.time-block-row');

    rows.forEach(function (row, index) {
      const inputs = row.querySelectorAll('input[type="time"]');

      if (inputs[0]) {
        inputs[0].name = `time_blocks[${index}][start_time]`;
      }

      if (inputs[1]) {
        inputs[1].name = `time_blocks[${index}][end_time]`;
      }

      updateOvernightNotice(row);
    });
  }

  function bindTimeInputs(row) {
    const startInput = row.querySelector('.time-start');
    const endInput = row.querySelector('.time-end');

    if (startInput) {
      startInput.addEventListener('input', function () {
        updateOvernightNotice(row);
      });
    }

    if (endInput) {
      endInput.addEventListener('input', function () {
        updateOvernightNotice(row);
      });
    }

    updateOvernightNotice(row);
  }

  function bindRemoveButtons() {
    timeBlocksWrap.querySelectorAll('.remove-time-block-btn').forEach(function (button) {
      button.onclick = function () {
        const rows = timeBlocksWrap.querySelectorAll('.time-block-row');

        if (rows.length <= 1) {
          showMessage('At least one consultation hour block is required.');
          return;
        }

        button.closest('.time-block-row').remove();
        reindexTimeBlocks();
      };
    });
  }

  function addTimeBlock(start = '', end = '') {
    const index = timeBlocksWrap.querySelectorAll('.time-block-row').length;

    const row = document.createElement('div');
    row.className = 'time-block-row';
    row.innerHTML = `
      <div>
        <span class="time-label">Start time</span>
        <input type="time" name="time_blocks[${index}][start_time]" class="form-control time-start" value="${start}" required>
      </div>

      <div class="time-divider">to</div>

      <div>
        <span class="time-label">End time</span>
        <input type="time" name="time_blocks[${index}][end_time]" class="form-control time-end" value="${end}" required>
      </div>

      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-outline-danger remove-time-block-btn" title="Remove block">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="overnight-note">
        <i class="bi bi-moon-stars me-1"></i>
        Overnight schedule detected. This block will continue into the next day.
      </div>
    `;

    timeBlocksWrap.appendChild(row);
    bindRemoveButtons();
    bindTimeInputs(row);
  }

  function clearTimeBlocks() {
    timeBlocksWrap.innerHTML = '';
    addTimeBlock();
  }

  function showMessage(text) {
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  function hideMessage() {
    messageBox.textContent = '';
    messageBox.classList.add('d-none');
  }

  function resetForm() {
    form.setAttribute('action', storeAction);
    formMethod.value = 'POST';
    scheduleId.value = '';
    submitLabel.textContent = 'Save schedule';
    cancelEdit.classList.add('d-none');
    hideMessage();

    form.reset();

    days.forEach(day => {
      day.checked = false;
    });

    limitRecurringPeriod.checked = false;
    recurringPeriodFields.classList.add('d-none');
    startDate.value = '';
    endDate.value = '';
    isActive.checked = true;
    serviceSelect.value = '';

    clearTimeBlocks();
    updateSelectedDaysPreview();
  }

  function formatTimeForDisplay(timeValue) {
    if (!timeValue) return '';

    const parts = timeValue.split(':');
    let hour = parseInt(parts[0], 10);
    const minute = parts[1] || '00';
    const suffix = hour >= 12 ? 'PM' : 'AM';

    hour = hour % 12;
    if (hour === 0) hour = 12;

    return `${hour}:${minute} ${suffix}`;
  }

  function nextDayIndex(dayIndex) {
    return (parseInt(dayIndex, 10) + 1) % 7;
  }

  days.forEach(day => {
    day.addEventListener('change', updateSelectedDaysPreview);
  });

  limitRecurringPeriod.addEventListener('change', function () {
    recurringPeriodFields.classList.toggle('d-none', !limitRecurringPeriod.checked);

    if (!limitRecurringPeriod.checked) {
      startDate.value = '';
      endDate.value = '';
    }
  });

  addTimeBlockBtn.addEventListener('click', function () {
    addTimeBlock();
  });

  bindRemoveButtons();
  timeBlocksWrap.querySelectorAll('.time-block-row').forEach(bindTimeInputs);
  updateSelectedDaysPreview();

  form.addEventListener('submit', function (event) {
    hideMessage();

    const selectedDays = Array.from(days).filter(day => day.checked);
    const selectedService = serviceSelect.value;

    if (!selectedService) {
      event.preventDefault();
      showMessage('Please select a service.');
      return;
    }

    if (!selectedDays.length) {
      event.preventDefault();
      showMessage('Please select at least one available day.');
      return;
    }

    const rows = timeBlocksWrap.querySelectorAll('.time-block-row');

    for (const row of rows) {
      const timeInputs = row.querySelectorAll('input[type="time"]');
      const start = timeInputs[0]?.value;
      const end = timeInputs[1]?.value;

      if (!start || !end) {
        event.preventDefault();
        showMessage('Please complete all consultation hour blocks.');
        return;
      }

      if (start === end) {
        event.preventDefault();
        showMessage('Start time and end time cannot be exactly the same.');
        return;
      }

      /*
        start > end is allowed.
        Example: 20:00 to 01:00 means overnight schedule.
      */
    }
  });

  document.querySelectorAll('.edit-btn').forEach(function (button) {
    button.addEventListener('click', function () {
      const item = button.closest('.saved-schedule-card');

      if (!item) return;

      const id = item.dataset.id;
      const serviceId = item.dataset.serviceId;
      const day = item.dataset.day;
      const start = item.dataset.startTime;
      const end = item.dataset.endTime;
      const startValue = item.dataset.startDate || '';
      const endValue = item.dataset.endDate || '';
      const activeValue = item.dataset.active === '1';

      scheduleId.value = id;
      formMethod.value = 'PUT';
      submitLabel.textContent = 'Update schedule';
      cancelEdit.classList.remove('d-none');
      form.setAttribute('action', `${updateActionBase}/${id}`);

      serviceSelect.value = String(serviceId);

      days.forEach(dayInput => {
        dayInput.checked = dayInput.value === String(day);
      });

      startDate.value = startValue;
      endDate.value = endValue;

      if (startValue || endValue) {
        limitRecurringPeriod.checked = true;
        recurringPeriodFields.classList.remove('d-none');
      } else {
        limitRecurringPeriod.checked = false;
        recurringPeriodFields.classList.add('d-none');
      }

      isActive.checked = activeValue;

      timeBlocksWrap.innerHTML = '';
      addTimeBlock(start, end);

      updateSelectedDaysPreview();
      hideMessage();
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  cancelEdit.addEventListener('click', resetForm);

  document.querySelectorAll('.delete-schedule-btn').forEach(function (button) {
    button.addEventListener('click', function () {
      deleteForm = button.closest('form');

      if (window.bootstrap && bootstrap.Modal) {
        const modal = new bootstrap.Modal(document.getElementById('deleteScheduleModal'));
        modal.show();
      } else if (confirm('Remove this schedule?')) {
        deleteForm.submit();
      }
    });
  });

  document.getElementById('confirmDeleteScheduleBtn').addEventListener('click', function () {
    if (deleteForm) {
      deleteForm.submit();
    }
  });

  const calendarEl = document.getElementById('doctorScheduleCalendar');

  if (calendarEl && window.FullCalendar) {
    const calendarEvents = [];

    schedules.forEach(function (schedule) {
      if (!schedule.is_active) return;

      const isOvernight = schedule.start_time > schedule.end_time;
      const fullTitle = `${schedule.service} • ${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)}`;
      const timeLabel = `${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)}`;

      if (!isOvernight) {
        calendarEvents.push({
          title: fullTitle,
          daysOfWeek: [String(schedule.day_of_week)],
          startTime: schedule.start_time,
          endTime: schedule.end_time,
          startRecur: schedule.start_date || undefined,
          endRecur: schedule.end_date || undefined,
          extendedProps: {
            serviceLabel: schedule.service,
            timeLabel: timeLabel
          }
        });
      } else {
        calendarEvents.push({
          title: `${schedule.service} • ${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)} overnight`,
          daysOfWeek: [String(schedule.day_of_week)],
          startTime: schedule.start_time,
          endTime: '23:59',
          startRecur: schedule.start_date || undefined,
          endRecur: schedule.end_date || undefined,
          extendedProps: {
            serviceLabel: schedule.service,
            timeLabel: `${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)}`,
            overnightLabel: 'Continues overnight'
          }
        });

        calendarEvents.push({
          title: `${schedule.service} • ${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)} overnight`,
          daysOfWeek: [String(nextDayIndex(schedule.day_of_week))],
          startTime: '00:00',
          endTime: schedule.end_time,
          startRecur: schedule.start_date || undefined,
          endRecur: schedule.end_date || undefined,
          extendedProps: {
            serviceLabel: schedule.service,
            timeLabel: `${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)}`,
            overnightLabel: 'Continues from previous day'
          }
        });
      }
    });

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      events: calendarEvents,

      dayMaxEvents: 1,
      dayMaxEventRows: 1,
      displayEventTime: false,
      eventDisplay: 'block',

      moreLinkText: function (num) {
        return '+' + num + ' more';
      },

      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
      },

      views: {
        dayGridMonth: {
          dayMaxEvents: 1,
          displayEventTime: false
        },
        timeGridWeek: {
          dayMaxEvents: false,
          displayEventTime: true
        }
      },

      eventContent: function (arg) {
        const isMonthView = arg.view.type === 'dayGridMonth';

        if (isMonthView) {
          return {
            html: `
              <div class="fc-month-clean-event" title="${arg.event.title}">
                <span class="fc-month-dot"></span>
                <span>Available</span>
              </div>
            `
          };
        }

        return {
          html: `
            <div class="fc-week-clean-event" title="${arg.event.title}">
              <strong>${arg.event.extendedProps.timeLabel || ''}</strong>
              <span>${arg.event.extendedProps.serviceLabel || arg.event.title}</span>
              ${
                arg.event.extendedProps.overnightLabel
                  ? `<small>${arg.event.extendedProps.overnightLabel}</small>`
                  : ''
              }
            </div>
          `
        };
      }
    });

    calendar.render();
  }
});
</script>
@endsection