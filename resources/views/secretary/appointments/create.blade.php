@extends('layouts.app')

@section('title', 'Create Appointment')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card medical-card">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">
                        <i class="bi bi-calendar-plus me-2"></i>Create Appointment for Patient
                    </h2>
                </div>

                <div class="card-body">
                    @include('partials.alerts')

                      <form method="POST"
                          action="{{ route('secretary.appointments.store') }}"
                          id="appointmentForm"
                          data-active-clinic="{{ $activeClinicId ?? 0 }}"
                          enctype="multipart/form-data">
                        @csrf


                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="patient_id" class="form-label fw-semibold mb-0">
                                    <i class="bi bi-person-vcard me-1"></i>Patient
                                </label>
                                <a href="{{ route('secretary.patients.create') }}" class="btn btn-link btn-sm text-decoration-none">
                                    <i class="bi bi-person-plus me-1"></i>Register Patient
                                </a>
                            </div>
                            <select name="patient_id"
                                    id="patient_id"
                                    class="form-select @error('patient_id') is-invalid @enderror"
                                    data-old-value="{{ old('patient_id') }}"
                                    required>
                                <option value="" disabled selected hidden>Patient name and email</option>
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="activeClinicSummary" data-active-clinic="{{ $activeClinicId ?? 0 }}" hidden></div>


                        <div class="mb-3">
                            <label for="service_id" class="form-label fw-semibold">
                                <i class="bi bi-tools me-1"></i>Service
                            </label>
                            <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                                <option value="">Select Service</option>
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="doctor_id" class="form-label fw-semibold">
                                <i class="bi bi-person-badge me-1"></i>Doctor
                            </label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">Select Doctor</option>
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                                                <div class="row g-3 mb-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold"><i class="bi bi-calendar me-1"></i>Date <span class="text-danger">*</span></label>
                                                        <input type="date" id="appointment_date" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date') }}" min="{{ date('Y-m-d') }}" required>
                                                        @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold"><i class="bi bi-calendar-week me-1"></i>Day</label>
                                                        <input type="text" id="dayDisplay" class="form-control" placeholder="—" readonly>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold mb-0"><i class="bi bi-clock-history me-1"></i>Available Time Slot <span class="text-danger">*</span></label>
                                                        <select id="timeSlot" name="appointment_time" class="form-select @error('appointment_time') is-invalid @enderror" @error('appointment_time') data-slot-error="1" @enderror required>
                                                            <option value="">Select doctor & date first</option>
                                                        </select>
                                                        @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="mb-3" id="doctorScheduleWrap" style="display:none;">
                                                    <label class="form-label fw-semibold"><i class="bi bi-list-check me-1"></i>Doctor Availability</label>
                                                    <div class="border rounded p-2 small" id="doctorScheduleInfo"></div>
                                                </div>


                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-sticky me-1"></i>Notes (Optional)
                            </label>
                            <textarea name="notes"
                                      id="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes about the appointment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="medical_document" class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-medical me-1"></i>Medical Document (Optional)
                            </label>
                            <input type="file" name="medical_document" id="medical_document" class="form-control @error('medical_document') is-invalid @enderror" accept=".pdf,image/*">
                            @error('medical_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Attach a past prescription, lab result, or relevant file (PDF or image, max 5MB).</div>
                        </div>


                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-2"></i>Create Appointment
                            </button>
                            <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
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
            resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
            (data.slots || []).forEach(s => {
                const opt = new Option(s.display + (s.available ? '' : ' – BOOKED'), s.time);
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
        let html = `<div><strong>Date:</strong> ${data.date} (${data.weekday})</div>`;
        if (data.schedule?.length) {
            html += '<div class="mt-1"><strong>Schedule Blocks:</strong> ' + data.schedule.map(r => `${to12(r.start)}–${to12(r.end)}`).join(', ') + '</div>';
        }
        html += `<div class="mt-1"><strong>Slot Length:</strong> ${data.slot_minutes} minutes</div>`;
        const count = (data.slots || []).filter(s => s.available).length;
        html += `<div class="mt-1"><strong>Available Slots:</strong> ${count}</div>`;
        return html;
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
                resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
                (data.slots || []).forEach(s => {
                    const opt = new Option(s.display + (s.available ? '' : ' – BOOKED'), s.time);
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
