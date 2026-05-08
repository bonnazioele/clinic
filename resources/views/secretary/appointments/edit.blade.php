@extends('layouts.app')

@section('title', 'Manage Appointment #'.$appointment->id)

@push('styles')
<style>
  .sec-edit-page { width: 96%; max-width: none; margin: 0 auto; }
  .sec-edit-shell { border-radius:24px; border:1px solid rgba(226,232,240,.96); background:rgba(255,255,255,.94); box-shadow:0 18px 45px rgba(15,23,42,.08); overflow:hidden; }
  .sec-edit-hero { padding:1.35rem 1.45rem; background:radial-gradient(circle at top left, rgba(13,110,253,.14), transparent 34%), linear-gradient(135deg,#fff,#f8fbff); border-bottom:1px solid #edf2f7; }
  .sec-edit-hero-row { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
  .sec-edit-title-wrap { display:flex; align-items:flex-start; gap:.85rem; }
  .sec-edit-icon { width:54px; height:54px; border-radius:18px; display:grid; place-items:center; background:linear-gradient(135deg,#0d6efd,#178bff); color:#fff; font-size:1.45rem; box-shadow:0 14px 28px rgba(13,110,253,.25); }
  .sec-edit-title { margin:0; font-size:1.55rem; font-weight:900; letter-spacing:-.045em; color:#0f172a; }
  .sec-edit-subtitle { margin:.25rem 0 0; color:#64748b; font-weight:650; }
  .sec-edit-body { padding:1.35rem; }
  .sec-edit-grid { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(280px,.75fr); gap:1rem; align-items:start; }
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
  .field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.85rem; }
  .side-stack { display:grid; gap:1rem; }
  .summary-card { padding:1rem; border-radius:22px; border:1px solid #e2e8f0; background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.05); }
  .summary-icon { width:58px; height:58px; border-radius:18px; display:grid; place-items:center; background:#eff6ff; color:#0d6efd; font-size:1.55rem; margin-bottom:.75rem; }
  .summary-title { margin:0; color:#0f172a; font-weight:900; letter-spacing:-.025em; }
  .summary-text { color:#64748b; font-size:.84rem; font-weight:650; margin:.35rem 0 0; }
  .summary-list { margin:1rem 0 0; padding:0; list-style:none; display:grid; gap:.55rem; }
  .summary-list li { padding:.75rem; border-radius:15px; border:1px solid #edf2f7; background:#f8fafc; }
  .summary-label { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
  .summary-value { color:#0f172a; font-size:.88rem; font-weight:900; line-height:1.3; }
  .sec-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
  .sec-actions .btn { border-radius:12px; font-weight:900; }
  @media(max-width:1100px){.sec-edit-grid{grid-template-columns:1fr}}
  @media(max-width:768px){.sec-edit-page{width:100%}.sec-edit-body,.sec-edit-hero{padding:.9rem}.field-grid{grid-template-columns:1fr}.sec-actions,.sec-actions .btn{width:100%}}
</style>
@endpush

@section('content')
@php
  $indexUrl = Route::has('secretary.appointments.index') ? route('secretary.appointments.index') : url('/secretary/dashboard');
@endphp
<div class="sec-edit-page">
  @include('partials.alerts')
  <div class="sec-edit-shell">
    <div class="sec-edit-hero">
      <div class="sec-edit-hero-row">
        <div class="sec-edit-title-wrap">
          <div class="sec-edit-icon"><i class="bi bi-pencil-square"></i></div>
          <div><h1 class="sec-edit-title">Manage Appointment #{{ $appointment->id }}</h1><p class="sec-edit-subtitle">Update the appointment clinic, service, doctor, schedule, and status.</p></div>
        </div>
        <a href="{{ $indexUrl }}" class="btn btn-outline-secondary rounded-4 fw-bold"><i class="bi bi-arrow-left me-2"></i>Back</a>
      </div>
    </div>
    <div class="sec-edit-body">
      <div class="sec-edit-grid">
        <main class="sec-card">
          <div class="sec-card-head"><h2 class="sec-card-title"><i class="bi bi-calendar-check"></i>Appointment Information</h2></div>
          <form method="POST" action="{{ route('secretary.appointments.update', $appointment) }}">
            @csrf
            @method('PATCH')
            <div class="sec-card-body">
              <div class="form-section"><div class="section-label"><i class="bi bi-hospital"></i>Clinic and Service</div><div class="field-grid"><div><label class="form-label">Clinic</label><select name="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror">@foreach($clinics as $c)<option value="{{ $c->id }}" @selected(old('clinic_id', $appointment->clinic_id) == $c->id)>{{ $c->name }}</option>@endforeach</select>@error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label">Service</label><select name="service_id" class="form-select @error('service_id') is-invalid @enderror">@foreach($services as $s)<option value="{{ $s->id }}" @selected(old('service_id', $appointment->service_id) == $s->id)>{{ $s->name }}</option>@endforeach</select>@error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>
              <div class="form-section"><div class="section-label"><i class="bi bi-person-badge"></i>Doctor</div><label class="form-label">Assign Doctor</label><select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror"><option value="">— Unassigned —</option>@foreach($doctors as $d)<option value="{{ $d->id }}" @selected(old('doctor_id', $appointment->doctor_id) == $d->id)>{{ $d->name }}</option>@endforeach</select>@error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
              <div class="form-section"><div class="section-label"><i class="bi bi-clock-history"></i>Schedule</div><div class="field-grid"><div><label class="form-label">Date</label><input type="date" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date', $appointment->appointment_date) }}">@error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label">Time</label><input type="time" name="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" value="{{ old('appointment_time', $appointment->appointment_time) }}">@error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>
              <div class="form-section"><div class="section-label"><i class="bi bi-info-circle"></i>Status</div><label class="form-label">Appointment Status</label><select name="status" class="form-select @error('status') is-invalid @enderror">@foreach(['scheduled','in_progress','completed','cancelled','no_show'] as $st)<option value="{{ $st }}" @selected(old('status', $appointment->status) == $st)>{{ ucwords(str_replace('_', ' ', $st)) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
              <div class="sec-actions"><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button><a href="{{ $indexUrl }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a></div>
            </div>
          </form>
        </main>
        <aside class="side-stack"><section class="summary-card"><div class="summary-icon"><i class="bi bi-calendar-event"></i></div><h3 class="summary-title">Current Details</h3><p class="summary-text">Review the current appointment before saving changes.</p><ul class="summary-list"><li><div class="summary-label">Patient</div><div class="summary-value">{{ $appointment->user->name ?? '—' }}</div></li><li><div class="summary-label">Clinic</div><div class="summary-value">{{ $appointment->clinic->name ?? '—' }}</div></li><li><div class="summary-label">Service</div><div class="summary-value">{{ $appointment->service->name ?? '—' }}</div></li><li><div class="summary-label">Status</div><div class="summary-value">{{ $appointment->status_label ?? ucwords(str_replace('_', ' ', $appointment->status)) }}</div></li></ul></section></aside>
      </div>
    </div>
  </div>
</div>
@endsection
