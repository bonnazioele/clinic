@extends('layouts.patient-dashboard')

@section('title', 'Find Clinics')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<style>
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .clinics-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .clinics-shell {
    width: 100%;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.95);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
  }

  .clinics-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .clinics-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .clinics-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .clinics-title-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    color: #ffffff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
    font-size: 1.4rem;
  }

  .clinics-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .clinics-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .clinics-hero-meta {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
  }

  .hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.48rem 0.75rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.78rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .clinics-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .search-card,
  .map-card,
  .clinics-list-card {
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
    overflow: hidden;
  }

  .search-card {
    margin-bottom: 1rem;
  }

  .card-head {
    padding: 1rem 1.15rem 0.85rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #0f172a;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .card-title i {
    color: #0d6efd;
  }

  .card-subtitle {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
    margin-top: 0.2rem;
  }

  .search-body {
    padding: 1.15rem;
  }

  .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 0.4rem;
  }

  .form-control,
  .form-select,
  .input-group-text {
    border-color: #dbe3ef;
    box-shadow: none;
  }

  .form-control,
  .form-select {
    border-radius: 14px;
    padding: 0.68rem 0.8rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 600;
  }

  .input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .input-group-text {
    border-radius: 14px 0 0 14px;
    background: #f8fafc;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(13, 110, 253, 0.55);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
  }

  .search-btn,
  .clear-btn {
    border-radius: 13px;
    padding: 0.66rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 800;
  }

  .quick-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .quick-info-card {
    padding: 0.85rem;
    border-radius: 18px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    display: flex;
    gap: 0.65rem;
    align-items: flex-start;
  }

  .quick-info-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    font-size: 1.05rem;
  }

  .quick-info-title {
    color: #0f172a;
    font-size: 0.84rem;
    font-weight: 800;
    margin-bottom: 0.1rem;
  }

  .quick-info-text {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .map-card {
    margin-bottom: 1rem;
  }

  .map-body {
    padding: 1.15rem;
  }

  .clinic-map {
    width: 100%;
    height: 360px;
    border-radius: 18px;
    border: 1px solid #edf2f7;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.055);
  }

  .map-note {
    margin-top: 0.65rem;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .list-toolbar {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
  }

  .result-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.42rem 0.7rem;
    background: #eaf3ff;
    color: #0d6efd;
    font-size: 0.75rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .view-toggle {
    display: inline-flex;
    padding: 0.18rem;
    border-radius: 12px;
    border: 1px solid #dbe3ef;
    background: #f8fafc;
  }

  .view-toggle button {
    border: 0;
    background: transparent;
    color: #64748b;
    width: 34px;
    height: 30px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    transition: 0.18s ease;
  }

  .view-toggle button.active {
    background: #0d6efd;
    color: #ffffff;
    box-shadow: 0 8px 18px rgba(13, 110, 253, 0.2);
  }

  .clinics-list-body {
    padding: 1.15rem;
  }

  .clinics-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.9rem;
  }

  .clinics-grid.list-mode {
    grid-template-columns: 1fr;
  }

  .clinic-card-modern {
    position: relative;
    min-height: 100%;
    padding: 1rem;
    border-radius: 19px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    transition: 0.22s ease;
  }

  .clinic-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.09);
  }

  .clinic-card-modern::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .clinics-grid.list-mode .clinic-card-modern {
    display: grid;
    grid-template-columns: 1.3fr 1fr 0.9fr;
    gap: 1rem;
    align-items: center;
  }

  .clinic-main {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    min-width: 0;
  }

  .clinic-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.35rem;
  }

  .clinic-name {
    margin: 0 0 0.2rem;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: -0.025em;
    line-height: 1.2;
  }

  .clinic-address {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .clinic-address i {
    color: #0d6efd;
  }

  .clinic-section {
    margin-top: 0.9rem;
    padding-top: 0.85rem;
    border-top: 1px solid #edf2f7;
  }

  .clinics-grid.list-mode .clinic-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
  }

  .mini-section-title {
    display: flex;
    align-items: center;
    gap: 0.38rem;
    margin: 0 0 0.5rem;
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 800;
  }

  .mini-section-title i {
    color: #0d6efd;
  }

  .service-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
  }

  .service-tag {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.34rem 0.55rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.72rem;
    font-weight: 800;
  }

  .service-tag.more {
    background: #f8fafc;
    color: #475569;
    border-color: #e2e8f0;
  }

  .queue-status-box {
    margin-top: 0.85rem;
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .clinics-grid.list-mode .queue-status-box {
    margin-top: 0;
  }

  .queue-box-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
  }

  .queue-label {
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 800;
  }

  .queue-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.32rem;
    border-radius: 999px;
    padding: 0.35rem 0.58rem;
    font-size: 0.72rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .queue-badge.busy {
    background: #fff7db;
    color: #8a6300;
    border: 1px solid #ffe7a2;
  }

  .queue-badge.open {
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
  }

  .queue-note {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .queue-note.open {
    color: #0f9f6e;
  }

  .clinic-actions {
    margin-top: 0.9rem;
    display: grid;
    gap: 0.5rem;
  }

  .clinics-grid.list-mode .clinic-actions {
    margin-top: 0;
  }

  .clinic-actions .btn {
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 800;
    padding: 0.5rem 0.75rem;
  }

  .auto-queue-note {
    text-align: center;
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 600;
  }

  .empty-state {
    padding: 2rem 1rem;
    text-align: center;
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 18px;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
  }

  .empty-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 0.8rem;
    display: grid;
    place-items: center;
    border-radius: 18px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 1.65rem;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.11);
  }

  .empty-title {
    margin: 0 0 0.3rem;
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 800;
  }

  .empty-text {
    color: #64748b;
    margin-bottom: 0.95rem;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .pagination-wrap {
    display: flex;
    justify-content: center;
    margin-top: 1.1rem;
  }

  .clinic-modal .modal-content {
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
  }

  .clinic-modal .modal-header {
    border-bottom: 1px solid #edf2f7;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 34%),
      linear-gradient(135deg, #ffffff, #f8fbff);
  }

  .clinic-modal .modal-title {
    color: #0f172a;
    font-weight: 900;
    letter-spacing: -0.025em;
  }

  .modal-clinic-card {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    margin-bottom: 0.9rem;
  }

  .modal-logo {
    width: 68px;
    height: 68px;
    flex: 0 0 68px;
    object-fit: cover;
    border-radius: 18px;
    background: #0d6efd;
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 1.4rem;
    font-weight: 900;
  }

  .modal-section {
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px solid #edf2f7;
    background: #ffffff;
    margin-bottom: 0.8rem;
  }

  .modal-section-title {
    color: #0f172a;
    font-weight: 900;
    font-size: 0.92rem;
    margin-bottom: 0.55rem;
  }

  @media (max-width: 1200px) {
    .clinics-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .quick-info-grid {
      grid-template-columns: 1fr;
    }

    .clinics-grid.list-mode .clinic-card-modern {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .clinics-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .clinics-hero,
    .clinics-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .clinics-hero-row,
    .card-head {
      flex-direction: column;
      align-items: stretch;
    }

    .clinics-title {
      font-size: 1.35rem;
    }

    .clinics-subtitle {
      font-size: 0.82rem;
    }

    .clinics-grid {
      grid-template-columns: 1fr;
    }

    .clinic-map {
      height: 300px;
    }

    .search-btn,
    .clear-btn {
      width: 100%;
    }

    .list-toolbar {
      justify-content: space-between;
    }
  }
</style>
@endpush

@section('content')
<div class="patient-tab-shell">
  <div class="patient-tab-content">
    <div class="container-fluid clinics-page px-0">
      @include('partials.alerts', ['toastOffsetTop' => '7rem'])

      <div class="clinics-shell">
        <div class="clinics-hero">
          <div class="clinics-hero-row">
            <div class="clinics-title-wrap">
              <div class="clinics-title-icon">
                <i class="bi bi-hospital"></i>
              </div>

              <div>
                <h1 class="clinics-title">Find Clinics</h1>
                <p class="clinics-subtitle">
                  Search nearby clinics, compare services, check queue activity, and book your visit.
                </p>
              </div>
            </div>

            <div class="clinics-hero-meta">
              <span class="hero-pill">
                <i class="bi bi-building"></i>
                {{ $clinics->total() }} {{ $clinics->total() == 1 ? 'Clinic' : 'Clinics' }}
              </span>

              <span class="hero-pill">
                <i class="bi bi-geo-alt"></i>
                Map View
              </span>
            </div>
          </div>
        </div>

        <div class="clinics-body">
          <section class="search-card">
            <div class="card-head">
              <div>
                <h5 class="card-title">
                  <i class="bi bi-search"></i>
                  Search & Filter Clinics
                </h5>
                <div class="card-subtitle">
                  Find a clinic by name, location, or available medical service.
                </div>
              </div>
            </div>

            <div class="search-body">
              <form class="row g-3" method="GET" action="{{ route('clinics.index') }}">
                <div class="col-lg-5">
                  <label class="form-label">
                    <i class="bi bi-building me-1"></i>
                    Clinic Name or Location
                  </label>

                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-search text-muted"></i>
                    </span>

                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="e.g. San Nicolas Health Center, Cebu City"
                           value="{{ request('name') }}">
                  </div>
                </div>

                <div class="col-lg-4">
                  <label class="form-label">
                    <i class="bi bi-clipboard2-pulse me-1"></i>
                    Medical Service
                  </label>

                  <select name="service_id" class="form-select">
                    <option value="">All Medical Services</option>

                    @foreach(\App\Models\Service::all() as $service)
                      <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>
                        {{ $service->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-lg-3 d-flex align-items-end">
                  <div class="d-grid w-100 gap-2">
                    <button type="submit" class="btn btn-primary search-btn">
                      <i class="bi bi-funnel me-2"></i>
                      Search Clinics
                    </button>

                    @if(request('name') || request('service_id'))
                      <a href="{{ route('clinics.index') }}" class="btn btn-outline-secondary clear-btn">
                        <i class="bi bi-x-circle me-2"></i>
                        Clear Filters
                      </a>
                    @endif
                  </div>
                </div>
              </form>
            </div>
          </section>

          <section class="quick-info-grid">
            <div class="quick-info-card">
              <div class="quick-info-icon">
                <i class="bi bi-search-heart"></i>
              </div>
              <div>
                <div class="quick-info-title">Search first</div>
                <div class="quick-info-text">
                  Use clinic name, address, or service to narrow your options.
                </div>
              </div>
            </div>

            <div class="quick-info-card">
              <div class="quick-info-icon">
                <i class="bi bi-people"></i>
              </div>
              <div>
                <div class="quick-info-title">Check queue activity</div>
                <div class="quick-info-text">
                  See if the clinic has an active queue before booking.
                </div>
              </div>
            </div>

            <div class="quick-info-card">
              <div class="quick-info-icon">
                <i class="bi bi-calendar-plus"></i>
              </div>
              <div>
                <div class="quick-info-title">Book your visit</div>
                <div class="quick-info-text">
                  Pick a clinic and create an appointment from its card.
                </div>
              </div>
            </div>
          </section>

          <section class="map-card">
            <div class="card-head">
              <div>
                <h5 class="card-title">
                  <i class="bi bi-geo-alt"></i>
                  Clinic Locations
                </h5>
                <div class="card-subtitle">
                  Click a marker to preview clinic details and book quickly.
                </div>
              </div>
            </div>

            <div class="map-body">
             <div id="map" class="clinic-map"></div>

            <div class="map-note" id="mapNote">
              <i class="bi bi-info-circle"></i>
              Showing clinic locations from the current filtered results.
            </div>
            </div>
          </section>

          <section class="clinics-list-card">
            <div class="card-head">
              <div>
                <h5 class="card-title">
                  <i class="bi bi-building"></i>
                  Available Clinics
                </h5>
                <div class="card-subtitle">
                  Review services, queue status, and booking options.
                </div>
              </div>

              <div class="list-toolbar">
                <span class="result-pill">
                  <i class="bi bi-check2-circle"></i>
                  {{ $clinics->total() }} found
                </span>

                <div class="view-toggle" role="group" aria-label="View toggle">
                  <button type="button" id="gridView" class="active" title="Grid view">
                    <i class="bi bi-grid-3x3-gap"></i>
                  </button>

                  <button type="button" id="listView" title="List view">
                    <i class="bi bi-list-ul"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="clinics-list-body">
              <div id="clinicsGrid" class="clinics-grid">
                @forelse($clinics as $clinic)
                  @php
                    $lat = $clinic->gps_latitude ?? $clinic->latitude;
                    $lng = $clinic->gps_longitude ?? $clinic->longitude;

                    $waitingCount = \App\Models\QueueEntry::where('clinic_id', $clinic->id)
                      ->whereIn('status', ['waiting', 'now_serving'])
                      ->count();
                  @endphp

                  <article class="clinic-card-modern">
                    <div class="clinic-main">
                      <div class="clinic-icon">
                        <i class="bi bi-hospital"></i>
                      </div>

                      <div>
                        <h5 class="clinic-name">{{ $clinic->name }}</h5>

                        <div class="clinic-address">
                          <i class="bi bi-geo-alt me-1"></i>
                          {{ $clinic->address }}
                        </div>
                      </div>
                    </div>

                    <div class="clinic-section">
                      <h6 class="mini-section-title">
                        <i class="bi bi-clipboard2-pulse"></i>
                        Services
                      </h6>

                      <div class="service-tags">
                        @foreach($clinic->services->take(3) as $service)
                          <span class="service-tag">{{ $service->name }}</span>
                        @endforeach

                        @if($clinic->services->count() > 3)
                          <span class="service-tag more">
                            +{{ $clinic->services->count() - 3 }} more
                          </span>
                        @endif

                        @if($clinic->services->count() === 0)
                          <span class="service-tag more">No services listed</span>
                        @endif
                      </div>

                      <div class="queue-status-box">
                        <div class="queue-box-top">
                          <div class="queue-label">
                            <i class="bi bi-people me-1"></i>
                            Queue Status
                          </div>

                          @if($waitingCount > 0)
                            <span class="queue-badge busy">
                              <i class="bi bi-clock-fill"></i>
                              {{ $waitingCount }} active
                            </span>
                          @else
                            <span class="queue-badge open">
                              <i class="bi bi-check-circle-fill"></i>
                              No wait
                            </span>
                          @endif
                        </div>

                        @if($waitingCount > 0)
                          <div class="queue-note">
                            Estimated active queue time: {{ $waitingCount * 15 }} minutes.
                          </div>
                        @else
                          <div class="queue-note open">
                            No active waiting queue right now.
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="clinic-actions">
                      <button type="button"
                              class="btn btn-outline-primary"
                              onclick="showClinicDetails({{ $clinic->id }})">
                        <i class="bi bi-info-circle me-2"></i>
                        View Details
                      </button>

                      @if($lat && $lng)
                        <button type="button"
                                class="btn btn-outline-secondary"
                                onclick="focusOnMap({{ $lat }}, {{ $lng }})">
                          <i class="bi bi-geo-alt me-2"></i>
                          Show on Map
                        </button>
                      @endif

                      <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}" class="btn btn-primary">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Book Appointment
                      </a>

                      <div class="auto-queue-note">
                        <i class="bi bi-info-circle me-1"></i>
                        You may be added to the queue after booking.
                      </div>
                    </div>
                  </article>
                @empty
                  <div class="empty-state">
                    <div class="empty-icon">
                      <i class="bi bi-building-x"></i>
                    </div>

                    <h5 class="empty-title">No clinics found</h5>
                    <p class="empty-text">
                      Try adjusting your clinic name, location, or service filter.
                    </p>

                    <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary search-btn">
                      <i class="bi bi-arrow-clockwise me-2"></i>
                      Clear Filters
                    </a>
                  </div>
                @endforelse
              </div>

              @if($clinics->hasPages())
                <div class="pagination-wrap">
                  <nav aria-label="Clinics pagination">
                    {{ $clinics->withQueryString()->links() }}
                  </nav>
                </div>
              @endif
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade clinic-modal" id="clinicDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-hospital me-2 text-primary"></i>
          Clinic Details
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="clinicDetailsContent"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
let map;
let markers = [];

document.addEventListener('DOMContentLoaded', function () {
  initializeMap();
  initializeViewToggle();
  initializeQueueUpdates();
});

function initializeMap() {
  const el = document.getElementById('map');

  if (!el || typeof L === 'undefined') return;

  map = L.map(el).setView([10.3157, 123.8854], 10);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const bounds = L.latLngBounds();

  @foreach($clinics as $clinic)
    @php
      $lat = $clinic->gps_latitude ?? $clinic->latitude;
      $lng = $clinic->gps_longitude ?? $clinic->longitude;
    @endphp

    @if($lat && $lng)
      (function () {
        const lat = parseFloat('{{ $lat }}');
        const lng = parseFloat('{{ $lng }}');

        const marker = L.marker([lat, lng])
          .addTo(map)
          .bindPopup(`
            <div class="text-center" style="min-width: 180px;">
              <h6 class="fw-bold text-primary mb-1">{{ addslashes($clinic->name) }}</h6>
              <p class="mb-2 small text-muted">{{ addslashes($clinic->address) }}</p>
              <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}"
                 class="btn btn-sm btn-primary">
                <i class="bi bi-calendar-plus me-1"></i>
                Book Now
              </a>
            </div>
          `);

        markers.push({
          id: {{ $clinic->id }},
          marker: marker,
          lat: lat,
          lng: lng
        });

        bounds.extend([lat, lng]);
      })();
    @endif
  @endforeach

  if (bounds.isValid()) {
    map.fitBounds(bounds.pad(0.1));
  }

  setTimeout(() => {
    map.invalidateSize();
  }, 300);
}

function initializeViewToggle() {
  const gridView = document.getElementById('gridView');
  const listView = document.getElementById('listView');
  const clinicsGrid = document.getElementById('clinicsGrid');

  if (!gridView || !listView || !clinicsGrid) return;

  gridView.addEventListener('click', function () {
    clinicsGrid.classList.remove('list-mode');
    gridView.classList.add('active');
    listView.classList.remove('active');

    if (map) {
      setTimeout(() => map.invalidateSize(), 150);
    }
  });

  listView.addEventListener('click', function () {
    clinicsGrid.classList.add('list-mode');
    listView.classList.add('active');
    gridView.classList.remove('active');

    if (map) {
      setTimeout(() => map.invalidateSize(), 150);
    }
  });
}

function initializeQueueUpdates() {
  /*
   * Reserved for future live queue updates.
   */
}

function focusOnMap(lat, lng) {
  if (!map) return;

  const targetLat = parseFloat(lat);
  const targetLng = parseFloat(lng);

  map.setView([targetLat, targetLng], 15);

  markers.forEach(item => {
    if (item.lat === targetLat && item.lng === targetLng) {
      item.marker.openPopup();
    }
  });

  document.getElementById('map')?.scrollIntoView({
    behavior: 'smooth',
    block: 'center'
  });
}

function showClinicDetails(clinicId) {
  const modalEl = document.getElementById('clinicDetailsModal');
  const bodyEl = document.getElementById('clinicDetailsContent');

  if (!modalEl || !bodyEl) return;

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  bodyEl.innerHTML = `
    <div class="text-center py-4">
      <div class="spinner-border text-primary mb-3"></div>
      <p class="text-muted mb-0">Loading clinic details...</p>
    </div>
  `;

  modal.show();

  fetch(`{{ url('/clinics') }}/${clinicId}`, {
    headers: {
      'Accept': 'application/json'
    }
  })
    .then(async response => {
      if (!response.ok) {
        throw new Error('Failed to load clinic details');
      }

      return response.json();
    })
    .then(data => {
      bodyEl.innerHTML = renderClinicDetails(data);
    })
    .catch(error => {
      console.error(error);

      bodyEl.innerHTML = `
        <div class="alert alert-danger mb-0">
          Unable to load clinic details. Please try again later.
        </div>
      `;
    });
}

function renderClinicDetails(data) {
  const services = (data.services || [])
    .map(service => `
      <span class="service-tag me-1 mb-1">
        ${escapeHtml(service.name)}
      </span>
    `)
    .join('') || '<span class="text-muted small">No services listed.</span>';

  const doctors = (data.doctors || [])
    .map(doctor => `
      <li class="list-group-item py-2">
        <i class="bi bi-person-badge me-2 text-primary"></i>
        ${escapeHtml(doctor.name)}
      </li>
    `)
    .join('');

  const doctorsBlock = doctors
    ? `<ul class="list-group small">${doctors}</ul>`
    : '<p class="text-muted mb-0 small">No doctors associated.</p>';

  const editBtn = data.can_edit && data.edit_url
    ? `
      <a href="${data.edit_url}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-pencil-square me-1"></i>
        Edit Clinic
      </a>
    `
    : '';

  const logo = data.logo_url
    ? `<img src="${data.logo_url}" alt="Logo" class="modal-logo">`
    : `<div class="modal-logo">${escapeHtml((data.name || 'C').charAt(0))}</div>`;

  return `
    <div class="clinic-details-content">
      ${
        data.cover_image_url
          ? `<div class="mb-3">
              <img src="${data.cover_image_url}" alt="Cover" class="img-fluid rounded-4 w-100" style="max-height: 220px; object-fit: cover;">
            </div>`
          : ''
      }

      <div class="modal-clinic-card">
        ${logo}

        <div class="flex-grow-1">
          <h5 class="fw-bold text-primary mb-1">${escapeHtml(data.name || 'Clinic')}</h5>

          <p class="mb-1 small text-muted">
            <i class="bi bi-geo-alt me-1"></i>
            ${escapeHtml(data.address || 'Unknown address')}
          </p>

          <p class="mb-1 small text-muted">
            <i class="bi bi-telephone me-1"></i>
            ${escapeHtml(data.contact_number || 'N/A')}
          </p>

          <p class="mb-2 small text-muted">
            <i class="bi bi-envelope me-1"></i>
            ${escapeHtml(data.email || 'N/A')}
          </p>

          ${editBtn}
        </div>
      </div>

      <div class="modal-section">
        <h6 class="modal-section-title">
          <i class="bi bi-info-circle me-1 text-primary"></i>
          About
        </h6>

        <p class="mb-0 text-muted small">
          ${escapeHtml(data.description || 'No description provided.')}
        </p>
      </div>

      <div class="modal-section">
        <h6 class="modal-section-title">
          <i class="bi bi-clipboard2-pulse me-1 text-primary"></i>
          Services
        </h6>

        <div class="d-flex flex-wrap">
          ${services}
        </div>
      </div>

      <div class="modal-section mb-0">
        <h6 class="modal-section-title">
          <i class="bi bi-person-lines-fill me-1 text-primary"></i>
          Doctors
        </h6>

        ${doctorsBlock}
      </div>
    </div>
  `;
}

  function escapeHtml(str) {
    if(str == null) return '';
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }
<<<<<<< Updated upstream
  </script>
=======
})();
</script>
>>>>>>> Stashed changes
function escapeHtml(str) {
  if (str == null) return '';

  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>
@endpush
