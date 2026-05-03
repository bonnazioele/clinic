@extends('layouts.app')

@section('title', 'Manage Services')

@section('content')
@php
  $user = auth()->user();

  $secretaryClinicRouteValue = request()->route('clinic');
  $secretaryClinicId = null;

  if (is_object($secretaryClinicRouteValue) && isset($secretaryClinicRouteValue->id)) {
      $secretaryClinicId = $secretaryClinicRouteValue->id;
  }

  if (!$secretaryClinicId && is_numeric($secretaryClinicRouteValue)) {
      $secretaryClinicId = $secretaryClinicRouteValue;
  }

  if (!$secretaryClinicId && isset($clinic) && isset($clinic->id)) {
      $secretaryClinicId = $clinic->id;
  }

  if (!$secretaryClinicId && session('active_clinic_id')) {
      $secretaryClinicId = session('active_clinic_id');
  }

  if (!$secretaryClinicId && isset($user->clinic_id)) {
      $secretaryClinicId = $user->clinic_id;
  }

  if (!$secretaryClinicId && isset($user->clinics) && $user->clinics->count()) {
      $secretaryClinicId = $user->clinics->first()->id;
  }

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) {
          return url($fallback);
      }

      $params = (array) $params;

      if ($secretaryClinicId && !array_key_exists('clinic', $params)) {
          $params = array_merge(['clinic' => $secretaryClinicId], $params);
      }

      try {
          return route($routeName, $params);
      } catch (\Throwable $error) {
          return url($fallback);
      }
  };

  $serviceItems = collect();

  if (isset($services)) {
      if (method_exists($services, 'getCollection')) {
          $serviceItems = $services->getCollection();
      } elseif ($services instanceof \Illuminate\Support\Collection) {
          $serviceItems = $services;
      } elseif (is_array($services)) {
          $serviceItems = collect($services);
      }
  }

  $serviceItems = $serviceItems->filter(function ($service) {
      return is_object($service);
  })->values();

  $serviceCount = isset($services) && method_exists($services, 'total')
      ? $services->total()
      : $serviceItems->count();

  $totalAssignedDoctors = isset($activeDoctorCounts)
      ? collect($activeDoctorCounts)->sum()
      : 0;

  $totalTodayQueue = isset($todayQueueCounts)
      ? collect($todayQueueCounts)->sum()
      : 0;

  $createUrl = $secUrl('secretary.services.create', [], '/secretary/dashboard');
@endphp

<style>
  .service-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .service-hero {
    border-radius: 26px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      radial-gradient(circle at 18% 92%, rgba(125,211,252,.22), transparent 24%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
  }

  .service-hero::after {
    content: "";
    position: absolute;
    right: -55px;
    bottom: -65px;
    width: 180px;
    height: 180px;
    border-radius: 55px;
    background: rgba(255, 255, 255, 0.12);
    transform: rotate(12deg);
  }

  .service-hero-row {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .service-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .service-title-icon {
    width: 62px;
    height: 62px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 1.7rem;
    flex: 0 0 62px;
  }

  .service-title {
    margin: 0;
    font-size: clamp(1.55rem, 2.6vw, 2.25rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .service-subtitle {
    margin: 0.35rem 0 0;
    font-size: 0.97rem;
    font-weight: 650;
    opacity: 0.94;
    max-width: 760px;
  }

  .service-hero-actions {
    display: flex;
    gap: 0.55rem;
    flex-wrap: wrap;
  }

  .service-hero-actions .btn {
    border-radius: 15px;
    font-weight: 900;
    min-height: 44px;
  }

  .service-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .service-stat-card {
    min-height: 140px;
    border-radius: 23px;
    padding: 1rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
  }

  .service-stat-card::after {
    content: "";
    position: absolute;
    right: -24px;
    bottom: -24px;
    width: 112px;
    height: 112px;
    border-radius: 36px;
    background: rgba(255,255,255,.14);
    transform: rotate(4deg);
  }

  .service-stat-blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .service-stat-green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .service-stat-purple {
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
  }

  .service-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .service-stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.45rem;
    flex: 0 0 54px;
  }

  .service-stat-value {
    font-size: 2.05rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.055em;
    margin-bottom: 0.42rem;
  }

  .service-stat-label {
    font-size: 0.88rem;
    font-weight: 900;
  }

  .service-stat-help {
    margin-top: 0.16rem;
    font-size: 0.78rem;
    font-weight: 650;
    opacity: 0.9;
  }

  .service-panel {
    border-radius: 26px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .service-panel-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    background: rgba(248,250,252,.85);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .service-panel-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .service-panel-title i {
    color: #0d6efd;
  }

  .service-toolbar {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
  }

  .service-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.42rem 0.72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
  }

  .service-view-toggle {
    display: inline-flex;
    padding: 0.18rem;
    border-radius: 14px;
    border: 1px solid #dbe3ef;
    background: #ffffff;
  }

  .service-view-toggle button {
    border: 0;
    background: transparent;
    color: #64748b;
    width: 38px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    transition: 0.18s ease;
  }

  .service-view-toggle button.active {
    background: #0d6efd;
    color: #ffffff;
    box-shadow: 0 8px 18px rgba(13, 110, 253, 0.22);
  }

  .service-grid {
    padding: 1.1rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }

  .service-card {
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15,23,42,.055);
    transition: 0.18s ease;
    display: flex;
    flex-direction: column;
    min-height: 100%;
  }

  .service-card:hover {
    transform: translateY(-4px);
    border-color: #bfdbfe;
    box-shadow: 0 22px 42px rgba(15,23,42,.10);
  }

  .service-card-top {
    padding: 1rem;
    background:
      radial-gradient(circle at 88% 20%, rgba(255,255,255,.24), transparent 18%),
      linear-gradient(135deg, #eff6ff, #ffffff);
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-start;
  }

  .service-icon {
    width: 58px;
    height: 58px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    font-size: 1.45rem;
    flex: 0 0 58px;
    box-shadow: 0 12px 24px rgba(13,110,253,.22);
  }

  .service-name {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: -0.03em;
    line-height: 1.2;
  }

  .service-desc {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 650;
    line-height: 1.45;
  }

  .service-more-btn {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    border: 1px solid #dbe3ef;
    background: #ffffff;
    color: #64748b;
    display: grid;
    place-items: center;
  }

  .service-card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    flex: 1;
  }

  .service-mini-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.65rem;
    margin-bottom: 0.95rem;
  }

  .service-mini-info {
    border-radius: 16px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    padding: 0.7rem;
  }

  .service-mini-info small {
    display: block;
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 0.15rem;
  }

  .service-mini-info strong {
    color: #0f172a;
    font-size: 0.84rem;
    font-weight: 900;
  }

  .service-note {
    border-radius: 16px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a8a;
    padding: 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .service-actions {
    margin-top: auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem;
  }

  .service-actions .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .service-table-wrap {
    display: none;
    padding: 1.1rem;
  }

  .service-table {
    margin: 0;
  }

  .service-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem;
  }

  .service-table tbody td {
    padding: 1rem;
    vertical-align: middle;
  }

  .service-row-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.25rem;
    flex: 0 0 48px;
  }

  .service-row-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.45rem;
    flex-wrap: wrap;
  }

  .service-row-actions .btn {
    border-radius: 12px;
    font-weight: 850;
  }

  .service-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
  }

  .service-empty-icon {
    width: 82px;
    height: 82px;
    margin: 0 auto 1rem;
    border-radius: 26px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 2.35rem;
  }

  .service-pagination {
    padding: 0 1.1rem 1.1rem;
    display: flex;
    justify-content: center;
  }

  @media (max-width: 1200px) {
    .service-stats-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .service-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .service-mini-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .service-page {
      width: 100%;
    }

    .service-hero,
    .service-panel {
      border-radius: 22px;
    }

    .service-stats-grid,
    .service-grid,
    .service-mini-grid {
      grid-template-columns: 1fr;
    }

    .service-grid,
    .service-table-wrap {
      padding: 0.85rem;
    }

    .service-actions {
      grid-template-columns: 1fr;
    }

    .service-row-actions,
    .service-row-actions .btn,
    .service-row-actions form {
      width: 100%;
    }
  }
</style>

<div class="service-page">
  @include('partials.alerts')

  <section class="service-hero">
    <div class="service-hero-row">
      <div class="service-title-wrap">
        <div class="service-title-icon">
          <i class="bi bi-clipboard2-pulse"></i>
        </div>

        <div>
          <h1 class="service-title">Manage Services</h1>
          <p class="service-subtitle">
            Manage services offered by {{ $clinic->name ?? 'your clinic' }}. Today’s queue only counts patients scheduled for today.
          </p>
        </div>
      </div>

      <div class="service-hero-actions">
        <a href="{{ $createUrl }}" class="btn btn-light text-primary">
          <i class="bi bi-plus-circle me-2"></i>
          New Service
        </a>
      </div>
    </div>
  </section>

  <section class="service-stats-grid">
    <div class="service-stat-card service-stat-blue">
      <div class="service-stat-content">
        <div class="service-stat-icon">
          <i class="bi bi-clipboard2-pulse"></i>
        </div>

        <div>
          <div class="service-stat-value">{{ number_format($serviceCount) }}</div>
          <div class="service-stat-label">Total Services</div>
          <div class="service-stat-help">Services offered by this clinic</div>
        </div>
      </div>
    </div>

    <div class="service-stat-card service-stat-green">
      <div class="service-stat-content">
        <div class="service-stat-icon">
          <i class="bi bi-person-badge"></i>
        </div>

        <div>
          <div class="service-stat-value">{{ number_format($totalAssignedDoctors) }}</div>
          <div class="service-stat-label">Assigned Doctors</div>
          <div class="service-stat-help">Doctors linked to services</div>
        </div>
      </div>
    </div>

    <div class="service-stat-card service-stat-purple">
      <div class="service-stat-content">
        <div class="service-stat-icon">
          <i class="bi bi-people"></i>
        </div>

        <div>
          <div class="service-stat-value">{{ number_format($totalTodayQueue) }}</div>
          <div class="service-stat-label">Today’s Queue</div>
          <div class="service-stat-help">Patients waiting today</div>
        </div>
      </div>
    </div>
  </section>

  <section class="service-panel">
    <div class="service-panel-head">
      <h2 class="service-panel-title">
        <i class="bi bi-list-ul"></i>
        Services List
      </h2>

      <div class="service-toolbar">
        <span class="service-pill">
          <i class="bi bi-check2-circle"></i>
          {{ number_format($serviceCount) }} services
        </span>

        <div class="service-view-toggle">
          <button type="button" id="cardView" class="active" title="Card view">
            <i class="bi bi-grid-3x3-gap"></i>
          </button>

          <button type="button" id="tableView" title="Table view">
            <i class="bi bi-table"></i>
          </button>
        </div>
      </div>
    </div>

    <div id="serviceCardView" class="service-grid">
      @forelse($serviceItems as $service)
        @php
          $editUrl = $secUrl('secretary.services.edit', ['service' => $service->id], '/secretary/dashboard');
          $destroyUrl = $secUrl('secretary.services.destroy', ['service' => $service->id], '/secretary/dashboard');

          $doctorCount = isset($activeDoctorCounts) ? (int) ($activeDoctorCounts[$service->id] ?? 0) : 0;
          $todayQueue = isset($todayQueueCounts) ? (int) ($todayQueueCounts[$service->id] ?? 0) : 0;
          $duration = $service->pivot->duration_minutes ?? 30;
        @endphp

        <article class="service-card">
          <div class="service-card-top">
            <div class="d-flex gap-3 align-items-start">
              <div class="service-icon">
                <i class="bi bi-clipboard2-pulse"></i>
              </div>

              <div>
                <h3 class="service-name">{{ $service->name ?? 'Unnamed Service' }}</h3>
                <p class="service-desc">
                  {{ $service->description ?: 'No description provided for this service yet.' }}
                </p>
              </div>
            </div>

            <div class="dropdown">
              <button type="button" class="service-more-btn" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>

              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="{{ $editUrl }}">
                    <i class="bi bi-pencil-square me-2"></i>
                    Edit Service
                  </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                  <form method="POST"
                        action="{{ $destroyUrl }}"
                        data-confirm="Delete this service from this clinic?"
                        data-confirm-title="Delete Service"
                        data-confirm-btn="Delete">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="dropdown-item text-danger">
                      <i class="bi bi-trash me-2"></i>
                      Delete Service
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>

          <div class="service-card-body">
            <div class="service-mini-grid">
              <div class="service-mini-info">
                <small>Duration</small>
                <strong>{{ $duration }} mins</strong>
              </div>

              <div class="service-mini-info">
                <small>Doctors</small>
                <strong>{{ $doctorCount }} assigned</strong>
              </div>

              <div class="service-mini-info">
                <small>Today</small>
                <strong>{{ $todayQueue }} active</strong>
              </div>
            </div>

            <div class="service-note">
              <i class="bi bi-info-circle me-1"></i>
              Today’s queue only includes patients scheduled for today, so the active count stays clear.
            </div>

            <div class="service-actions">
              <a href="{{ $editUrl }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i>
                Edit
              </a>

              <form method="POST"
                    action="{{ $destroyUrl }}"
                    data-confirm="Delete this service from this clinic?"
                    data-confirm-title="Delete Service"
                    data-confirm-btn="Delete">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-outline-danger w-100">
                  <i class="bi bi-trash me-1"></i>
                  Delete
                </button>
              </form>
            </div>
          </div>
        </article>
      @empty
        <div class="service-empty">
          <div class="service-empty-icon">
            <i class="bi bi-clipboard-x"></i>
          </div>

          <h5 class="fw-bold">No services found</h5>
          <p class="mb-3">Create a service for {{ $clinic->name ?? 'your clinic' }}.</p>

          <a href="{{ $createUrl }}" class="btn btn-primary rounded-4 fw-bold">
            <i class="bi bi-plus-circle me-2"></i>
            Add Service
          </a>
        </div>
      @endforelse
    </div>

    <div id="serviceTableView" class="service-table-wrap">
      <div class="table-responsive">
        <table class="table service-table align-middle">
          <thead>
            <tr>
              <th>Service</th>
              <th>Description</th>
              <th>Duration</th>
              <th>Doctors</th>
              <th>Today’s Queue</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($serviceItems as $service)
              @php
                $editUrl = $secUrl('secretary.services.edit', ['service' => $service->id], '/secretary/dashboard');
                $destroyUrl = $secUrl('secretary.services.destroy', ['service' => $service->id], '/secretary/dashboard');

                $doctorCount = isset($activeDoctorCounts) ? (int) ($activeDoctorCounts[$service->id] ?? 0) : 0;
                $todayQueue = isset($todayQueueCounts) ? (int) ($todayQueueCounts[$service->id] ?? 0) : 0;
                $duration = $service->pivot->duration_minutes ?? 30;
              @endphp

              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="service-row-icon">
                      <i class="bi bi-clipboard2-pulse"></i>
                    </div>

                    <div>
                      <div class="fw-bold text-dark">{{ $service->name ?? 'Unnamed Service' }}</div>
                      <small class="text-muted">Service ID: {{ $service->id }}</small>
                    </div>
                  </div>
                </td>

                <td style="max-width: 360px;">
                  <span class="text-muted fw-semibold">
                    {{ $service->description ?: 'No description provided.' }}
                  </span>
                </td>

                <td>
                  <span class="service-pill">{{ $duration }} mins</span>
                </td>

                <td>
                  <span class="service-pill">{{ $doctorCount }} doctors</span>
                </td>

                <td>
                  <span class="service-pill">{{ $todayQueue }} active</span>
                </td>

                <td>
                  <div class="service-row-actions">
                    <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil-square me-1"></i>
                      Edit
                    </a>

                    <form method="POST"
                          action="{{ $destroyUrl }}"
                          data-confirm="Delete this service from this clinic?"
                          data-confirm-title="Delete Service"
                          data-confirm-btn="Delete">
                      @csrf
                      @method('DELETE')

                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6">
                  <div class="service-empty">
                    <div class="service-empty-icon">
                      <i class="bi bi-clipboard-x"></i>
                    </div>

                    <h5 class="fw-bold">No services found</h5>
                    <p class="mb-0">Create a service for this clinic.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if(isset($services) && method_exists($services, 'hasPages') && $services->hasPages())
      <div class="service-pagination">
        {{ $services->withQueryString()->links() }}
      </div>
    @endif
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const serviceCardView = document.getElementById('serviceCardView');
    const serviceTableView = document.getElementById('serviceTableView');

    if (!cardView || !tableView || !serviceCardView || !serviceTableView) {
      return;
    }

    cardView.addEventListener('click', function () {
      cardView.classList.add('active');
      tableView.classList.remove('active');

      serviceCardView.style.display = 'grid';
      serviceTableView.style.display = 'none';
    });

    tableView.addEventListener('click', function () {
      tableView.classList.add('active');
      cardView.classList.remove('active');

      serviceCardView.style.display = 'none';
      serviceTableView.style.display = 'block';
    });
  });
</script>
@endpush