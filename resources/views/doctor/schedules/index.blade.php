@extends('layouts.app')
@section('title', 'Doctor Schedule')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css">
<style>
  /* Use global .medical-card styles for hero and cards to match queue/dashboard UI */
  .schedule-hero h1 { font-size: 1.7rem; font-weight: 800; margin: 0; }
  .schedule-hero p { margin: .5rem 0 0; color: #4b5563; }

  .section-title {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 1rem;
  }

  .section-title i {
    color: #2563eb;
  }

  .schedule-toggle {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .75rem;
  }

  .schedule-toggle input {
    display: none;
  }

  .schedule-toggle label {
    border: 1px solid #d1d5db;
    border-radius: .85rem;
    padding: .85rem 1rem;
    cursor: pointer;
    transition: .2s ease;
    background: #fff;
    font-weight: 600;
  }

  .schedule-toggle input:checked + label {
    border-color: #2563eb;
    background: rgba(37, 99, 235, .08);
    color: #1d4ed8;
  }

  .clinic-picker {
    min-height: 132px;
  }

  .day-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: .5rem;
  }

  .day-grid input {
    display: none;
  }

  .day-grid label {
    display: block;
    text-align: center;
    padding: .7rem .4rem;
    border-radius: .6rem;
    border: 1px solid rgba(0,0,0,0.06);
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
    background: linear-gradient(135deg, #fff, #fbfbfd);
  }

  .day-grid input:checked + label {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
  }

  .time-blocks {
    background: linear-gradient(180deg, #fff, #fbfbfd);
    border: 1px solid rgba(0,0,0,0.04);
    border-radius: var(--border-radius);
    padding: 1rem;
  }

  .time-block-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto;
    gap: .75rem;
    align-items: end;
    background: linear-gradient(180deg,#fff,#fafbfd);
    border: 1px solid rgba(0,0,0,0.04);
    border-radius: var(--border-radius-sm);
    padding: .9rem;
    margin-bottom: .75rem;
  }

  .time-block-row:last-child {
    margin-bottom: 0;
  }

  .time-block-label {
    display: block;
    font-size: .82rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: .35rem;
  }

  .break-note {
    grid-column: 1 / -1;
    font-size: .82rem;
    color: #475569;
    padding-top: .15rem;
  }

  .time-remove {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: .7rem;
  }

  .schedule-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .35rem .75rem;
    font-size: .8rem;
    font-weight: 700;
  }

  .schedule-badge.recurring {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .schedule-badge.one-time {
    background: #dcfce7;
    color: #166534;
  }

  #doctorScheduleCalendar {
    min-height: 500px;
  }

  .schedules-table th,
  .schedules-table td {
    vertical-align: middle;
  }

  @media (max-width: 991px) {
    .day-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .time-block-row {
      grid-template-columns: 1fr 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4 schedule-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
      <div>
          <h1 class="fw-bold text-primary d-flex align-items-center"><i class="bi bi-calendar2-week me-2"></i>Doctor Availability Management</h1>
        <p>Set recurring clinic hours, one-time sessions, and multiple consultation blocks from one place.</p>
      </div>
      <div class="text-end small opacity-75">
        <div class="fw-semibold small text-muted">Active Clinic</div>
        <div>
          <span class="badge bg-primary rounded-pill py-2 px-3">
            <i class="bi bi-building me-1"></i>
            {{ $activeClinic?->name ?? 'No active clinic' }}
          </span>
        </div>
      </div>
    </div>
  </div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Please fix the highlighted errors.</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="medical-card p-4 h-100">
        <div class="section-title"><i class="bi bi-sliders"></i>Create or Update Schedule</div>

        <form id="scheduleForm" method="POST" action="{{ route('doctor.schedules.store') }}">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="schedule_id" id="schedule_id" value="">
          <input type="hidden" name="day_of_week" id="day_of_week" value="">
          @php $initialScheduleType = old('schedule_type', 'recurring'); @endphp
          <input type="hidden" name="schedule_type" id="schedule_type" value="{{ $initialScheduleType }}">

          <div class="mb-3">
            <label class="form-label fw-semibold">Service(s)</label>
            <select name="service_ids[]" id="service_ids" class="form-select clinic-picker @error('service_ids') is-invalid @enderror" multiple>
              @foreach($services as $service)
                <option value="{{ $service->id }}" @selected(collect(old('service_ids', []))->contains($service->id))>
                  {{ $service->name }}
                </option>
              @endforeach
            </select>
            @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Schedule Type</label>
            <div class="schedule-toggle">
              <input type="radio" id="scheduleRecurring" name="schedule_type_toggle" value="recurring" @checked($initialScheduleType === 'recurring')>
              <label for="scheduleRecurring"><i class="bi bi-arrow-repeat me-2"></i>Recurring weekly</label>
              <input type="radio" id="scheduleOneTime" name="schedule_type_toggle" value="one_time" @checked($initialScheduleType === 'one_time')>
              <label for="scheduleOneTime"><i class="bi bi-calendar2-date me-2"></i>One-time / specific date</label>
            </div>
          </div>

          <div id="recurringFields">
            <div class="mb-3">
              <label class="form-label fw-semibold">Days of Week</label>
              <div class="day-grid">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $day)
                  <div>
                    <input type="checkbox" name="days[]" id="day_{{ $i }}" value="{{ $i }}" @checked(collect(old('days', []))->contains((string) $i) || collect(old('days', []))->contains($i))>
                    <label for="day_{{ $i }}">{{ $day }}</label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="mb-3">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="limitRecurringPeriod">
                <label class="form-check-label fw-semibold" for="limitRecurringPeriod">Limit this recurring schedule to a date range</label>
              </div>

              <div id="recurringPeriodFields" class="row g-3 d-none">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Start Date</label>
                  <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">End Date</label>
                  <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>
              </div>
            </div>
          </div>

          <div id="oneTimeFields" class="d-none mb-3">
            <label class="form-label fw-semibold">Specific Date</label>
            <input type="date" name="one_time_date" id="one_time_date" class="form-control" value="{{ old('one_time_date') }}">
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label fw-semibold mb-0">Consultation Hours</label>
              <button type="button" class="btn btn-outline-primary btn-sm" id="addTimeBlockBtn">
                <i class="bi bi-plus-lg me-1"></i>Add block
              </button>
            </div>
            <div class="time-blocks" id="timeBlocksWrap">
              @php
                $oldBlocks = old('time_blocks', [['start_time' => old('start_time'), 'end_time' => old('end_time')]]);
              @endphp
              @foreach($oldBlocks as $idx => $block)
                <div class="time-block-row">
                  <div>
                    <span class="time-block-label">Start time</span>
                    <input type="time" name="time_blocks[{{ $idx }}][start_time]" class="form-control" value="{{ $block['start_time'] ?? '' }}" required>
                  </div>
                  <div class="d-flex align-items-end justify-content-center text-muted fw-semibold">to</div>
                  <div>
                    <span class="time-block-label">End time</span>
                    <input type="time" name="time_blocks[{{ $idx }}][end_time]" class="form-control" value="{{ $block['end_time'] ?? '' }}" required>
                  </div>
                  <div class="d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-outline-danger time-remove remove-time-block-btn" title="Remove block">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <div class="break-note d-none"></div>
                </div>
              @endforeach
            </div>
            @error('time_blocks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
            <label class="form-check-label fw-semibold" for="is_active">Active</label>
          </div>

          <div id="scheduleFormMessage" class="alert alert-warning d-none"></div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1" id="submitBtn">
              <i class="bi bi-save me-1"></i><span id="submitLabel">Save schedule</span>
            </button>
            <button type="button" class="btn btn-outline-secondary d-none" id="cancelEdit">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="medical-card p-4 mb-4">
        <div class="section-title"><i class="bi bi-calendar-event"></i>Calendar Preview</div>
        <div id="doctorScheduleCalendar"></div>
      </div>

      <div class="medical-card p-4">
        <div class="section-title"><i class="bi bi-list-check"></i>Saved Schedules</div>
        <div class="table-responsive">
          <table class="table align-middle schedules-table mb-0">
            <thead class="table-light">
              <tr>
                <th>Service</th>
                <th>Type</th>
                <th>Day / Date</th>
                <th>Consultation Hours</th>
                <th>Effective Period</th>
                <th>Status</th>
                <th width="90">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($schedules as $schedule)
                <tr
                  data-id="{{ $schedule->id }}"
                  data-clinic-id="{{ $schedule->clinic_id }}"
                  data-service-id="{{ $schedule->service_id }}"
                  data-schedule-type="{{ $schedule->schedule_type ?? 'recurring' }}"
                  data-day="{{ $schedule->day_of_week }}"
                  data-start-date="{{ optional($schedule->start_date)->format('Y-m-d') }}"
                  data-end-date="{{ optional($schedule->end_date)->format('Y-m-d') }}"
                  data-start-time="{{ substr($schedule->start_time, 0, 5) }}"
                  data-end-time="{{ substr($schedule->end_time, 0, 5) }}"
                  data-active="{{ (int) ($schedule->is_active ?? 1) }}"
                >
                  <td>
                    @if($schedule->service)
                      {{ $schedule->service->name }}
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="schedule-badge {{ ($schedule->schedule_type ?? 'recurring') === 'one_time' ? 'one-time' : 'recurring' }}">
                      {{ ($schedule->schedule_type ?? 'recurring') === 'one_time' ? 'One-time' : 'Recurring' }}
                    </span>
                  </td>
                  <td>
                    @if(($schedule->schedule_type ?? 'recurring') === 'one_time')
                      {{ optional($schedule->start_date)->format('M d, Y') ?? 'Specific date' }}
                    @else
                      {{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$schedule->day_of_week] ?? 'Day' }}
                    @endif
                  </td>
                  <td>
                    @php
                      try {
                        $startFmt = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('g:i A');
                        $endFmt = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('g:i A');
                      } catch (\Throwable $e) {
                        $startFmt = substr($schedule->start_time, 0, 5);
                        $endFmt = substr($schedule->end_time, 0, 5);
                      }
                    @endphp
                    {{ $startFmt }} - {{ $endFmt }}
                  </td>
                  <td>
                    @if($schedule->start_date || $schedule->end_date)
                      {{ $schedule->start_date ? $schedule->start_date->format('M d, Y') : 'Any' }} - {{ $schedule->end_date ? $schedule->end_date->format('M d, Y') : 'Any' }}
                    @else
                      Open-ended
                    @endif
                  </td>
                  <td>
                    @if($schedule->is_active)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <button type="button" class="btn btn-sm btn-outline-primary edit-btn" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <form method="POST" action="{{ route('doctor.schedules.destroy', $schedule) }}" class="delete-schedule-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger delete-schedule-btn" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">No schedules have been created yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Remove schedule?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">This schedule entry will be permanently removed.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteScheduleBtn">Remove</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  window.doctorScheduleData = {
    activeClinicId: {{ $activeClinic?->id ?? 'null' }},
    storeAction: '{{ route("doctor.schedules.store") }}',
    schedules: {!! json_encode($schedules->map(fn ($schedule) => [
      'id' => $schedule->id,
      'service' => $schedule->service?->name ?? $schedule->clinic?->name ?? 'Clinic',
      'schedule_type' => $schedule->schedule_type ?? 'recurring',
      'day_of_week' => $schedule->day_of_week,
      'start_date' => optional($schedule->start_date)->format('Y-m-d'),
      'end_date' => optional($schedule->end_date)->format('Y-m-d'),
      'start_time' => substr($schedule->start_time, 0, 5),
      'end_time' => substr($schedule->end_time, 0, 5),
      'is_active' => (bool) $schedule->is_active,
    ])->values()) !!}
  };
</script>
<script src="{{ asset('js/doctor-schedule-manager.js') }}"></script>
@endpush
