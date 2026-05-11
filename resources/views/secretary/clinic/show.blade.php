@extends('layouts.app')

@section('title', 'Clinic Settings')

@section('content')
@php
  $logoUrl = $clinic->logo ? asset('storage/' . $clinic->logo) : null;
@endphp

<style>
  .settings-page{
    width:min(96%,1180px);
    margin:0 auto;
    padding:1rem 0 2rem;
  }

  .edit-hero{
    border-radius:26px;
    padding:1.4rem;
    color:#fff;
    background:
      radial-gradient(circle at 90% 20%,rgba(255,255,255,.18),transparent 18%),
      linear-gradient(135deg,#0d6efd,#1d4ed8);
    box-shadow:0 18px 45px rgba(37,99,235,.22);
    margin-bottom:1rem;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:1rem;
    flex-wrap:wrap;
  }

  .hero-main{
    display:flex;
    align-items:center;
    gap:1rem;
    min-width:0;
    flex:1;
  }

  .hero-logo{
    width:76px;
    height:76px;
    border-radius:20px;
    background:rgba(255,255,255,.18);
    border:2px solid rgba(255,255,255,.55);
    display:grid;
    place-items:center;
    overflow:hidden;
    flex-shrink:0;
  }

  .hero-logo img{
    width:100%;
    height:100%;
    object-fit:cover;
  }

  .hero-logo i{
    font-size:2rem;
    color:#fff;
  }

  .edit-title{
    margin:0;
    font-size:clamp(1.55rem,3vw,2.25rem);
    font-weight:950;
    letter-spacing:-.05em;
    line-height:1.05;
    word-break:break-word;
  }

  .edit-subtitle{
    margin:.25rem 0 0;
    opacity:.95;
    font-weight:650;
    line-height:1.4;
    word-break:break-word;
  }

  .edit-btn{
    border-radius:14px;
    font-weight:900;
    padding:.72rem 1rem;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.4rem;
    text-decoration:none;
    white-space:nowrap;
  }

  .settings-grid{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(320px,.75fr);
    gap:1.25rem;
  }

  .settings-card{
    border:1px solid #e2e8f0;
    border-radius:24px;
    background:#fff;
    box-shadow:0 16px 40px rgba(15,23,42,.06);
    overflow:hidden;
  }

  .settings-card-body{
    padding:1.35rem;
  }

  .section-title{
    margin:0 0 1rem;
    color:#0f172a;
    font-size:1.05rem;
    font-weight:950;
    display:flex;
    align-items:center;
    gap:.5rem;
  }

  .section-title i{
    color:#0d6efd;
  }

  .info-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:.8rem;
  }

  .info-item{
    border:1px solid #edf2f7;
    background:#f8fafc;
    border-radius:16px;
    padding:.85rem 1rem;
    min-width:0;
  }

  .info-item.full{
    grid-column:1 / -1;
  }

  .info-label{
    color:#94a3b8;
    font-size:.71rem;
    font-weight:950;
    text-transform:uppercase;
    letter-spacing:.07em;
  }

  .info-value{
    color:#0f172a;
    font-weight:800;
    margin-top:.28rem;
    word-break:break-word;
    font-size:.93rem;
    line-height:1.45;
  }

  .ready-pill,
  .not-ready-pill{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    border-radius:999px;
    padding:.38rem .75rem;
    font-weight:900;
    font-size:.81rem;
    border:1px solid;
    line-height:1.25;
  }

  .ready-pill{
    background:#dcfce7;
    color:#166534;
    border-color:#bbf7d0;
  }

  .not-ready-pill{
    background:#fff7d6;
    color:#8a5a00;
    border-color:#fde68a;
  }

  .hours-list{
    display:grid;
    gap:.6rem;
  }

  .hour-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    border:1px solid #edf2f7;
    background:#f8fafc;
    border-radius:14px;
    padding:.75rem 1rem;
    min-width:0;
  }

  .hour-day{
    font-weight:950;
    color:#0f172a;
    font-size:.9rem;
    flex-shrink:0;
  }

  .hour-time{
    font-weight:900;
    color:#1d4ed8;
    text-align:right;
    font-size:.9rem;
    line-height:1.25;
  }

  .hour-closed{
    font-weight:800;
    color:#94a3b8;
    font-size:.9rem;
  }

  .hour-break{
    font-size:.74rem;
    color:#64748b;
    font-weight:750;
    text-align:right;
    margin-top:.12rem;
    line-height:1.25;
  }

  .muted-note{
    color:#94a3b8;
    font-weight:650;
    line-height:1.55;
    font-size:.84rem;
  }

  @media(max-width:900px){
    .settings-grid{
      grid-template-columns:1fr;
    }

    .info-grid{
      grid-template-columns:1fr;
    }
  }

  @media(max-width:560px){
    .settings-page{
      width:min(100% - 1rem,1180px);
    }

    .edit-hero{
      padding:1.15rem;
      align-items:flex-start;
    }

    .hero-main{
      align-items:flex-start;
    }

    .hero-logo{
      width:62px;
      height:62px;
      border-radius:16px;
    }

    .edit-btn{
      width:100%;
    }

    .hour-row{
      align-items:flex-start;
    }
  }
</style>

<div class="settings-page">
  @include('partials.alerts')

  <section class="edit-hero">
    <div class="hero-main">
      <div class="hero-logo">
        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="{{ $clinic->name }} logo">
        @else
          <i class="bi bi-hospital"></i>
        @endif
      </div>

      <div>
        <h1 class="edit-title">{{ $clinic->name }}</h1>
        <p class="edit-subtitle">
          <i class="bi bi-geo-alt me-1"></i>{{ $clinic->address }}
        </p>
      </div>
    </div>

    <a href="{{ route('secretary.clinic.edit', $clinic) }}" class="btn btn-light text-primary edit-btn">
      <i class="bi bi-pencil-square"></i>Edit Settings
    </a>
  </section>

  <div class="settings-grid">
    <section class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title">
          <i class="bi bi-info-circle"></i>Clinic Information
        </h2>

        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">Clinic Name</div>
            <div class="info-value">{{ $clinic->name }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">{{ ucfirst($clinic->status ?? 'active') }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Address</div>
            <div class="info-value">{{ $clinic->address }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Contact Number</div>
            <div class="info-value">{{ $clinic->contact_number ?? '—' }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value">{{ $clinic->email ?? '—' }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Setup</div>
            <div class="info-value">
              @if($clinic->operational_hours_configured)
                <span class="ready-pill">
                  <i class="bi bi-check-circle"></i>Operational hours configured
                </span>
              @else
                <span class="not-ready-pill">
                  <i class="bi bi-exclamation-circle"></i>Needs operational hours
                </span>
              @endif
            </div>
          </div>

          <div class="info-item full">
            <div class="info-label">Description</div>
            <div class="info-value">{{ $clinic->description ?: 'No description provided.' }}</div>
          </div>
        </div>
      </div>
    </section>

    <aside class="settings-card">
      <div class="settings-card-body">
        <h2 class="section-title">
          <i class="bi bi-clock-history"></i>Operational Hours
        </h2>

        <div class="hours-list">
          @foreach(\App\Models\ClinicOperationalHour::DAYS as $dayKey => $dayLabel)
            @php $hour = $hours[$dayKey] ?? null; @endphp

            <div class="hour-row">
              <div class="hour-day">{{ $dayLabel }}</div>

              <div>
                @if($hour?->is_open)
                  <div class="hour-time">{{ $hour->display_hours }}</div>

                  @if(! $hour->is_24_hours && ($hour->break_start || $hour->break_end))
                    <div class="hour-break">
                      Break:
                      {{ $hour->break_start ? $hour->break_start->format('g:i A') : '—' }}
                      –
                      {{ $hour->break_end ? $hour->break_end->format('g:i A') : '—' }}
                    </div>
                  @endif
                @else
                  <div class="hour-closed">Closed</div>
                @endif
              </div>
            </div>
          @endforeach
        </div>

        <p class="muted-note mt-3 mb-0">
          Operational hours guide clinic availability. Queue mode has been removed from clinic settings.
        </p>
      </div>
    </aside>
  </div>
</div>
@endsection