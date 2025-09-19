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
          <label class="form-label fw-semibold"><i class="bi bi-clock-history me-1"></i>Time Slot <span class="text-danger">*</span></label>
          <select id="timeSlot" name="appointment_time" class="form-select @error('appointment_time') is-invalid @enderror" required>
            <option value="">Select doctor & date first</option>
          </select>
          @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <button class="btn btn-primary"><i class="bi bi-calendar-check-fill me-2"></i>Book Now</button>
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
      resetSelect(slotSelect, data.slots.length ? 'Select a time slot' : 'No available slots');
      data.slots.forEach(s => {
        const label = `${s.display}${s.available ? '' : ' (booked)'}`;
        const opt = new Option(label, s.time, false, false);
        if (!s.available) opt.disabled = true;
        slotSelect.add(opt);
      });
      // Restore old() selection if applicable
      @if(old('appointment_time'))
        slotSelect.value = "{{ old('appointment_time') }}";
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
    setTimeout(fetchAvailability, 100);
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
});
</script>
@endpush
