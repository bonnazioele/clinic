@extends('layouts.patient-dashboard')

@section('title', 'Book Appointment')

@section('content')
<div class="booking-page py-4">
  <div class="booking-shell">
    <div class="booking-topbar">
      <div>
        <div class="booking-eyebrow">
          <i class="bi bi-calendar2-plus"></i>
          Appointment
        </div>
        <h1 class="booking-title mb-1">Book an Appointment</h1>
        <p class="booking-subtitle mb-0">
          Select your clinic, service, doctor, and preferred schedule.
        </p>
      </div>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('appointments.store') }}" enctype="multipart/form-data" id="appointmentForm">
      @csrf

      <div class="booking-layout">
        {{-- LEFT: FORM --}}
        <div class="booking-main">
          <div class="booking-card glass-card">
            <div class="booking-card-head">
              <div class="booking-card-icon">
                <i class="bi bi-ui-checks-grid"></i>
              </div>
              <div>
                <h5 class="mb-1">Appointment Details</h5>
                <p class="text-muted mb-0">Complete the information below to continue.</p>
              </div>
            </div>

            <div class="booking-steps">
              <div class="booking-step active">
                <span>1</span>
                <small>Choose</small>
              </div>
              <div class="booking-step-line"></div>
              <div class="booking-step active">
                <span>2</span>
                <small>Schedule</small>
              </div>
              <div class="booking-step-line"></div>
              <div class="booking-step active">
                <span>3</span>
                <small>Confirm</small>
              </div>
            </div>

            <div class="booking-section">
              <div class="section-label">
                <i class="bi bi-hospital"></i>
                <span>Clinic and Service</span>
              </div>

              <div class="row g-3">
                <div class="col-lg-4">
                  <label class="form-label fw-semibold">
                    Clinic <span class="text-danger">*</span>
                  </label>
                  <select id="clinic" name="clinic_id" class="form-select booking-input @error('clinic_id') is-invalid @enderror" required>
                    <option value="">Select a clinic</option>
                    @foreach($clinics as $c)
                      <option value="{{ $c->id }}"
                        {{ old('clinic_id') == $c->id || ($clinics->count() == 1) ? 'selected' : '' }}>
                        {{ $c->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('clinic_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label class="form-label fw-semibold">
                    Service <span class="text-danger">*</span>
                  </label>
                  <select id="service" name="service_id" class="form-select booking-input @error('service_id') is-invalid @enderror" required>
                    <option value="">First select a clinic</option>
                  </select>
                  @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label class="form-label fw-semibold">
                    Doctor <span class="text-danger">*</span>
                  </label>
                  <select id="doctor" name="doctor_id" class="form-select booking-input @error('doctor_id') is-invalid @enderror" required>
                    <option value="">First select a service</option>
                  </select>
                  @error('doctor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="booking-section">
              <div class="section-label">
                <i class="bi bi-clock-history"></i>
                <span>Schedule</span>
              </div>

              <div class="row g-3">
                <div class="col-lg-4">
                  <label class="form-label fw-semibold">
                    Date <span class="text-danger">*</span>
                  </label>
                  <input
                    type="date"
                    id="apptDate"
                    name="appointment_date"
                    class="form-control booking-input @error('appointment_date') is-invalid @enderror"
                    value="{{ old('appointment_date') }}"
                    min="{{ date('Y-m-d') }}"
                    required
                  >
                  @error('appointment_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label class="form-label fw-semibold">Day</label>
                  <input
                    type="text"
                    id="apptDay"
                    class="form-control booking-input booking-readonly"
                    value=""
                    placeholder="Auto-filled"
                    readonly
                  >
                </div>

                <div class="col-lg-4">
                  <label class="form-label fw-semibold">
                    Available Time Slot <span class="text-danger">*</span>
                  </label>
                  <select
                    id="timeSlot"
                    name="appointment_time"
                    class="form-select booking-input @error('appointment_time') is-invalid @enderror"
                    @error('appointment_time') data-slot-error="1" @enderror
                    required
                  >
                    <option value="">Select doctor & date first</option>
                  </select>
                  <div class="form-text small" id="slotHelp">Choose a starting time.</div>
                  @error('appointment_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="doctor-schedule-box mt-3" id="doctorScheduleWrap" style="display:none;">
                <div class="doctor-schedule-head">
                  <i class="bi bi-calendar-week"></i>
                  <span>Doctor Availability</span>
                </div>
                <div id="doctorScheduleInfo"></div>
              </div>
            </div>

            <div class="booking-section">
              <div class="section-label">
                <i class="bi bi-file-earmark-medical"></i>
                <span>Supporting Document</span>
              </div>

              <div class="upload-box">
                <label class="form-label fw-semibold mb-2">
                  Medical Document <span class="text-muted">(optional)</span>
                </label>
                <input
                  type="file"
                  name="medical_document"
                  id="medicalDocument"
                  class="form-control booking-input @error('medical_document') is-invalid @enderror"
                  accept=".pdf,image/*"
                >
                @error('medical_document')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text mt-2">
                  Attach a prescription, lab result, or related file. Accepted: PDF or image, max 5MB.
                </div>
              </div>
            </div>

            <div class="booking-actions">
              <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg px-4">
                <i class="bi bi-arrow-left me-2"></i>Cancel
              </a>
              <button id="bookSubmit" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-calendar-check-fill me-2"></i>Book Now
              </button>
            </div>
          </div>
        </div>

        {{-- RIGHT: SUMMARY --}}
        <div class="booking-sidebar">
          <div class="booking-card glass-card booking-sticky">
            <div class="booking-card-head booking-card-head-sm">
              <div class="booking-card-icon">
                <i class="bi bi-journal-check"></i>
              </div>
              <div>
                <h5 class="mb-1">Appointment Summary</h5>
                <p class="text-muted mb-0">Review your selected details.</p>
              </div>
            </div>

            <div class="summary-list">
              <div class="summary-item">
                <span class="summary-label">Clinic</span>
                <strong id="summaryClinic">Not selected</strong>
              </div>
              <div class="summary-item">
                <span class="summary-label">Service</span>
                <strong id="summaryService">Not selected</strong>
              </div>
              <div class="summary-item">
                <span class="summary-label">Doctor</span>
                <strong id="summaryDoctor">Not selected</strong>
              </div>
              <div class="summary-item">
                <span class="summary-label">Date</span>
                <strong id="summaryDate">Not selected</strong>
              </div>
              <div class="summary-item">
                <span class="summary-label">Time</span>
                <strong id="summaryTime">Not selected</strong>
              </div>
              <div class="summary-item">
                <span class="summary-label">Attachment</span>
                <strong id="summaryFile">No file uploaded</strong>
              </div>
            </div>

            <div class="booking-note-card">
              <div class="note-card-head">
                <i class="bi bi-info-circle"></i>
                <span>Before booking</span>
              </div>
              <ul class="booking-note-list mb-0">
                <li>Choose the correct clinic and service first.</li>
                <li>Only available time slots can be booked.</li>
                <li>Upload any supporting medical file if needed.</li>
                <li>Please double-check your selected schedule before submitting.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const clinicSelect   = document.getElementById('clinic');
  const serviceSelect  = document.getElementById('service');
  const doctorSelect   = document.getElementById('doctor');
  const dateInput      = document.getElementById('apptDate');
  const dayInput       = document.getElementById('apptDay');
  const slotSelect     = document.getElementById('timeSlot');
  const scheduleWrap   = document.getElementById('doctorScheduleWrap');
  const scheduleInfo   = document.getElementById('doctorScheduleInfo');
  const form           = document.getElementById('appointmentForm');
  const submitBtn      = document.getElementById('bookSubmit');
  const fileInput      = document.getElementById('medicalDocument');

  const summaryClinic  = document.getElementById('summaryClinic');
  const summaryService = document.getElementById('summaryService');
  const summaryDoctor  = document.getElementById('summaryDoctor');
  const summaryDate    = document.getElementById('summaryDate');
  const summaryTime    = document.getElementById('summaryTime');
  const summaryFile    = document.getElementById('summaryFile');

  let lastPopulateAt = Date.now();

  const clinicMap = {};
  @foreach($clinics as $c)
    clinicMap["{{ $c->id }}"] = {
      services: @json($c->services->map(function($s){ return ['id' => $s->id, 'name' => $s->name]; })->values()),
      doctors:  @json($c->doctors->map(function($d){
                  return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'services' => $d->services->pluck('id')->values()
                  ];
                })->values())
    };
  @endforeach

  function resetSelect(el, placeholder) {
    el.innerHTML = '';
    el.add(new Option(placeholder, ''));
  }

  function getSelectedText(select) {
    if (!select || select.selectedIndex < 0) return '';
    const option = select.options[select.selectedIndex];
    return option && option.value ? option.text : '';
  }

  function formatDateReadable(value) {
    if (!value) return 'Not selected';
    const d = new Date(value + 'T00:00:00');
    if (isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function syncSummary() {
    summaryClinic.textContent  = getSelectedText(clinicSelect) || 'Not selected';
    summaryService.textContent = getSelectedText(serviceSelect) || 'Not selected';
    summaryDoctor.textContent  = getSelectedText(doctorSelect) || 'Not selected';
    summaryDate.textContent    = dateInput.value ? `${formatDateReadable(dateInput.value)}${dayInput.value ? ' (' + dayInput.value + ')' : ''}` : 'Not selected';
    summaryTime.textContent    = getSelectedText(slotSelect) || 'Not selected';
    summaryFile.textContent    = fileInput.files.length ? fileInput.files[0].name : 'No file uploaded';
  }

  function populateServices(clinicId) {
    resetSelect(serviceSelect, 'Select a service');
    resetSelect(doctorSelect, 'First select a service');

    const list = (clinicMap[clinicId] && clinicMap[clinicId].services) || [];
    list.forEach(s => serviceSelect.add(new Option(s.name, s.id)));

    resetAvailability();
    syncSummary();
  }

  function populateDoctors(clinicId, serviceId) {
    resetSelect(doctorSelect, 'Select a doctor');

    const docs = (clinicMap[clinicId] && clinicMap[clinicId].doctors) || [];
    docs
      .filter(d => (d.services || []).includes(Number(serviceId)))
      .forEach(d => doctorSelect.add(new Option(`Dr. ${d.name}`, d.id)));

    resetAvailability();
    syncSummary();
  }

  function resetAvailability() {
    dayInput.value = '';
    scheduleWrap.style.display = 'none';
    scheduleInfo.innerHTML = '';
    resetSelect(slotSelect, 'Select doctor & date first');
    slotSelect.value = '';
    syncSummary();
  }

  function to12(t) {
    if (!/^\d{2}:\d{2}$/.test(t)) return t;
    const [h, m] = t.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = ((h + 11) % 12 + 1);
    return `${hour}:${m.toString().padStart(2, '0')} ${ampm}`;
  }

  function renderSchedule(data) {
    if (data.message) {
      return `<div class="schedule-muted">${data.message}</div>`;
    }

    const slots = data.slots || [];
    const availableCount = slots.filter(s => s.available).length;
    const expiredCount = slots.filter(s => s.expired).length;
    const bookedCount = slots.filter(s => s.occupied && !s.expired).length;

    let html = `
      <div class="schedule-grid">
        <div class="schedule-pill">
          <span>Date</span>
          <strong>${data.date || '-'}</strong>
        </div>
        <div class="schedule-pill">
          <span>Day</span>
          <strong>${data.weekday || '-'}</strong>
        </div>
        <div class="schedule-pill">
          <span>Slot Length</span>
          <strong>${data.slot_minutes || '-'} mins</strong>
        </div>
        <div class="schedule-pill">
          <span>Available Slots</span>
          <strong>${availableCount}</strong>
        </div>
      </div>
    `;

    if (slots.length && availableCount === 0) {
      html += `
        <div class="schedule-muted mt-2">
          ${expiredCount === slots.length
            ? 'All generated slots for this date are already past. Try the next matching schedule date.'
            : `${bookedCount} slot${bookedCount === 1 ? '' : 's'} booked and ${expiredCount} past.`}
        </div>
      `;
    }

    if (data.schedule && data.schedule.length) {
      const blocks = data.schedule.map(r => `
        <span class="time-badge">${to12(r.start)} - ${to12(r.end)}</span>
      `).join('');

      html += `
        <div class="schedule-blocks">
          <div class="schedule-block-title">Doctor Schedule Blocks</div>
          <div class="time-badge-wrap">${blocks}</div>
        </div>
      `;
    }

    return html;
  }

  function slotPlaceholder(slots) {
    slots = slots || [];

    if (!slots.length) {
      return 'No schedule slots';
    }

    if (!slots.some(s => s.available)) {
      return 'No available slots';
    }

    return 'Select a time slot';
  }

  async function fetchAvailability() {
    const clinicId = clinicSelect.value;
    const doctorId = doctorSelect.value;
    const dateVal  = dateInput.value;

    if (!clinicId || !doctorId || !dateVal) {
      resetAvailability();
      return;
    }

    resetSelect(slotSelect, 'Loading...');
    syncSummary();

    try {
      const params = new URLSearchParams({
        clinic_id: clinicId,
        doctor_id: doctorId,
        date: dateVal,
        service_id: serviceSelect.value || ''
      });

      const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) throw new Error('Failed to load availability');

      const data = await res.json();

      dayInput.value = data.weekday || '';
      scheduleWrap.style.display = 'block';
      scheduleInfo.innerHTML = renderSchedule(data);

      resetSelect(slotSelect, slotPlaceholder(data.slots));

      (data.slots || []).forEach(s => {
        const label = s.display + (s.available ? '' : (s.expired ? ' - PAST' : ' - BOOKED'));
        const opt = new Option(label, s.time);
        if (!s.available) {
          opt.disabled = true;
          opt.setAttribute('data-booked', '1');
        }
        slotSelect.add(opt);
      });

      @if(old('appointment_time'))
        if ([...slotSelect.options].some(o => o.value === "{{ old('appointment_time') }}")) {
          slotSelect.value = "{{ old('appointment_time') }}";
        }
      @endif

      lastPopulateAt = Date.now();
      syncSummary();
    } catch (e) {
      resetAvailability();
      resetSelect(slotSelect, 'Error loading availability');
      syncSummary();
    }
  }

  clinicSelect.addEventListener('change', (e) => {
    const cid = e.target.value;
    if (!cid) {
      resetSelect(serviceSelect, 'First select a clinic');
      resetSelect(doctorSelect, 'First select a service');
      resetAvailability();
      syncSummary();
      return;
    }
    populateServices(cid);
  });

  serviceSelect.addEventListener('change', (e) => {
    const cid = clinicSelect.value;
    const sid = e.target.value;
    if (!cid || !sid) {
      resetSelect(doctorSelect, 'First select a service');
      resetAvailability();
      syncSummary();
      return;
    }
    populateDoctors(cid, sid);
  });

  doctorSelect.addEventListener('change', () => {
    fetchAvailability();
    syncSummary();
  });

  dateInput.addEventListener('change', () => {
    fetchAvailability();
    syncSummary();
  });

  slotSelect.addEventListener('change', syncSummary);
  fileInput.addEventListener('change', syncSummary);
  clinicSelect.addEventListener('change', syncSummary);
  serviceSelect.addEventListener('change', syncSummary);

  @if(old('clinic_id'))
    populateServices("{{ old('clinic_id') }}");
    serviceSelect.value = "{{ old('service_id') }}";
    if (serviceSelect.value) {
      populateDoctors("{{ old('clinic_id') }}", "{{ old('service_id') }}");
    }
    doctorSelect.value = "{{ old('doctor_id') }}";
  @else
    if (clinicSelect.value) {
      populateServices(clinicSelect.value);
    }
  @endif

  @if(old('doctor_id') && old('appointment_date'))
    setTimeout(fetchAvailability, 120);
  @endif

  if (slotSelect.dataset.slotError) {
    const fb = slotSelect.parentElement.querySelector('.invalid-feedback');
    slotSelect.classList.remove('is-invalid');
    if (fb) fb.remove();
    setTimeout(fetchAvailability, 30);
  }

  form.addEventListener('submit', async (e) => {
    const chosen = slotSelect.value;
    if (!chosen) return;

    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';

    try {
      const params = new URLSearchParams({
        clinic_id: clinicSelect.value,
        doctor_id: doctorSelect.value,
        date: dateInput.value,
        service_id: serviceSelect.value || ''
      });

      const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) throw new Error('Failed to validate slot');

      const data = await res.json();
      const stillAvailable = (data.slots || []).some(s => s.available && s.time === chosen);

      if (!stillAvailable) {
        resetSelect(slotSelect, slotPlaceholder(data.slots));
        (data.slots || []).forEach(s => {
          const opt = new Option(s.display + (s.available ? '' : (s.expired ? ' - PAST' : ' - BOOKED')), s.time);
          if (!s.available) opt.disabled = true;
          slotSelect.add(opt);
        });

        slotSelect.value = '';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-calendar-check-fill me-2"></i>Book Now';
        syncSummary();
        return;
      }

      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking...';
      form.submit();
    } catch (err) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-calendar-check-fill me-2"></i>Book Now';
    }
  });

  setInterval(() => {
    if (clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  }, 60000);

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  });

  slotSelect.addEventListener('focus', () => {
    if (Date.now() - lastPopulateAt > 30000 && clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  });

  syncSummary();
});
</script>
@endpush

@push('styles')
<style>
  .booking-page {
    width: 100%;
  }

  .booking-shell {
    width: min(92%, 1380px);
    margin: 0 auto;
  }

  .booking-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
  }

  .booking-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .4rem .8rem;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.08);
    color: #0d6efd;
    font-size: .83rem;
    font-weight: 700;
    margin-bottom: .75rem;
  }

  .booking-title {
    font-size: clamp(1.6rem, 2vw, 2.15rem);
    font-weight: 800;
    color: #0f172a;
  }

  .booking-subtitle {
    color: #64748b;
    font-size: .98rem;
  }

  .booking-layout {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(320px, 430px);
    gap: 1.5rem;
    align-items: start;
  }

  .booking-card {
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 24px;
    padding: 1.4rem;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
  }

  .glass-card {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  .booking-sticky {
    position: sticky;
    top: 100px;
  }

  .booking-card-head {
    display: flex;
    align-items: center;
    gap: .95rem;
    margin-bottom: 1.25rem;
  }

  .booking-card-head-sm {
    margin-bottom: 1rem;
  }

  .booking-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(13,110,253,.14), rgba(13,110,253,.05));
    color: #0d6efd;
    font-size: 1.2rem;
    flex-shrink: 0;
  }

  .booking-steps {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1.4rem;
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
  }

  .booking-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .25rem;
    min-width: 64px;
  }

  .booking-step span {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #0d6efd;
    font-weight: 800;
  }

  .booking-step small {
    color: #475569;
    font-weight: 600;
  }

  .booking-step-line {
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, #bfdbfe, #dbeafe);
    border-radius: 999px;
  }

  .booking-section + .booking-section {
    margin-top: 1.4rem;
  }

  .section-label {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: .9rem;
    font-size: .98rem;
  }

  .section-label i {
    color: #0d6efd;
  }

  .booking-input {
    min-height: 50px;
    border-radius: 14px;
    border: 1px solid #dbe2ea;
    box-shadow: none !important;
  }

  .booking-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 .18rem rgba(13, 110, 253, .12) !important;
  }

  .booking-readonly {
    background: #f8fafc;
  }

  .doctor-schedule-box {
    border-radius: 18px;
    border: 1px solid #dbeafe;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    padding: 1rem;
  }

  .doctor-schedule-head {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: .9rem;
  }

  .schedule-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .75rem;
  }

  .schedule-pill {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 14px;
    padding: .85rem .9rem;
  }

  .schedule-pill span {
    display: block;
    font-size: .78rem;
    color: #64748b;
    margin-bottom: .15rem;
  }

  .schedule-pill strong {
    color: #0f172a;
    font-size: .95rem;
  }

  .schedule-blocks {
    margin-top: 1rem;
  }

  .schedule-block-title {
    font-weight: 700;
    color: #334155;
    margin-bottom: .55rem;
  }

  .time-badge-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .time-badge {
    display: inline-flex;
    align-items: center;
    padding: .5rem .75rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #dbeafe;
    font-size: .82rem;
    font-weight: 700;
  }

  .schedule-muted {
    color: #64748b;
    font-size: .95rem;
  }

  .upload-box {
    border: 1px dashed #cbd5e1;
    border-radius: 18px;
    padding: 1rem;
    background: #fafcff;
  }

  .booking-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .75rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
  }

  .summary-list {
    display: flex;
    flex-direction: column;
    gap: .75rem;
    margin-bottom: 1rem;
  }

  .summary-item {
    padding: .9rem 1rem;
    border-radius: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
  }

  .summary-label {
    display: block;
    font-size: .78rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .2rem;
  }

  .summary-item strong {
    color: #0f172a;
    font-size: .96rem;
    word-break: break-word;
  }

  .booking-note-card {
    border-radius: 18px;
    background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
    border: 1px solid #dbeafe;
    padding: 1rem;
  }

  .note-card-head {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-weight: 800;
    color: #1e3a8a;
    margin-bottom: .75rem;
  }

  .booking-note-list {
    padding-left: 1.1rem;
    color: #475569;
  }

  .booking-note-list li + li {
    margin-top: .45rem;
  }

  #timeSlot option[disabled] {
    text-decoration: line-through;
    color: #6c757d;
    font-style: italic;
  }

  @media (max-width: 1199.98px) {
    .booking-layout {
      grid-template-columns: 1fr;
    }

    .booking-sticky {
      position: static;
      top: auto;
    }
  }

  @media (max-width: 767.98px) {
    .booking-shell {
      width: min(95%, 100%);
    }

    .booking-card {
      padding: 1rem;
      border-radius: 20px;
    }

    .booking-steps {
      gap: .5rem;
      padding: .85rem;
    }

    .booking-step {
      min-width: 54px;
    }

    .booking-step span {
      width: 30px;
      height: 30px;
      font-size: .9rem;
    }

    .schedule-grid {
      grid-template-columns: 1fr;
    }

    .booking-actions {
      justify-content: stretch;
    }

    .booking-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush
