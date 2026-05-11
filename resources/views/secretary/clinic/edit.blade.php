@extends('layouts.app')

@section('title', 'Edit Clinic Settings')

@section('content')
@php
  $logoUrl = $clinic->logo ? asset('storage/' . $clinic->logo) : null;
  $coverUrl = $clinic->cover_image ? asset('storage/' . $clinic->cover_image) : null;
@endphp

<style>
  .settings-edit-page{width:min(96%,1180px);margin:0 auto;padding:1rem 0 2rem}.edit-hero{border-radius:26px;padding:1.4rem;color:#fff;background:radial-gradient(circle at 90% 20%,rgba(255,255,255,.18),transparent 18%),linear-gradient(135deg,#0d6efd,#1d4ed8);box-shadow:0 18px 45px rgba(37,99,235,.22);margin-bottom:1rem;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}.edit-title{margin:0;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:950;letter-spacing:-.05em}.edit-subtitle{margin:.25rem 0 0;opacity:.95;font-weight:650}.back-btn{border-radius:14px;font-weight:900}.settings-card{border:1px solid #e2e8f0;border-radius:24px;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.06);overflow:hidden;margin-bottom:1rem}.settings-card-body{padding:1.25rem}.section-title{margin:0 0 1rem;color:#0f172a;font-size:1.1rem;font-weight:950;display:flex;align-items:center;gap:.5rem}.section-title i{color:#0d6efd}.form-label{font-weight:900;color:#334155}.form-control,.form-select{border-radius:14px;border-color:#cbd5e1;min-height:46px;font-weight:700}.upload-preview{display:flex;align-items:center;gap:.75rem;margin-top:.7rem;border:1px solid #edf2f7;background:#f8fafc;border-radius:16px;padding:.65rem}.upload-preview img{width:76px;height:54px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0}.hours-grid{display:grid;gap:.8rem}.hour-card{border:1px solid #edf2f7;background:#f8fafc;border-radius:20px;padding:1rem}.hour-head{display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;margin-bottom:.85rem}.hour-day{font-weight:950;color:#0f172a;font-size:1rem}.hour-toggles{display:flex;gap:1rem;align-items:center;flex-wrap:wrap}.form-check-label{font-weight:850;color:#334155}.time-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}.closed-note{color:#64748b;font-weight:750;font-size:.88rem}.save-bar{position:sticky;bottom:0;background:rgba(248,250,252,.94);backdrop-filter:blur(10px);border:1px solid #e2e8f0;border-radius:20px;padding:1rem;display:flex;justify-content:flex-end;gap:.65rem;flex-wrap:wrap;box-shadow:0 -12px 30px rgba(15,23,42,.06)}.save-bar .btn{border-radius:14px;font-weight:900;padding:.72rem 1rem}.soft-note{color:#64748b;font-weight:650;line-height:1.55}@media(max-width:900px){.time-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.time-grid{grid-template-columns:1fr}.save-bar .btn{width:100%}}
</style>

<div class="settings-edit-page">
  @include('partials.alerts')

  <section class="edit-hero">
    <div>
      <h1 class="edit-title">Edit Clinic Settings</h1>
      <p class="edit-subtitle">Update clinic information and operational hours. Queue mode has been removed.</p>
    </div>

    <a href="{{ route('secretary.clinic.show', $clinic) }}" class="btn btn-light text-primary back-btn">
      <i class="bi bi-arrow-left me-1"></i>Back to Settings
    </a>
  </section>

  <form method="POST" action="{{ route('secretary.clinic.update', $clinic) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <section class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title"><i class="bi bi-info-circle"></i>Clinic Information</h2>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="name">Clinic Name</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $clinic->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <input type="text" class="form-control" value="{{ ucfirst($clinic->status ?? 'active') }}" disabled>
          </div>

          <div class="col-12">
            <label class="form-label" for="address">Address</label>
            <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $clinic->address) }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $clinic->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="logo">Clinic Logo</label>
            <input type="file" id="logo" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($logoUrl)
              <div class="upload-preview"><img src="{{ $logoUrl }}" alt="Current logo"><span class="soft-note">Current logo</span></div>
            @endif
          </div>

          <div class="col-md-6">
            <label class="form-label" for="cover_image">Cover Image</label>
            <input type="file" id="cover_image" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($coverUrl)
              <div class="upload-preview"><img src="{{ $coverUrl }}" alt="Current cover"><span class="soft-note">Current cover</span></div>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title"><i class="bi bi-clock-history"></i>Operational Hours</h2>
        <p class="soft-note mb-3">Set the clinic schedule per day. For 24-hour operation, check both Open and 24 Hours.</p>

        @error('hours')<div class="alert alert-danger">{{ $message }}</div>@enderror

        <div class="hours-grid">
          @foreach(\App\Models\ClinicOperationalHour::DAYS as $dayKey => $dayLabel)
            @php
              $hour = $hours[$dayKey] ?? null;
              $isOpen = old("hours.$dayKey.is_open", $hour?->is_open ? '1' : null);
              $is24 = old("hours.$dayKey.is_24_hours", $hour?->is_24_hours ? '1' : null);
              $openTime = old("hours.$dayKey.open_time", $hour?->open_time ? $hour->open_time->format('H:i') : '');
              $closeTime = old("hours.$dayKey.close_time", $hour?->close_time ? $hour->close_time->format('H:i') : '');
              $breakStart = old("hours.$dayKey.break_start", $hour?->break_start ? $hour->break_start->format('H:i') : '');
              $breakEnd = old("hours.$dayKey.break_end", $hour?->break_end ? $hour->break_end->format('H:i') : '');
            @endphp

            <div class="hour-card" data-day-card="{{ $dayKey }}">
              <div class="hour-head">
                <div class="hour-day">{{ $dayLabel }}</div>

                <div class="hour-toggles">
                  <div class="form-check form-switch">
                    <input class="form-check-input js-open-toggle" type="checkbox" role="switch" id="{{ $dayKey }}_open" name="hours[{{ $dayKey }}][is_open]" value="1" @checked($isOpen)>
                    <label class="form-check-label" for="{{ $dayKey }}_open">Open</label>
                  </div>

                  <div class="form-check form-switch">
                    <input class="form-check-input js-24-toggle" type="checkbox" role="switch" id="{{ $dayKey }}_24" name="hours[{{ $dayKey }}][is_24_hours]" value="1" @checked($is24)>
                    <label class="form-check-label" for="{{ $dayKey }}_24">24 Hours</label>
                  </div>
                </div>
              </div>

              <div class="time-grid js-time-grid">
                <div>
                  <label class="form-label" for="{{ $dayKey }}_open_time">Open Time</label>
                  <input type="time" id="{{ $dayKey }}_open_time" name="hours[{{ $dayKey }}][open_time]" class="form-control @error("hours.$dayKey.open_time") is-invalid @enderror" value="{{ $openTime }}">
                  @error("hours.$dayKey.open_time")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                  <label class="form-label" for="{{ $dayKey }}_close_time">Close Time</label>
                  <input type="time" id="{{ $dayKey }}_close_time" name="hours[{{ $dayKey }}][close_time]" class="form-control @error("hours.$dayKey.close_time") is-invalid @enderror" value="{{ $closeTime }}">
                  @error("hours.$dayKey.close_time")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                  <label class="form-label" for="{{ $dayKey }}_break_start">Break Start</label>
                  <input type="time" id="{{ $dayKey }}_break_start" name="hours[{{ $dayKey }}][break_start]" class="form-control @error("hours.$dayKey.break_start") is-invalid @enderror" value="{{ $breakStart }}">
                  @error("hours.$dayKey.break_start")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                  <label class="form-label" for="{{ $dayKey }}_break_end">Break End</label>
                  <input type="time" id="{{ $dayKey }}_break_end" name="hours[{{ $dayKey }}][break_end]" class="form-control @error("hours.$dayKey.break_end") is-invalid @enderror" value="{{ $breakEnd }}">
                  @error("hours.$dayKey.break_end")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="closed-note js-closed-note d-none">This day is closed.</div>
              <div class="closed-note js-24-note d-none">This day is open 24 hours. Specific time fields are not required.</div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <div class="save-bar">
      <a href="{{ route('secretary.clinic.show', $clinic) }}" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Settings</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-day-card]').forEach(function (card) {
    const openToggle = card.querySelector('.js-open-toggle');
    const hoursToggle = card.querySelector('.js-24-toggle');
    const timeGrid = card.querySelector('.js-time-grid');
    const closedNote = card.querySelector('.js-closed-note');
    const hoursNote = card.querySelector('.js-24-note');
    const timeInputs = timeGrid.querySelectorAll('input[type="time"]');

    function sync() {
      const isOpen = openToggle.checked;
      const is24 = hoursToggle.checked;

      hoursToggle.disabled = !isOpen;
      timeGrid.classList.toggle('d-none', !isOpen || is24);
      closedNote.classList.toggle('d-none', isOpen);
      hoursNote.classList.toggle('d-none', !isOpen || !is24);

      timeInputs.forEach(function (input) {
        input.disabled = !isOpen || is24;
      });

      if (!isOpen) {
        hoursToggle.checked = false;
      }
    }

    openToggle.addEventListener('change', sync);
    hoursToggle.addEventListener('change', sync);
    sync();
  });
});
</script>
@endsection