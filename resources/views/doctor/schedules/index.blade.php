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

          <div class="mb-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
              <option value="">Select a service</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>
                  {{ $service->name }}
                </option>
              @endforeach
            </select>
            @error('service_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
<<<<<<< Updated upstream

          <div class="mb-3">
            <label class="form-label fw-semibold">Days of Week</label>
            <div class="day-grid">
              @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $day)
                <div>
                  <input type="checkbox" name="days[]" id="day_{{ $i }}" value="{{ $i }}" @checked(collect(old('days', []))->contains((string) $i) || collect(old('days', []))->contains($i))>
                  <label for="day_{{ $i }}">{{ $day }}</label>
                </div>
              @endforeach
=======
          <div id="daySection" class="mb-3">
            <label class="form-label">Days of Week</label>
            @php
              $oldDays = collect(old('day_of_weeks', old('day_of_week') !== null ? [old('day_of_week')] : []))
                ->map(fn ($v) => (int) $v)
                ->all();
            @endphp
            <div id="multiDayWrapper" class="border rounded p-3">
              <div class="row g-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i=>$d)
                  <div class="col-6 col-md-4">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        name="day_of_weeks[]"
                        id="day_{{ $i }}"
                        value="{{ $i }}"
                        @checked(in_array($i, $oldDays, true))
                      >
                      <label class="form-check-label" for="day_{{ $i }}">{{ $d }}</label>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            @error('day_of_weeks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('day_of_weeks.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <input type="hidden" name="day_of_week" id="day_of_week_edit" value="{{ old('day_of_week') }}" disabled>
            @error('day_of_week')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Start</label>
              <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required />
              @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col">
              <label class="form-label">End</label>
              <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required />
              @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
>>>>>>> Stashed changes
            </div>
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
                <th>Day</th>
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
                  data-day="{{ $schedule->day_of_week }}"
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
                  <td>{{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$schedule->day_of_week] ?? 'Day' }}</td>
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
<<<<<<< Updated upstream
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
=======
                      <button type="button" class="btn btn-sm btn-outline-primary edit-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger delete-schedule-btn"
                        title="Delete"
                        data-action="{{ route('doctor.schedules.destroy',$s) }}"
                        data-clinic="{{ $s->clinic->name }}"
                        data-day="{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$s->day_of_week] }}"
                        data-time="{{ $startFmt }} - {{ $endFmt }}"
                      >
                        <i class="bi bi-x"></i>
                      </button>
>>>>>>> Stashed changes
                    </div>
                  </td>
                </tr>
              @empty
<<<<<<< Updated upstream
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">No schedules have been created yet.</td>
                </tr>
=======
                <tr><td colspan="5" class="text-center py-4 text-muted">No availability set.</td></tr>
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
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
=======
    <form method="POST" id="deleteScheduleForm" class="modal-content">
      @csrf
      @method('DELETE')
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Remove Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Are you sure you want to remove this schedule?</p>
        <div id="deleteScheduleSummary" class="small text-muted"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Remove</button>
      </div>
    </form>
>>>>>>> Stashed changes
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
<<<<<<< Updated upstream
  window.doctorScheduleData = {
    activeClinicId: {{ $activeClinic?->id ?? 'null' }},
    storeAction: '{{ route("doctor.schedules.store") }}',
    schedules: {!! json_encode($schedules->map(fn ($schedule) => [
      'id' => $schedule->id,
      'service' => $schedule->service?->name ?? $schedule->clinic?->name ?? 'Clinic',
      'day_of_week' => $schedule->day_of_week,
      'start_time' => substr($schedule->start_time, 0, 5),
      'end_time' => substr($schedule->end_time, 0, 5),
      'is_active' => (bool) $schedule->is_active,
    ])->values()) !!}
  };
=======
  (function(){
    const doctorSchedules = @json($doctorSchedulesPayload);

    function normalizeTime(value) {
      if (!value) return '09:00:00';
      if (value.length === 5) return value + ':00';
      if (value.length === 8) return value;
      return value.padEnd(8, ':00');
    }

    function formatDate(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }

    function buildEvents(rangeStart, rangeEnd) {
      const events = [];
      doctorSchedules.forEach(schedule => {
        if (!schedule.is_active) return;
        const start = new Date(rangeStart.getTime());
        const dayDiff = (schedule.day_of_week - start.getDay() + 7) % 7;
        start.setDate(start.getDate() + dayDiff);
        for (const cursor = new Date(start); cursor <= rangeEnd; cursor.setDate(cursor.getDate() + 7)) {
          const dateStamp = formatDate(cursor);
          const startTime = normalizeTime(schedule.start_time);
          const endTime = normalizeTime(schedule.end_time);
          events.push({
            id: `schedule-${schedule.id}-${dateStamp}`,
            title: schedule.clinic,
            start: `${dateStamp}T${startTime}`,
            end: `${dateStamp}T${endTime}`,
            display: 'block',
            extendedProps: {
              clinic: schedule.clinic,
              scheduleId: schedule.id,
              day_of_week: schedule.day_of_week,
              start_time: schedule.start_time,
              end_time: schedule.end_time,
            },
          });
        }
      });
      return events;
    }

    const form = document.getElementById('scheduleForm');
    if (!form) return;
    const methodInput = document.getElementById('formMethod');
    const scheduleIdInput = document.getElementById('schedule_id');
    const submitLabel = document.getElementById('submitLabel');
    const cancelBtn = document.getElementById('cancelEdit');
    const clinicSelect = form.querySelector('select[name="clinic_id"]');
    const daySection = document.getElementById('daySection');
    const multiDayWrapper = document.getElementById('multiDayWrapper');
    const dayChecks = Array.from(form.querySelectorAll('input[name="day_of_weeks[]"]'));
    const dayEditInput = form.querySelector('input[name="day_of_week"]');
    const startInput = form.querySelector('input[name="start_time"]');
    const endInput = form.querySelector('input[name="end_time"]');
    const activeInput = form.querySelector('input[name="is_active"]');
    const deleteModalEl = document.getElementById('deleteScheduleModal');
    const deleteForm = document.getElementById('deleteScheduleForm');
    const deleteSummary = document.getElementById('deleteScheduleSummary');
    const deleteModal = (deleteModalEl && window.bootstrap)
      ? new bootstrap.Modal(deleteModalEl)
      : null;

    function setCreateMode(){
      if (daySection) daySection.classList.remove('d-none');
      if (multiDayWrapper) multiDayWrapper.classList.remove('d-none');
      if (dayEditInput) {
        dayEditInput.disabled = true;
        dayEditInput.value = '';
      }
      dayChecks.forEach(input => {
        input.disabled = false;
      });
    }

    function setEditMode(dayValue){
      if (daySection) daySection.classList.add('d-none');
      if (multiDayWrapper) multiDayWrapper.classList.remove('d-none');
      if (dayEditInput) {
        dayEditInput.disabled = false;
        dayEditInput.value = dayValue;
      }
      dayChecks.forEach(input => {
        input.checked = false;
        input.disabled = true;
      });
    }

    function resetForm(){
      form.action = '{{ route('doctor.schedules.store') }}';
      methodInput.value = 'POST';
      scheduleIdInput.value = '';
      submitLabel.textContent = 'Save';
      cancelBtn.classList.add('d-none');
      form.reset();
      setCreateMode();
    }

    setCreateMode();

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.dataset.id;
        clinicSelect.value = tr.dataset.clinic;
        setEditMode(tr.dataset.day);
        startInput.value = tr.dataset.start;
        endInput.value = tr.dataset.end;
        if (tr.dataset.active !== undefined) {
          activeInput.checked = tr.dataset.active === '1';
        }
        form.action = '{{ route('doctor.schedules.update','__ID__') }}'.replace('__ID__', id);
        methodInput.value = 'PUT';
        scheduleIdInput.value = id;
        submitLabel.textContent = 'Update';
        cancelBtn.classList.remove('d-none');
        clinicSelect.focus();
      });
    });

    cancelBtn.addEventListener('click', resetForm);

    form.addEventListener('submit', function (event) {
      if (methodInput.value !== 'POST') return;
      const selectedCount = dayChecks.filter(input => input.checked).length;
      if (selectedCount > 0) return;
      event.preventDefault();
      window.alert('Please select at least one day.');
    });

    document.querySelectorAll('.delete-schedule-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!deleteForm || !deleteModal) {
          return;
        }
        const action = btn.getAttribute('data-action');
        const clinic = btn.getAttribute('data-clinic') || 'Clinic';
        const day = btn.getAttribute('data-day') || '';
        const time = btn.getAttribute('data-time') || '';

        deleteForm.setAttribute('action', action || '');
        if (deleteSummary) {
          const parts = [clinic, day, time].filter(Boolean);
          deleteSummary.textContent = parts.join(' • ');
        }
        deleteModal.show();
      });
    });

    const calendarEl = document.getElementById('doctorScheduleCalendar');
    if (calendarEl && window.FullCalendar) {
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek'
        },
        events(info, successCallback) {
          const startRange = new Date(info.start.getTime());
          const endRange = new Date(info.end.getTime());
          successCallback(buildEvents(startRange, endRange));
        },
        nowIndicator: true,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
        eventClick(info) {
          const scheduleId = info?.event?.extendedProps?.scheduleId;
          if (!scheduleId) return;
          const targetRow = document.querySelector(`tr[data-id="${scheduleId}"]`);
          if (targetRow) {
            const editBtn = targetRow.querySelector('.edit-btn');
            if (editBtn) {
              editBtn.click();
              targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
        },
        dateClick(info) {
          if (!startInput || !endInput) return;
          if (methodInput.value !== 'PUT') {
            const target = dayChecks.find(input => Number(input.value) === info.date.getDay());
            if (target) {
              target.checked = true;
            }
          }
          if (!clinicSelect.value && clinicSelect.options.length > 1) {
            clinicSelect.selectedIndex = 1;
          }
          if (!startInput.value) {
            startInput.value = '09:00';
          }
          if (!endInput.value) {
            endInput.value = '10:00';
          }
          activeInput.checked = true;
          clinicSelect.focus();
        }
      });
      calendar.render();
    }
  })();
>>>>>>> Stashed changes
</script>
<script src="{{ asset('js/doctor-schedule-manager.js') }}"></script>
@endpush
