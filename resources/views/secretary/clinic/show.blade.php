@extends('layouts.app')

@section('title', 'Clinic Settings')

@section('content')
@php
  $logoUrl = $clinic->logo ? asset('storage/' . $clinic->logo) : null;
  $coverUrl = $clinic->cover_image ? asset('storage/' . $clinic->cover_image) : null;
@endphp

<style>
  .settings-page{width:min(96%,1180px);margin:0 auto;padding:1rem 0 2rem}.settings-hero{border-radius:28px;overflow:hidden;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.08);border:1px solid #e2e8f0;margin-bottom:1rem}.settings-cover{min-height:210px;background:linear-gradient(135deg,#0d6efd,#1d4ed8);position:relative}.settings-cover.has-cover{background-size:cover;background-position:center}.settings-cover::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,23,42,.1),rgba(15,23,42,.55))}.settings-hero-body{position:relative;padding:1.25rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-top:-86px;z-index:2;flex-wrap:wrap}.clinic-identity{display:flex;align-items:flex-end;gap:1rem}.clinic-logo{width:110px;height:110px;border-radius:28px;border:5px solid #fff;background:#eff6ff;object-fit:cover;box-shadow:0 14px 35px rgba(15,23,42,.18)}.clinic-logo-placeholder{display:grid;place-items:center;color:#0d6efd;font-size:2.5rem}.clinic-title{color:#fff;margin:0;font-size:clamp(1.7rem,3vw,2.45rem);font-weight:950;letter-spacing:-.055em;text-shadow:0 10px 28px rgba(15,23,42,.32)}.clinic-subtitle{color:rgba(255,255,255,.92);font-weight:750;margin:.25rem 0 0}.edit-btn{border:0;border-radius:16px;padding:.8rem 1rem;font-weight:900;background:#fff;color:#0d6efd;box-shadow:0 14px 30px rgba(15,23,42,.14);text-decoration:none}.edit-btn:hover{color:#0b5ed7;background:#f8fafc}.settings-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(340px,.75fr);gap:1rem}.settings-card{border:1px solid #e2e8f0;border-radius:24px;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.06);overflow:hidden}.settings-card-body{padding:1.25rem}.section-title{margin:0 0 1rem;color:#0f172a;font-size:1.1rem;font-weight:950;display:flex;align-items:center;gap:.5rem}.section-title i{color:#0d6efd}.info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.info-item{border:1px solid #edf2f7;background:#f8fafc;border-radius:18px;padding:.9rem}.info-label{color:#64748b;font-size:.74rem;font-weight:950;text-transform:uppercase;letter-spacing:.06em}.info-value{color:#0f172a;font-weight:900;margin-top:.25rem;word-break:break-word}.hours-list{display:grid;gap:.7rem}.hour-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #edf2f7;background:#f8fafc;border-radius:18px;padding:.85rem}.hour-day{font-weight:950;color:#0f172a}.hour-time{font-weight:900;color:#1d4ed8;text-align:right}.hour-break{font-size:.78rem;color:#64748b;font-weight:750;text-align:right;margin-top:.15rem}.ready-pill{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;padding:.45rem .75rem;font-weight:900;background:#dcfce7;color:#166534;border:1px solid #bbf7d0}.not-ready-pill{background:#fff7d6;color:#8a5a00;border-color:#fde68a}.muted-note{color:#64748b;font-weight:650;line-height:1.55}@media(max-width:900px){.settings-grid,.info-grid{grid-template-columns:1fr}.settings-hero-body{margin-top:-70px}.clinic-identity{align-items:flex-start;flex-direction:column}.clinic-title,.clinic-subtitle{color:#0f172a;text-shadow:none}.settings-hero-body{background:#fff}.edit-btn{background:#eff6ff}}
</style>

<div class="settings-page">
  @include('partials.alerts')

  <section class="settings-hero">
    <div class="settings-cover {{ $coverUrl ? 'has-cover' : '' }}" @if($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif></div>

    <div class="settings-hero-body">
      <div class="clinic-identity">
        @if($logoUrl)
          <img class="clinic-logo" src="{{ $logoUrl }}" alt="{{ $clinic->name }} logo">
        @else
          <div class="clinic-logo clinic-logo-placeholder"><i class="bi bi-hospital"></i></div>
        @endif

        <div>
          <h1 class="clinic-title">{{ $clinic->name }}</h1>
          <p class="clinic-subtitle"><i class="bi bi-geo-alt me-1"></i>{{ $clinic->address }}</p>
        </div>
      </div>

      <a href="{{ route('secretary.clinic.edit', $clinic) }}" class="edit-btn">
        <i class="bi bi-pencil-square me-1"></i>Edit Settings
      </a>
    </div>
  </section>

  <div class="settings-grid">
    <section class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title"><i class="bi bi-info-circle"></i>Clinic Information</h2>

        <div class="info-grid">
          <div class="info-item"><div class="info-label">Clinic Name</div><div class="info-value">{{ $clinic->name }}</div></div>
          <div class="info-item"><div class="info-label">Status</div><div class="info-value">{{ ucfirst($clinic->status ?? 'active') }}</div></div>
          <div class="info-item"><div class="info-label">Address</div><div class="info-value">{{ $clinic->address }}</div></div>
          <div class="info-item"><div class="info-label">Contact Number</div><div class="info-value">{{ $clinic->contact_number ?? '—' }}</div></div>
          <div class="info-item"><div class="info-label">Email</div><div class="info-value">{{ $clinic->email ?? '—' }}</div></div>
          <div class="info-item"><div class="info-label">Setup</div><div class="info-value">
            @if($clinic->operational_hours_configured)
              <span class="ready-pill"><i class="bi bi-check-circle"></i>Operational hours configured</span>
            @else
              <span class="ready-pill not-ready-pill"><i class="bi bi-exclamation-circle"></i>Needs operational hours</span>
            @endif
          </div></div>
        </div>

        <div class="info-item mt-3">
          <div class="info-label">Description</div>
          <div class="info-value">{{ $clinic->description ?: 'No description provided.' }}</div>
        </div>
      </div>
    </section>

    <aside class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title"><i class="bi bi-clock-history"></i>Operational Hours</h2>

        <div class="hours-list">
          @foreach(\App\Models\ClinicOperationalHour::DAYS as $dayKey => $dayLabel)
            @php $hour = $hours[$dayKey] ?? null; @endphp
            <div class="hour-row">
              <div class="hour-day">{{ $dayLabel }}</div>
              <div>
                <div class="hour-time">{{ $hour?->display_hours ?? 'Closed' }}</div>
                @if($hour?->is_open && ! $hour?->is_24_hours && ($hour?->break_start || $hour?->break_end))
                  <div class="hour-break">
                    Break: {{ $hour->break_start ? $hour->break_start->format('g:i A') : '—' }} - {{ $hour->break_end ? $hour->break_end->format('g:i A') : '—' }}
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>

        <p class="muted-note mt-3 mb-0">
          Operational hours are used to guide clinic availability. Queue mode has been removed from clinic settings.
        </p>
      </div>
    </aside>
  </div>
</div>
@endsection
