@extends('layouts.app')
@section('title','My Schedule')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css">
<style>
  #doctorScheduleCalendar .fc-event { cursor: pointer; }
  #doctorScheduleCalendar { min-height: 520px; }
</style>
@endpush

@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <h2 class="fw-bold text-primary mb-1 d-flex align-items-center"><i class="bi bi-calendar-range medical-icon me-2"></i>My Schedule</h2>
    <p class="text-muted mb-0">Manage your availability across clinics</p>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="medical-card p-4 h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-plus-circle medical-icon me-2"></i>Add Availability</h5>
        <form id="scheduleForm" method="POST" action="{{ route('doctor.schedules.store') }}">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="schedule_id" id="schedule_id" value="">
          <div class="mb-3">
            <label class="form-label">Clinic</label>
            <select name="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
              <option value="">Select clinic</option>
              @foreach($clinics as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id')==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
            @error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label mb-2">Days of Week</label>
            <div id="multiDayWrap" class="border rounded p-2 @error('days') border-danger @enderror @error('days.*') border-danger @enderror">
              <div class="d-flex flex-wrap gap-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $d)
                  <div class="form-check form-check-inline m-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      name="days[]"
                      id="day_{{ $i }}"
                      value="{{ $i }}"
                      @checked(collect(old('days', []))->contains((string)$i) || collect(old('days', []))->contains($i))
                    >
                    <label class="form-check-label" for="day_{{ $i }}">{{ $d }}</label>
                  </div>
                @endforeach
              </div>
              <small class="text-muted d-block mt-2">Select one or more days.</small>
            </div>
            @error('days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('days.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

            <div id="singleDayWrap" class="mt-2 d-none">
              <select name="day_of_week" id="singleDaySelect" class="form-select @error('day_of_week') is-invalid @enderror" disabled>
                <option value="">Select day</option>
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i=>$d)
                  <option value="{{ $i }}" @selected(old('day_of_week')==$i)>{{ $d }}</option>
                @endforeach
              </select>
              @error('day_of_week')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
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
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Start Date <small class="text-muted">(optional)</small></label>
              <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" />
              @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col">
              <label class="form-label">End Date <small class="text-muted">(optional)</small></label>
              <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" />
              @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div id="dayDateWarning" class="alert alert-warning py-2 px-3 small d-none mb-3" role="alert"></div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
            <label class="form-check-label" for="is_active">Active</label>
          </div>
          <div class="d-flex gap-2">
            <button id="submitBtn" class="btn btn-primary flex-grow-1"><i class="bi bi-save me-1"></i><span id="submitLabel">Save</span></button>
            <button type="button" id="cancelEdit" class="btn btn-outline-secondary d-none"><i class="bi bi-x-circle me-1"></i>Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="medical-card p-4 mb-4">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-calendar-event me-2"></i>Monthly Calendar</h5>
        <div id="doctorScheduleCalendar"></div>
      </div>
      <div class="medical-card p-4">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-list-ul medical-icon me-2"></i>Your Availability</h5>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th class="px-4 py-3">Clinic</th>
                <th class="px-4 py-3">Day</th>
                <th class="px-4 py-3">Time</th>
                <th class="px-4 py-3">Date Range</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3" width="70">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($schedules as $s)
        <tr data-id="{{ $s->id }}"
          data-clinic="{{ $s->clinic_id }}"
          data-day="{{ $s->day_of_week }}"
          data-start="{{ substr($s->start_time,0,5) }}"
          data-end="{{ substr($s->end_time,0,5) }}"
            data-start-date="{{ $s->start_date ? $s->start_date->format('Y-m-d') : '' }}"
            data-end-date="{{ $s->end_date ? $s->end_date->format('Y-m-d') : '' }}"
          data-active="{{ (int)($s->is_active ?? 1) }}">
                  <td class="px-4 py-3">{{ $s->clinic->name }}</td>
                  <td class="px-4 py-3">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$s->day_of_week] }}</td>
                  @php
                    try {
                      $startFmt = \Carbon\Carbon::createFromFormat('H:i:s', $s->start_time)->format('g:i A');
                      $endFmt = \Carbon\Carbon::createFromFormat('H:i:s', $s->end_time)->format('g:i A');
                    } catch (Exception $e) {
                      $startFmt = substr($s->start_time,0,5);
                      $endFmt = substr($s->end_time,0,5);
                    }
                  @endphp
                  <td class="px-4 py-3">{{ $startFmt }} - {{ $endFmt }}</td>
                  <td class="px-4 py-3">
                    @if($s->start_date || $s->end_date)
                      {{ $s->start_date ? $s->start_date->format('Y-m-d') : 'Any' }} - {{ $s->end_date ? $s->end_date->format('Y-m-d') : 'Any' }}
                    @else
                      Always
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    @if($s->is_active ?? true)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <div class="d-flex gap-1">
                      <button type="button" class="btn btn-sm btn-outline-primary edit-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                      <form method="POST" action="{{ route('doctor.schedules.destroy',$s) }}" class="delete-schedule-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger delete-schedule-btn" title="Delete"><i class="bi bi-x"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No availability set.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-labelledby="deleteScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteScheduleModalLabel">Remove schedule?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        This availability entry will be permanently removed.
      </div>
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
@php
  $doctorSchedulesPayload = $schedules->map(function($s) {
    return [
      'id' => $s->id,
      'clinic' => $s->clinic->name ?? 'Clinic',
      'day_of_week' => $s->day_of_week,
      'start_date' => $s->start_date ? $s->start_date->format('Y-m-d') : null,
      'end_date' => $s->end_date ? $s->end_date->format('Y-m-d') : null,
      'start_time' => substr($s->start_time, 0, 5),
      'end_time' => substr($s->end_time, 0, 5),
      'is_active' => (bool)($s->is_active ?? true),
    ];
  })->values();
@endphp
<script>
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

    function parseDateOnly(value) {
      if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
      const [y, m, d] = value.split('-').map(Number);
      return new Date(y, m - 1, d);
    }

    function stripTime(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function isWithinDateRange(date, schedule) {
      const current = stripTime(date);
      const startDate = parseDateOnly(schedule.start_date);
      const endDate = parseDateOnly(schedule.end_date);
      if (startDate && current < startDate) return false;
      if (endDate && current > endDate) return false;
      return true;
    }

    function buildEvents(rangeStart, rangeEnd) {
      const events = [];
      doctorSchedules.forEach(schedule => {
        if (!schedule.is_active) return;
        const start = new Date(rangeStart.getTime());
        const dayDiff = (schedule.day_of_week - start.getDay() + 7) % 7;
        start.setDate(start.getDate() + dayDiff);
        for (const cursor = new Date(start); cursor <= rangeEnd; cursor.setDate(cursor.getDate() + 7)) {
          if (!isWithinDateRange(cursor, schedule)) {
            continue;
          }
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
              start_date: schedule.start_date,
              end_date: schedule.end_date,
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
    const singleDayWrap = document.getElementById('singleDayWrap');
    const singleDaySelect = document.getElementById('singleDaySelect');
    const multiDayWrap = document.getElementById('multiDayWrap');
    const dayCheckboxes = Array.from(form.querySelectorAll('input[name="days[]"]'));
    const startInput = form.querySelector('input[name="start_time"]');
    const endInput = form.querySelector('input[name="end_time"]');
    const startDateInput = form.querySelector('input[name="start_date"]');
    const endDateInput = form.querySelector('input[name="end_date"]');
    const dayDateWarning = document.getElementById('dayDateWarning');
    const activeInput = form.querySelector('input[name="is_active"]');
    const deleteModalEl = document.getElementById('deleteScheduleModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteScheduleBtn');
    const deleteModal = (window.bootstrap && deleteModalEl)
      ? new window.bootstrap.Modal(deleteModalEl)
      : null;
    let pendingDeleteForm = null;

    function setCreateMode() {
      singleDayWrap.classList.add('d-none');
      singleDaySelect.disabled = true;
      singleDaySelect.value = '';
      multiDayWrap.classList.remove('d-none');
    }

    function setEditMode() {
      multiDayWrap.classList.add('d-none');
      dayCheckboxes.forEach(cb => {
        cb.checked = false;
      });
      singleDayWrap.classList.remove('d-none');
      singleDaySelect.disabled = false;
    }

    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    function getDateDay(value) {
      if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
      const [year, month, day] = value.split('-').map(Number);
      return new Date(year, month - 1, day).getDay();
    }

    function getSelectedDays() {
      if (!singleDaySelect.disabled && singleDaySelect.value !== '') {
        return [Number(singleDaySelect.value)];
      }

      return dayCheckboxes
        .filter(cb => cb.checked)
        .map(cb => Number(cb.value));
    }

    function updateDayDateWarning() {
      if (!dayDateWarning) return;

      const selectedDays = getSelectedDays();
      const warnings = [];
      const startDateDay = getDateDay(startDateInput.value);
      const endDateDay = getDateDay(endDateInput.value);

      if (selectedDays.length && startDateDay !== null && !selectedDays.includes(startDateDay)) {
        warnings.push('Start date falls on ' + dayNames[startDateDay] + ', but selected day(s): ' + selectedDays.map(d => dayNames[d]).join(', ') + '.');
      }

      if (selectedDays.length && endDateDay !== null && !selectedDays.includes(endDateDay)) {
        warnings.push('End date falls on ' + dayNames[endDateDay] + ', but selected day(s): ' + selectedDays.map(d => dayNames[d]).join(', ') + '.');
      }

      if (!warnings.length) {
        dayDateWarning.classList.add('d-none');
        dayDateWarning.innerHTML = '';
        return;
      }

      dayDateWarning.innerHTML = warnings.join('<br>') + '<br><span class="fw-semibold">Tip:</span> choose date(s) that land on the selected weekday(s).';
      dayDateWarning.classList.remove('d-none');
    }

    function hasDayDateMismatch() {
      const selectedDays = getSelectedDays();
      if (!selectedDays.length) return false;

      const startDateDay = getDateDay(startDateInput.value);
      const endDateDay = getDateDay(endDateInput.value);

      if (startDateDay !== null && !selectedDays.includes(startDateDay)) {
        return true;
      }
      if (endDateDay !== null && !selectedDays.includes(endDateDay)) {
        return true;
      }

      return false;
    }

    function resetForm(){
      form.action = '{{ route('doctor.schedules.store') }}';
      methodInput.value = 'POST';
      scheduleIdInput.value = '';
      submitLabel.textContent = 'Save';
      cancelBtn.classList.add('d-none');
      form.reset();
      setCreateMode();
      updateDayDateWarning();
    }

    if (methodInput.value === 'PUT' || scheduleIdInput.value) {
      setEditMode();
    } else {
      setCreateMode();
    }

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.dataset.id;
        setEditMode();
        clinicSelect.value = tr.dataset.clinic;
        singleDaySelect.value = tr.dataset.day;
        startInput.value = tr.dataset.start;
        endInput.value = tr.dataset.end;
        startDateInput.value = tr.dataset.startDate || '';
        endDateInput.value = tr.dataset.endDate || '';
        if (tr.dataset.active !== undefined) {
          activeInput.checked = tr.dataset.active === '1';
        }
        form.action = '{{ route('doctor.schedules.update','__ID__') }}'.replace('__ID__', id);
        methodInput.value = 'PUT';
        scheduleIdInput.value = id;
        submitLabel.textContent = 'Update';
        cancelBtn.classList.remove('d-none');
        updateDayDateWarning();
        clinicSelect.focus();
      });
    });

    cancelBtn.addEventListener('click', resetForm);

    document.querySelectorAll('.delete-schedule-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        pendingDeleteForm = e.currentTarget.closest('form.delete-schedule-form');
        if (!pendingDeleteForm) return;
        if (deleteModal) {
          deleteModal.show();
          return;
        }
        pendingDeleteForm.submit();
      });
    });

    if (confirmDeleteBtn) {
      confirmDeleteBtn.addEventListener('click', () => {
        if (!pendingDeleteForm) return;
        pendingDeleteForm.submit();
      });
    }

    if (deleteModalEl) {
      deleteModalEl.addEventListener('hidden.bs.modal', () => {
        pendingDeleteForm = null;
      });
    }

    if (singleDaySelect) {
      singleDaySelect.addEventListener('change', updateDayDateWarning);
    }
    dayCheckboxes.forEach(cb => cb.addEventListener('change', updateDayDateWarning));
    if (startDateInput) {
      startDateInput.addEventListener('change', updateDayDateWarning);
    }
    if (endDateInput) {
      endDateInput.addEventListener('change', updateDayDateWarning);
    }

    form.addEventListener('submit', e => {
      updateDayDateWarning();
      if (!hasDayDateMismatch()) {
        return;
      }
      e.preventDefault();
      if (dayDateWarning) {
        dayDateWarning.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    updateDayDateWarning();

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
          if (singleDaySelect && !singleDaySelect.disabled) {
            singleDaySelect.value = info.date.getDay();
          } else {
            const target = dayCheckboxes.find(cb => Number(cb.value) === info.date.getDay());
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
          if (startDateInput && !startDateInput.value) {
            const selectedDays = getSelectedDays();
            if (!selectedDays.length || selectedDays.includes(info.date.getDay())) {
              startDateInput.value = formatDate(info.date);
            }
          }
          activeInput.checked = true;
          updateDayDateWarning();
          clinicSelect.focus();
        }
      });
      calendar.render();
    }
  })();
</script>
@endpush
