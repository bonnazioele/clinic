@extends('layouts.app')

@section('title', 'Create Appointment')

@push('styles')
<style>
  .sec-form-page { width: 96%; max-width: none; margin: 0 auto; }
  .sec-form-shell { border-radius:24px; border:1px solid rgba(226,232,240,.96); background:rgba(255,255,255,.94); box-shadow:0 18px 45px rgba(15,23,42,.08); overflow:hidden; }
  .sec-form-hero { padding:1.35rem 1.45rem; background:radial-gradient(circle at top left, rgba(13,110,253,.14), transparent 34%), linear-gradient(135deg,#fff,#f8fbff); border-bottom:1px solid #edf2f7; }
  .sec-form-hero-row { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
  .sec-form-title-wrap { display:flex; align-items:flex-start; gap:.85rem; }
  .sec-form-icon { width:54px; height:54px; border-radius:18px; display:grid; place-items:center; background:linear-gradient(135deg,#0d6efd,#178bff); color:#fff; font-size:1.45rem; box-shadow:0 14px 28px rgba(13,110,253,.25); }
  .sec-form-title { margin:0; font-size:1.55rem; font-weight:900; letter-spacing:-.045em; color:#0f172a; }
  .sec-form-subtitle { margin:.25rem 0 0; color:#64748b; font-weight:650; }
  .sec-form-body { padding:1.35rem; }
  .sec-form-grid { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(280px,.75fr); gap:1rem; align-items:start; }
  .sec-card { border-radius:22px; border:1px solid #e2e8f0; background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.05); overflow:hidden; }
  .sec-card-head { padding:1rem 1.15rem; border-bottom:1px solid #edf2f7; display:flex; justify-content:space-between; align-items:center; gap:.75rem; }
  .sec-card-title { margin:0; font-size:1.02rem; font-weight:900; color:#0f172a; display:flex; gap:.5rem; align-items:center; }
  .sec-card-title i { color:#0d6efd; }
  .sec-card-body { padding:1.15rem; }
  .form-section { margin-bottom:1.05rem; }
  .form-section:last-child { margin-bottom:0; }
  .section-label { display:flex; align-items:center; gap:.45rem; margin-bottom:.75rem; color:#0f172a; font-size:.9rem; font-weight:900; }
  .section-label i { color:#0d6efd; }
  .form-label { color:#334155; font-size:.82rem; font-weight:850; margin-bottom:.4rem; }
  .form-control,.form-select { border-radius:14px!important; border-color:#dbe3ef!important; padding:.68rem .8rem!important; color:#0f172a; font-size:.9rem; font-weight:650; box-shadow:none!important; }
  .form-control:focus,.form-select:focus { border-color:rgba(13,110,253,.55)!important; box-shadow:0 0 0 .2rem rgba(13,110,253,.1)!important; }
  .field-help,.form-text { margin-top:.38rem; color:#64748b; font-size:.76rem; font-weight:650; }
  .field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.85rem; }
  .schedule-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.85rem; }
  .soft-box { padding:.9rem; border-radius:17px; border:1px dashed rgba(13,110,253,.32); background:linear-gradient(135deg,rgba(13,110,253,.055),rgba(255,255,255,.94)); }
  .side-stack { display:grid; gap:1rem; }
  .summary-card { padding:1rem; border-radius:22px; border:1px solid #e2e8f0; background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.05); }
  .summary-icon { width:58px; height:58px; border-radius:18px; display:grid; place-items:center; background:#eff6ff; color:#0d6efd; font-size:1.55rem; margin-bottom:.75rem; }
  .summary-title { margin:0; color:#0f172a; font-weight:900; letter-spacing:-.025em; }
  .summary-text { color:#64748b; font-size:.84rem; font-weight:650; margin:.35rem 0 0; }
  .summary-list { margin:1rem 0 0; padding:0; list-style:none; display:grid; gap:.55rem; }
  .summary-list li { display:flex; gap:.5rem; color:#475569; font-weight:650; font-size:.84rem; }
  .summary-list i { color:#0d6efd; }
  .sec-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
  .sec-actions .btn { border-radius:12px; font-weight:900; }
  @media(max-width:1100px){.sec-form-grid{grid-template-columns:1fr}.schedule-grid{grid-template-columns:1fr 1fr}}
  @media(max-width:768px){.sec-form-page{width:100%}.sec-form-body,.sec-form-hero{padding:.9rem}.field-grid,.schedule-grid{grid-template-columns:1fr}.sec-actions,.sec-actions .btn{width:100%}}
  #timeSlot option[disabled] { text-decoration: line-through; color:#6c757d; font-style: italic; }
</style>
@endpush

@section('content')
@php
  $secIndexUrl = Route::has('secretary.appointments.index') ? route('secretary.appointments.index') : url('/secretary/dashboard');
  $registerPatientUrl = Route::has('secretary.patients.create') ? route('secretary.patients.create') : url('/secretary/dashboard');
@endphp
<div class="sec-form-page">
  <div class="sec-form-shell">
    <div class="sec-form-hero">
      <div class="sec-form-hero-row">
        <div class="sec-form-title-wrap">
          <div class="sec-form-icon"><i class="bi bi-calendar-plus"></i></div>
          <div><h1 class="sec-form-title">Create Appointment</h1><p class="sec-form-subtitle">Select the patient, service, doctor, and available time slot.</p></div>
        </div>
        <a href="{{ $secIndexUrl }}" class="btn btn-outline-secondary rounded-4 fw-bold"><i class="bi bi-arrow-left me-2"></i>Back</a>
      </div>
    </div>

    <div class="sec-form-body">
      @include('partials.alerts')
      <div class="sec-form-grid">
        <main class="sec-card">
          <div class="sec-card-head"><h2 class="sec-card-title"><i class="bi bi-clipboard2-pulse"></i>Appointment Details</h2></div>
          <form method="POST" action="{{ route('secretary.appointments.store') }}" id="appointmentForm" data-active-clinic="{{ $activeClinicId ?? 0 }}">
            @csrf
            <div class="sec-card-body">
              <div class="form-section">
                <div class="section-label"><i class="bi bi-person-vcard"></i>Patient</div>
                <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                  <label for="patient_id" class="form-label mb-0">Patient name and email</label>
                  <a href="{{ $registerPatientUrl }}" class="btn btn-sm btn-outline-primary rounded-4 fw-bold"><i class="bi bi-person-plus me-1"></i>Register Patient</a>
                </div>
                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" data-old-value="{{ old('patient_id') }}" required><option value="" disabled selected hidden>Patient name and email</option></select>
                @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div id="activeClinicSummary" data-active-clinic="{{ $activeClinicId ?? 0 }}" hidden></div>

              <div class="form-section">
                <div class="section-label"><i class="bi bi-hospital"></i>Service and Doctor</div>
                <div class="field-grid">
                  <div><label for="service_id" class="form-label">Service</label><select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required><option value="">Select Service</option></select>@error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                  <div><label for="doctor_id" class="form-label">Doctor</label><select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required><option value="">Select Doctor</option></select>@error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
              </div>

              <div class="form-section">
                <div class="section-label"><i class="bi bi-clock-history"></i>Schedule</div>
                <div class="schedule-grid">
                  <div><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" id="appointment_date" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date') }}" min="{{ date('Y-m-d') }}" required>@error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                  <div><label class="form-label">Day</label><input type="text" id="dayDisplay" class="form-control" placeholder="—" readonly></div>
                  <div><label class="form-label">Available Time Slot <span class="text-danger">*</span></label><select id="timeSlot" name="appointment_time" class="form-select @error('appointment_time') is-invalid @enderror" @error('appointment_time') data-slot-error="1" @enderror required><option value="">Select doctor & date first</option></select>@error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="soft-box mt-3" id="doctorScheduleWrap" style="display:none;"><label class="form-label"><i class="bi bi-list-check me-1"></i>Doctor Availability</label><div class="small" id="doctorScheduleInfo"></div></div>
              </div>

              <div class="sec-actions"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Create Appointment</button><a href="{{ $secIndexUrl }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a></div>
            </div>
          </form>
        </main>

        <aside class="side-stack">
          <section class="summary-card"><div class="summary-icon"><i class="bi bi-lightbulb"></i></div><h3 class="summary-title">Before creating</h3><p class="summary-text">Make sure the selected doctor is available for the chosen date and time.</p><ul class="summary-list"><li><i class="bi bi-check-circle"></i>Pick a patient registered under the active clinic.</li><li><i class="bi bi-check-circle"></i>Select service first to filter doctors.</li><li><i class="bi bi-check-circle"></i>Only available slots can be submitted.</li></ul></section>
        </aside>
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const doctorSelect = document.getElementById('doctor_id');
    const patientSelect = document.getElementById('patient_id');
    const dateInput     = document.getElementById('appointment_date');
    const dayInput      = document.getElementById('dayDisplay');
    const slotSelect    = document.getElementById('timeSlot');
    const scheduleWrap  = document.getElementById('doctorScheduleWrap');
    const scheduleInfo  = document.getElementById('doctorScheduleInfo');
    const clinicSummary = document.getElementById('activeClinicSummary');
    const form = document.getElementById('appointmentForm');
    let activeClinicId = Number(form?.dataset.activeClinic || clinicSummary?.dataset.activeClinic || 0);
    const clinicsRaw = @json($clinics);
    const clinicPatients = @json($clinicPatients);
    const clinicMap = {};
    clinicsRaw.forEach(c => {
        clinicMap[c.id] = {
            services: (c.services || []).map(s => ({ id: s.id, name: s.name })),
            doctors: (c.doctors || []).map(d => ({ id: d.id, name: d.name, services: (d.services || []).map(s => s.id) }))
        };
    });
    const submitBtn = form.querySelector('button[type="submit"]');

    function resetSelect(el, placeholder, opts = {}) {
        if (!el) { return; }
        el.innerHTML = '';
        const option = new Option(
            placeholder,
            Object.prototype.hasOwnProperty.call(opts, 'value') ? opts.value : ''
        );
        if (opts.disabled) option.disabled = true;
        if (opts.selected) option.selected = true;
        if (opts.hidden) option.hidden = true;
        el.add(option);
    }

    function handleMissingClinicContext() {
        const message = 'Select an active clinic from the switcher above to load options.';
        resetSelect(patientSelect, message);
        resetSelect(serviceSelect, message);
        resetSelect(doctorSelect, message);
        resetSelect(slotSelect, message);
        [patientSelect, serviceSelect, doctorSelect, slotSelect].forEach(el => { if (el) { el.disabled = true; } });
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.title = message;
        }
    }

    if (!activeClinicId || !clinicMap[activeClinicId]) {
        handleMissingClinicContext();
        return;
    }

    function populatePatients() {
        const list = clinicPatients[activeClinicId] || [];
        if (!list.length) {
            resetSelect(patientSelect, 'No patients registered for this clinic yet', { disabled: true, selected: true });
            patientSelect.disabled = true;
            return;
        }
        resetSelect(patientSelect, 'Patient name and email', { disabled: true, selected: true, hidden: true });
        patientSelect.disabled = false;
        list.forEach(p => {
            const label = p.email ? `${p.name} — ${p.email}` : p.name;
            patientSelect.add(new Option(label, p.id));
        });
        const previous = patientSelect.dataset.oldValue;
        if (previous && [...patientSelect.options].some(o => o.value === previous)) {
            patientSelect.value = previous;
            patientSelect.dataset.oldValue = '';
        }
    }

    function populateServices() {
        resetSelect(serviceSelect, 'Select Service');
        resetSelect(doctorSelect, 'Select Doctor');
        const list = clinicMap[activeClinicId]?.services || [];
        list.forEach(s => serviceSelect.add(new Option(s.name, s.id)));
    }

    function populateDoctors(serviceId) {
        resetSelect(doctorSelect, 'Select Doctor');
        const docs = clinicMap[activeClinicId]?.doctors || [];
        const filtered = serviceId ? docs.filter(d => (d.services || []).includes(Number(serviceId))) : docs;
        filtered.forEach(d => doctorSelect.add(new Option('Dr. ' + d.name, d.id)));
    }

    function resetAvailability() {
        dayInput.value = '';
        scheduleWrap.style.display = 'none';
        scheduleInfo.innerHTML = '';
        resetSelect(slotSelect, 'Select doctor & date first');
    }

    async function fetchAvailability() {
        const cid = activeClinicId;
        const did = doctorSelect.value;
        const dateVal = dateInput.value;
        if (!cid || !did || !dateVal) {
            resetAvailability();
            return;
        }
        resetSelect(slotSelect, 'Loading...');
        try {
            const params = new URLSearchParams({ clinic_id: cid, doctor_id: did, date: dateVal, service_id: serviceSelect.value || '' });
            const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('fail');
            const data = await res.json();
            dayInput.value = data.weekday || '';
            scheduleWrap.style.display = 'block';
            scheduleInfo.innerHTML = renderSchedule(data);
            resetSelect(slotSelect, slotPlaceholder(data.slots));
            (data.slots || []).forEach(s => {
                const opt = new Option(s.display + (s.available ? '' : (s.expired ? ' - PAST' : ' - BOOKED')), s.time);
                if (!s.available) opt.disabled = true;
                slotSelect.add(opt);
            });
            @if(old('appointment_time'))
                if ([...slotSelect.options].some(o => o.value === "{{ old('appointment_time') }}")) {
                    slotSelect.value = "{{ old('appointment_time') }}";
                }
            @endif
        } catch (err) {
            resetAvailability();
            resetSelect(slotSelect, 'Error loading availability');
        }
    }

    function renderSchedule(data) {
        if (data.message) return `<span class="text-muted">${data.message}</span>`;
        const slots = data.slots || [];
        const availableCount = slots.filter(s => s.available).length;
        const expiredCount = slots.filter(s => s.expired).length;
        const bookedCount = slots.filter(s => s.occupied && !s.expired).length;
        let html = `<div><strong>Date:</strong> ${data.date} (${data.weekday})</div>`;
        if (data.schedule?.length) {
            html += '<div class="mt-1"><strong>Schedule Blocks:</strong> ' + data.schedule.map(r => `${to12(r.start)}–${to12(r.end)}`).join(', ') + '</div>';
        }
        html += `<div class="mt-1"><strong>Slot Length:</strong> ${data.slot_minutes} minutes</div>`;
        html += `<div class="mt-1"><strong>Available Slots:</strong> ${availableCount}</div>`;
        if (slots.length && availableCount === 0) {
            html += `<div class="mt-1 text-muted small">${
                expiredCount === slots.length
                    ? 'All generated slots for this date are already past. Try the next matching schedule date.'
                    : `${bookedCount} slot${bookedCount === 1 ? '' : 's'} booked and ${expiredCount} past.`
            }</div>`;
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

    function to12(t) {
        if (!/^\d{2}:\d{2}$/.test(t)) return t;
        const [h, m] = t.split(':').map(Number);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const hour = ((h + 11) % 12) + 1;
        return `${hour}:${m.toString().padStart(2, '0')} ${ampm}`;
    }

    if (slotSelect.dataset.slotError) {
        slotSelect.classList.remove('is-invalid');
        const fb = slotSelect.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.remove();
        setTimeout(fetchAvailability, 30);
    }

    serviceSelect.addEventListener('change', e => {
        populateDoctors(e.target.value || null);
        fetchAvailability();
    });
    doctorSelect.addEventListener('change', fetchAvailability);
    dateInput.addEventListener('change', fetchAvailability);

    const formSubmitHandler = async e => {
        const chosen = slotSelect.value;
        if (!chosen) { return; }
        e.preventDefault();
        submitBtn.disabled = true;
        const original = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
        try {
            const params = new URLSearchParams({ clinic_id: activeClinicId, doctor_id: doctorSelect.value, date: dateInput.value, service_id: serviceSelect.value || '' });
            const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            const data = await res.json();
            const stillFree = (data.slots || []).some(s => s.available && s.time === chosen);
            if (!stillFree) {
                resetSelect(slotSelect, slotPlaceholder(data.slots));
                (data.slots || []).forEach(s => {
                    const opt = new Option(s.display + (s.available ? '' : (s.expired ? ' - PAST' : ' - BOOKED')), s.time);
                    if (!s.available) opt.disabled = true;
                    slotSelect.add(opt);
                });
                slotSelect.value = '';
                submitBtn.disabled = false;
                submitBtn.innerHTML = original;
                return;
            }
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
            form.removeEventListener('submit', formSubmitHandler);
            form.submit();
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        }
    };

    form.addEventListener('submit', formSubmitHandler);

    setInterval(() => {
        if (activeClinicId && doctorSelect.value && dateInput.value) {
            fetchAvailability();
        }
    }, 60000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && activeClinicId && doctorSelect.value && dateInput.value) {
            fetchAvailability();
        }
    });

    (function init(){
        populatePatients();
        populateServices();
        @if(old('service_id'))
            serviceSelect.value = "{{ old('service_id') }}";
        @endif
        populateDoctors(serviceSelect.value || null);
        @if(old('doctor_id'))
            doctorSelect.value = "{{ old('doctor_id') }}";
        @endif
        if (doctorSelect.value && dateInput.value) {
            fetchAvailability();
        }
    })();
});
</script>
@endpush
@push('styles')
<style>
    #timeSlot option[disabled] { text-decoration: line-through; color:#6c757d; font-style: italic; }
</style>
@endpush
@endsection
