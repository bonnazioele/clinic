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

                    <form method="POST" action="{{ route('secretary.appointments.store') }}" id="appointmentForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Patient
                            </label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Select Patient</option>
                                @foreach(\App\Models\User::where('is_doctor', false)->where('is_admin', false)->where('is_secretary', false)->get() as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Clinic Selection -->
                        <div class="mb-3">
                            <label for="clinic_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Clinic
                            </label>
                            <select name="clinic_id" id="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
                                <option value="">Select Clinic</option>
                                @foreach($clinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->name }} - {{ $clinic->branch_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('clinic_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Service Selection -->
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

                        <!-- Doctor Selection -->
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

                                                <!-- Date / Day / Dynamic Time Slot -->
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

                        <!-- Notes -->
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

                        <!-- Medical Document (Optional) -->
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

                        <!-- Submit Buttons -->
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
    const clinicSelect = document.getElementById('clinic_id');
    const serviceSelect = document.getElementById('service_id');
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput     = document.getElementById('appointment_date');
    const dayInput      = document.getElementById('dayDisplay');
    const slotSelect    = document.getElementById('timeSlot');
    const scheduleWrap  = document.getElementById('doctorScheduleWrap');
    const scheduleInfo  = document.getElementById('doctorScheduleInfo');
    // Only secretary-assigned clinics supplied from controller
        const clinicsRaw = @json($clinics);
        // Normalize into a map: id -> {services:[{id,name}], doctors:[{id,name,services:[ids]}]}
        const clinicMap = {};
        clinicsRaw.forEach(c => {
            clinicMap[c.id] = {
                services: (c.services||[]).map(s => ({ id: s.id, name: s.name })),
                doctors: (c.doctors||[]).map(d => ({ id: d.id, name: d.name, services: (d.services||[]).map(s=>s.id) }))
            };
        });
        console.debug('Secretary clinicMap', clinicMap);

        function resetSelect(el, placeholder){ el.innerHTML=''; el.add(new Option(placeholder,'')); }
            function populateServices(cid){
                resetSelect(serviceSelect,'Select Service');
                resetSelect(doctorSelect,'Select Doctor');
                const list = clinicMap[cid]?.services || [];
                list.forEach(s=> serviceSelect.add(new Option(s.name, s.id)));
            }
                function populateDoctors(cid, sid){
                    resetSelect(doctorSelect,'Select Doctor');
                    const docs = clinicMap[cid]?.doctors || [];
                    if(!sid){ // show all doctors if no specific service yet
                        docs.forEach(d=> doctorSelect.add(new Option('Dr. '+d.name, d.id)));
                        return;
                    }
                    docs.filter(d => (d.services||[]).includes(Number(sid))).forEach(d=> doctorSelect.add(new Option('Dr. '+d.name, d.id)));
                }
        clinicSelect.addEventListener('change', e=>{ const cid=e.target.value; if(!cid){ resetSelect(serviceSelect,'Select Service'); resetSelect(doctorSelect,'Select Doctor'); return;} populateServices(cid); populateDoctors(cid, null); fetchAvailability(); });
        serviceSelect.addEventListener('change', e=>{ if(!clinicSelect.value){ resetSelect(doctorSelect,'Select Doctor'); return;} populateDoctors(clinicSelect.value, e.target.value || null); fetchAvailability(); });
        doctorSelect.addEventListener('change', fetchAvailability);
        dateInput.addEventListener('change', fetchAvailability);

    function resetAvailability(){ dayInput.value=''; scheduleWrap.style.display='none'; scheduleInfo.innerHTML=''; resetSelect(slotSelect,'Select doctor & date first'); }
        async function fetchAvailability(){
            const cid=clinicSelect.value, did=doctorSelect.value, dateVal=dateInput.value; if(!cid||!did||!dateVal){ resetAvailability(); return; }
            resetSelect(slotSelect,'Loading...');
            try {
                const params = new URLSearchParams({ clinic_id: cid, doctor_id: did, date: dateVal, service_id: serviceSelect.value||'' });
                const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers: { 'Accept':'application/json' } });
                if(!res.ok) throw new Error('fail');
                const data = await res.json();
                dayInput.value = data.weekday || '';
                scheduleWrap.style.display='block';
                scheduleInfo.innerHTML = renderSchedule(data);
                // Build dropdown only
                resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
                (data.slots||[]).forEach(s => { const opt = new Option(s.display + (s.available?'':' – BOOKED'), s.time); if(!s.available) opt.disabled=true; slotSelect.add(opt); });
                @if(old('appointment_time'))
                    if ([...slotSelect.options].some(o=>o.value==="{{ old('appointment_time') }}")) { slotSelect.value = "{{ old('appointment_time') }}"; }
                @endif
            } catch(err){ resetAvailability(); resetSelect(slotSelect,'Error loading availability'); }
        }
        function renderSchedule(data){ if(data.message) return `<span class="text-muted">${data.message}</span>`; let html=`<div><strong>Date:</strong> ${data.date} (${data.weekday})</div>`; if(data.schedule?.length){ html += '<div class="mt-1"><strong>Schedule Blocks:</strong> ' + data.schedule.map(r=>`${to12(r.start)}–${to12(r.end)}`).join(', ') + '</div>'; } html += `<div class="mt-1"><strong>Slot Length:</strong> ${data.slot_minutes} minutes</div>`; const count=(data.slots||[]).filter(s=>s.available).length; html += `<div class="mt-1"><strong>Available Slots:</strong> ${count}</div>`; return html; }
        function to12(t){ if(!/^\d{2}:\d{2}$/.test(t)) return t; const [h,m]=t.split(':').map(Number); const ampm=h>=12?'PM':'AM'; const hour=((h+11)%12)+1; return `${hour}:${m.toString().padStart(2,'0')} ${ampm}`; }
        if(slotSelect.dataset.slotError){ slotSelect.classList.remove('is-invalid'); const fb=slotSelect.parentElement.querySelector('.invalid-feedback'); if(fb) fb.remove(); setTimeout(fetchAvailability,30); }
        const form = document.getElementById('appointmentForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        form.addEventListener('submit', async e => {
            const chosen = slotSelect.value; if(!chosen) return; e.preventDefault(); submitBtn.disabled=true; const original = submitBtn.innerHTML; submitBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
            try {
                const params = new URLSearchParams({ clinic_id: clinicSelect.value, doctor_id: doctorSelect.value, date: dateInput.value, service_id: serviceSelect.value||'' });
                const res= await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers:{'Accept':'application/json'} });
                if(!res.ok) throw new Error();
                const data= await res.json();
                const stillFree = (data.slots||[]).some(s=>s.available && s.time===chosen);
                if(!stillFree){
                    resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
                    (data.slots||[]).forEach(s=>{ const opt=new Option(s.display + (s.available?'':' – BOOKED'), s.time); if(!s.available) opt.disabled=true; slotSelect.add(opt); });
                    slotSelect.value='';
                    submitBtn.disabled=false; submitBtn.innerHTML=original; return; }
                submitBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                form.submit();
            } catch(err) { submitBtn.disabled=false; submitBtn.innerHTML=original; }
        });
        // passive refresh
        setInterval(()=>{ if(clinicSelect.value && doctorSelect.value && dateInput.value){ fetchAvailability(); } }, 60000);
        document.addEventListener('visibilitychange', ()=>{ if(!document.hidden && clinicSelect.value && doctorSelect.value && dateInput.value){ fetchAvailability(); }});

        // Initial population if old input or single clinic pre-selected
        (function init(){
            const pre = clinicSelect.value || (clinicSelect.options.length === 2 ? clinicSelect.options[1].value : '');
            if(pre && !clinicSelect.value){ clinicSelect.value = pre; }
            if(clinicSelect.value){
                populateServices(clinicSelect.value);
                // If old('service_id')
                @if(old('service_id'))
                    serviceSelect.value = "{{ old('service_id') }}";
                @endif
                populateDoctors(clinicSelect.value, serviceSelect.value || null);
                @if(old('doctor_id'))
                    doctorSelect.value = "{{ old('doctor_id') }}";
                @endif
            }
            if(clinicSelect.value && doctorSelect.value && dateInput.value){ fetchAvailability(); }
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
