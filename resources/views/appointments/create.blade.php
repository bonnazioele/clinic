@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="container py-4">
  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-calendar-plus me-2"></i>
      <h5 class="mb-0">Book Appointment</h5>
    </div>
    <div class="card-body">

    @include('partials.alerts')

  <form method="POST" action="{{ route('appointments.store') }}" enctype="multipart/form-data">
      @csrf

      {{-- Clinic --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">
          <i class="bi bi-building me-1"></i>Clinic <span class="text-danger">*</span>
        </label>
        <select id="clinic" name="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
          <option value="">Select a clinic</option>
          @foreach($clinics as $c)
            <option value="{{ $c->id }}"
              {{ old('clinic_id') == $c->id || ($clinics->count() == 1) ? 'selected' : '' }}>
              {{ $c->name }}
            </option>
          @endforeach
        </select>
        @error('clinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Service --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">
          <i class="bi bi-scissors me-1"></i>Service <span class="text-danger">*</span>
        </label>
        <select id="service" name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
          <option value="">First select a clinic</option>
        </select>
        @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Doctor --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">
          <i class="bi bi-person-badge me-1"></i>Doctor <span class="text-danger">*</span>
        </label>
        <select id="doctor" name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
          <option value="">First select a service</option>
        </select>
        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Date, Day & Time (dynamic availability) --}}
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold"><i class="bi bi-calendar me-1"></i>Date <span class="text-danger">*</span></label>
          <input type="date" id="apptDate" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date') }}" min="{{ date('Y-m-d') }}" required>
          @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold"><i class="bi bi-calendar-week me-1"></i>Day</label>
          <input type="text" id="apptDay" class="form-control" value="" placeholder="—" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold mb-0"><i class="bi bi-clock-history me-1"></i>Available Time Slot <span class="text-danger">*</span></label>
          <!-- Hidden select kept for form submission & server-side validation compatibility -->
          <select id="timeSlot" name="appointment_time" class="form-select d-none @error('appointment_time') is-invalid @enderror" @error('appointment_time') data-slot-error="1" @enderror required>
            <option value="">Select doctor & date first</option>
          </select>
          <div id="slotGridPlaceholder" class="text-muted small">Select doctor & date first</div>
          <div id="slotGrid" class="slot-grid mt-2" style="display:none;"></div>
          @error('appointment_time') <div class="invalid-feedback d-none">{{ $message }}</div> @enderror
        </div>
      </div>
      <div class="mb-3" id="doctorScheduleWrap" style="display:none;">
        <label class="form-label fw-semibold"><i class="bi bi-list-check me-1"></i>Doctor Availability</label>
        <div class="border rounded p-2 small" id="doctorScheduleInfo"></div>
      </div>

      {{-- Optional Medical Document --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">
          <i class="bi bi-file-earmark-medical me-1"></i>Medical Document (optional)
        </label>
        <input type="file" name="medical_document" class="form-control @error('medical_document') is-invalid @enderror" accept=".pdf,image/*">
        @error('medical_document') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Attach a past prescription, lab result, or relevant file (PDF or image, max 5MB).</div>
      </div>

      <div class="d-flex gap-2 justify-content-end">
        <button id="bookSubmit" class="btn btn-primary"><i class="bi bi-calendar-check-fill me-2"></i>Book Now</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
      </div>
    </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const clinicSelect  = document.getElementById('clinic');
  const serviceSelect = document.getElementById('service');
  const doctorSelect  = document.getElementById('doctor');
  const dateInput     = document.getElementById('apptDate');
  const dayInput      = document.getElementById('apptDay');
  const slotSelect    = document.getElementById('timeSlot');
  const slotGrid      = document.getElementById('slotGrid');
  const slotGridPh    = document.getElementById('slotGridPlaceholder');
  const scheduleWrap  = document.getElementById('doctorScheduleWrap');
  const scheduleInfo  = document.getElementById('doctorScheduleInfo');

  // Build a compact JSON blob in PHP to avoid empty maps when relations aren't loaded
  const clinicMap = {};
  @foreach($clinics as $c)
    clinicMap["{{ $c->id }}"] = {
      services: @json($c->services->map(function($s){ return ['id'=>$s->id,'name'=>$s->name]; })->values()),
      doctors:  @json($c->doctors->map(function($d){
                    return ['id'=>$d->id,'name'=>$d->name,'services'=>$d->services->pluck('id')->values()];
                  })->values())
    };
  @endforeach

  function resetSelect(el, placeholder) {
    el.innerHTML = '';
    el.add(new Option(placeholder, ''));
  }

  function populateServices(clinicId) {
    resetSelect(serviceSelect, 'Select a service');
    resetSelect(doctorSelect, 'First select a service');

    const list = (clinicMap[clinicId] && clinicMap[clinicId].services) || [];
    list.forEach(s => serviceSelect.add(new Option(s.name, s.id)));
  }

  function populateDoctors(clinicId, serviceId) {
    resetSelect(doctorSelect, 'Select a doctor');

    const docs = (clinicMap[clinicId] && clinicMap[clinicId].doctors) || [];
    docs
      .filter(d => (d.services || []).includes(Number(serviceId)))
  .forEach(d => doctorSelect.add(new Option(`Dr. ${d.name}`, d.id)));
  }

  clinicSelect.addEventListener('change', e => {
    const cid = e.target.value;
    if (!cid) { resetSelect(serviceSelect, 'First select a clinic'); resetSelect(doctorSelect, 'First select a service'); return; }
    populateServices(cid);
  });

  serviceSelect.addEventListener('change', e => {
    const cid = clinicSelect.value;
    const sid = e.target.value;
    if (!cid || !sid) { resetSelect(doctorSelect, 'First select a service'); return; }
    populateDoctors(cid, sid);
    resetAvailability();
  });

  doctorSelect.addEventListener('change', () => {
    fetchAvailability();
  });
  dateInput.addEventListener('change', () => {
    fetchAvailability();
  });

  function resetAvailability() {
    dayInput.value = '';
    scheduleWrap.style.display = 'none';
    scheduleInfo.innerHTML = '';
    resetSelect(slotSelect, 'Select doctor & date first');
    slotGrid.innerHTML='';
    slotGrid.style.display='none';
    slotGridPh.style.display='block';
    slotSelect.value='';
  }

  async function fetchAvailability() {
    const clinicId = clinicSelect.value;
    const doctorId = doctorSelect.value;
    const dateVal  = dateInput.value;
    if (!clinicId || !doctorId || !dateVal) { resetAvailability(); return; }
    resetSelect(slotSelect, 'Loading...');
    try {
      const params = new URLSearchParams({ clinic_id: clinicId, doctor_id: doctorId, date: dateVal, service_id: serviceSelect.value || '' });
      const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers: { 'Accept':'application/json' } });
      if (!res.ok) throw new Error('Failed to load');
      const data = await res.json();
      dayInput.value = data.weekday || '';
      scheduleWrap.style.display = 'block';
      scheduleInfo.innerHTML = renderSchedule(data);
      // Build hidden select with all slots (even booked) for race re-check logic
      resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
      slotGrid.innerHTML='';
      if (data.slots.length) {
        slotGridPh.style.display='none';
        slotGrid.style.display='grid';
        slotGrid.style.gridTemplateColumns='repeat(auto-fill,minmax(90px,1fr))';
        slotGrid.style.gap='6px';
        data.slots.forEach(s => {
          // add to select (value set only if available to avoid accidental submission of booked time)
          const opt = new Option(s.display, s.time, false, false);
          if(!s.available) opt.disabled = true;
          slotSelect.add(opt);
          const btn = document.createElement('button');
          btn.type='button';
          btn.className = 'slot-btn btn btn-sm w-100 ' + (s.available ? 'btn-outline-primary' : 'btn-outline-secondary disabled taken');
          btn.textContent = s.display;
          btn.dataset.time = s.time;
          if(!s.available){ btn.setAttribute('aria-disabled','true'); }
          btn.addEventListener('click', () => {
            if(!s.available) return; // ignore booked
            // deselect previous
            slotGrid.querySelectorAll('.slot-btn.selected').forEach(el=> el.classList.remove('selected','btn-primary'));
            // mark selected
            btn.classList.add('selected','btn-primary');
            btn.classList.remove('btn-outline-primary');
            slotSelect.value = s.time; // ensure form submission
          });
          slotGrid.appendChild(btn);
        });
      } else {
        slotGridPh.textContent = 'No slots';
        slotGridPh.style.display='block';
        slotGrid.style.display='none';
      }
      // Removed slot meta display per request
      // Restore old() selection if applicable (only if slot still in list)
      @if(old('appointment_time'))
        if ([...slotSelect.options].some(o => o.value === "{{ old('appointment_time') }}")) {
          slotSelect.value = "{{ old('appointment_time') }}";
          // highlight on grid
          const btn = slotGrid.querySelector(`[data-time='{{ old('appointment_time') }}']`);
          if(btn && !btn.classList.contains('taken')) { btn.click(); }
        }
      @endif
    } catch (e) {
      resetAvailability();
      resetSelect(slotSelect, 'Error loading availability');
    }
  }

  function renderSchedule(data) {
    if (data.message) {
      return `<span class="text-muted">${data.message}</span>`;
    }
    let html = `<div><strong>Date:</strong> ${data.date} (${data.weekday})</div>`;
    if (data.schedule && data.schedule.length) {
      const blocks = data.schedule.map(r => `${to12(r.start)}–${to12(r.end)}`).join(', ');
      html += '<div class="mt-1"><strong>Schedule Blocks:</strong> ' + blocks + '</div>';
    }
    html += `<div class="mt-1"><strong>Slot Length:</strong> ${data.slot_minutes} minutes</div>`;
    const availableCount = (data.slots||[]).filter(s => s.available).length;
    html += `<div class="mt-1"><strong>Available Slots:</strong> ${availableCount}</div>`;
    return html;
  }

  function to12(t) {
    // expects HH:MM
    if(!/^\d{2}:\d{2}$/.test(t)) return t;
    const [h,m] = t.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = ((h + 11) % 12 + 1); // convert 0->12
    return `${hour}:${m.toString().padStart(2,'0')} ${ampm}`;
  }

  // Trigger availability load if old values exist
  @if(old('doctor_id') && old('appointment_date'))
    setTimeout(fetchAvailability, 120);
  @endif

  // Restore old() after validation OR auto-populate if a clinic is preselected
  @if(old('clinic_id'))
    populateServices("{{ old('clinic_id') }}");
    serviceSelect.value = "{{ old('service_id') }}";
    if (serviceSelect.value) populateDoctors("{{ old('clinic_id') }}", "{{ old('service_id') }}");
    doctorSelect.value = "{{ old('doctor_id') }}";
    // Availability restoration handled separately
  @else
    if (clinicSelect.value) populateServices(clinicSelect.value);
  @endif
  // If previous submission error flagged the slot, refresh immediately to drop booked slot
  if (slotSelect.dataset.slotError) {
    // Remove prior invalid styling & message; silently refresh to drop stale slot
    const fb = slotSelect.parentElement.querySelector('.invalid-feedback');
    slotSelect.classList.remove('is-invalid');
    if (fb) fb.remove();
    setTimeout(fetchAvailability, 30);
  }

  // Pre-submit recheck to avoid submitting a now-booked slot
  const form = slotSelect.closest('form');
  const submitBtn = document.getElementById('bookSubmit');
  form.addEventListener('submit', async (e) => {
    const chosen = slotSelect.value;
    if (!chosen) return; // let HTML5 required handle
    // quick re-fetch to ensure slot still free
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
    try {
      const params = new URLSearchParams({ clinic_id: clinicSelect.value, doctor_id: doctorSelect.value, date: dateInput.value, service_id: serviceSelect.value || '' });
      const res = await fetch(`{{ route('appointments.availability') }}?${params.toString()}`, { headers: { 'Accept':'application/json' } });
      if(!res.ok) throw new Error('Fetch failed');
      const data = await res.json();
      const stillAvailable = (data.slots||[]).some(s => s.available && s.time === chosen);
      if (!stillAvailable) {
        // silently rebuild grid
        resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No slots');
        slotGrid.innerHTML='';
        data.slots.forEach(s=>{
          const opt = new Option(s.display, s.time); if(!s.available) opt.disabled=true; slotSelect.add(opt);
          const btn = document.createElement('button'); btn.type='button'; btn.className='slot-btn btn btn-sm w-100 ' + (s.available? 'btn-outline-primary':'btn-outline-secondary disabled taken'); btn.textContent=s.display; btn.dataset.time=s.time; if(s.available){ btn.addEventListener('click',()=>{ slotGrid.querySelectorAll('.slot-btn.selected').forEach(el=>el.classList.remove('selected','btn-primary')); btn.classList.add('selected','btn-primary'); btn.classList.remove('btn-outline-primary'); slotSelect.value=s.time; }); } slotGrid.appendChild(btn);
        });
        slotSelect.value='';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-calendar-check-fill me-2"></i>Book Now';
        return; // no visible error message per request
      }
      // slot still free; proceed
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking...';
      form.submit();
    } catch(err) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-calendar-check-fill me-2"></i>Book Now';
    }
  });

  // Passive auto-sync every 60s while a doctor/date selected
  setInterval(() => {
    if (clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  }, 60000);

  // Refresh when returning to tab (visibility change)
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  });

  // Refresh on slot select focus if more than 30s since last population (simple heuristic using option count meta)
  let lastPopulateAt = Date.now();
  const originalFetch = fetchAvailability;
  fetchAvailability = async function() {
    await originalFetch();
    lastPopulateAt = Date.now();
  };
  slotSelect.addEventListener('focus', () => {
    if (Date.now() - lastPopulateAt > 30000 && clinicSelect.value && doctorSelect.value && dateInput.value) {
      fetchAvailability();
    }
  });
});
</script>
@endpush

@push('styles')
<style>
  .slot-grid .slot-btn { font-size: .75rem; line-height: 1.1rem; white-space: nowrap; }
  .slot-grid .slot-btn.taken { pointer-events: none; opacity: .55; text-decoration: line-through; }
  .slot-grid .slot-btn.selected { font-weight: 600; }
</style>
@endpush
