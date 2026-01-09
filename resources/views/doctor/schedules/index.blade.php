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
            <label class="form-label">Day of Week</label>
            <select name="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror" required>
              <option value="">Select day</option>
              @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i=>$d)
                <option value="{{ $i }}" @selected(old('day_of_week')==$i)>{{ $d }}</option>
              @endforeach
            </select>
            @error('day_of_week')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    @if($s->is_active ?? true)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <div class="d-flex gap-1">
                      <button type="button" class="btn btn-sm btn-outline-primary edit-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                      <form method="POST" action="{{ route('doctor.schedules.destroy',$s) }}" onsubmit="return confirm('Remove schedule?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-x"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No availability set.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
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
    const daySelect = form.querySelector('select[name="day_of_week"]');
    const startInput = form.querySelector('input[name="start_time"]');
    const endInput = form.querySelector('input[name="end_time"]');
    const activeInput = form.querySelector('input[name="is_active"]');

    function resetForm(){
      form.action = '{{ route('doctor.schedules.store') }}';
      methodInput.value = 'POST';
      scheduleIdInput.value = '';
      submitLabel.textContent = 'Save';
      cancelBtn.classList.add('d-none');
      form.reset();
    }

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.dataset.id;
        clinicSelect.value = tr.dataset.clinic;
        daySelect.value = tr.dataset.day;
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
          if (!daySelect || !startInput || !endInput) return;
          daySelect.value = info.date.getDay();
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
</script>
@endpush
