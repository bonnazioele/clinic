@extends('layouts.app')

@section('title', 'Manage Doctors')

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

      try {
          return route($routeName, $params);
      } catch (\Throwable $firstError) {
          if ($secretaryClinicId) {
              try {
                  return route($routeName, array_merge(['clinic' => $secretaryClinicId], (array) $params));
              } catch (\Throwable $secondError) {
                  return url($fallback);
              }
          }

          return url($fallback);
      }
  };

  $doctorItems = collect();

  if (isset($doctors)) {
      if (method_exists($doctors, 'getCollection')) {
          $doctorItems = $doctors->getCollection();
      } elseif ($doctors instanceof \Illuminate\Support\Collection) {
          $doctorItems = $doctors;
      } elseif (is_array($doctors)) {
          $doctorItems = collect($doctors);
      }
  }

  $doctorItems = $doctorItems->filter(function ($doctor) {
      return is_object($doctor);
  })->values();

  $doctorCount = (int) ($doctorStats['total'] ?? (
      isset($doctors) && method_exists($doctors, 'total') ? $doctors->total() : $doctorItems->count()
  ));

  $withServicesCount = (int) ($doctorStats['with_services'] ?? $doctorItems->filter(function ($doctor) {
      return isset($doctor->services) && $doctor->services && method_exists($doctor->services, 'count') && $doctor->services->count() > 0;
  })->count());

  $withoutServicesCount = (int) ($doctorStats['without_services'] ?? max(0, $doctorCount - $withServicesCount));

  $matchingDoctorCount = isset($doctors) && method_exists($doctors, 'total')
      ? $doctors->total()
      : $doctorItems->count();

  $activeServiceScope = request('service_scope');
  $doctorScopeUrl = function (?string $scope) use ($secUrl) {
      $query = request()->except('page');

      if ($scope) {
          $query['service_scope'] = $scope;
      } else {
          unset($query['service_scope']);
      }

      return $secUrl('secretary.doctors.index') . (count($query) ? '?' . http_build_query($query) : '');
  };
@endphp

<style>
  .doctor-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .doctor-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255,255,255,.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
  }

  .doctor-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .doctor-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }

  .doctor-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .doctor-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .doctor-subtitle {
    margin: .3rem 0 0;
    font-size: .95rem;
    font-weight: 650;
    opacity: .94;
  }

  .doctor-hero .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .doctor-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .doctor-stat-card {
    min-height: 138px;
    border-radius: 22px;
    padding: 1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
    display: block;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, outline-color .18s ease;
  }

  .doctor-stat-card:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 18px 42px rgba(15,23,42,.12);
  }

  .doctor-stat-yellow:hover {
    color: #162033;
  }

  .doctor-stat-card.active {
    outline: 4px solid rgba(13,110,253,.28);
    outline-offset: 3px;
  }

  .doctor-stat-card::after {
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

  .doctor-stat-blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .doctor-stat-green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .doctor-stat-yellow {
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
  }

  .doctor-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }

  .doctor-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.45rem;
    flex: 0 0 52px;
  }

  .doctor-stat-value {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -.055em;
    margin-bottom: .4rem;
  }

  .doctor-stat-label {
    font-size: .88rem;
    font-weight: 900;
  }

  .doctor-stat-help {
    margin-top: .15rem;
    font-size: .78rem;
    font-weight: 650;
    opacity: .9;
  }

  .doctor-panel {
    border-radius: 24px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .doctor-panel-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
  }

  .doctor-panel-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .doctor-panel-title i {
    color: #0d6efd;
  }

  .doctor-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .42rem .72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .76rem;
    font-weight: 900;
  }

  .doctor-table {
    margin: 0;
  }

  .doctor-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem;
  }

  .doctor-table tbody td {
    padding: 1rem;
    vertical-align: middle;
  }

  .doctor-avatar {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-weight: 900;
    flex: 0 0 48px;
  }

  .doctor-name {
    font-weight: 900;
    color: #0f172a;
  }

  .doctor-contact {
    color: #64748b;
    font-size: .8rem;
    font-weight: 650;
  }

  .doctor-service-tags {
    display: flex;
    gap: .35rem;
    flex-wrap: wrap;
  }

  .doctor-service-tag {
    border-radius: 999px;
    padding: .32rem .55rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .7rem;
    font-weight: 900;
  }

  .doctor-actions {
    display: flex;
    justify-content: flex-end;
    gap: .45rem;
    flex-wrap: wrap;
  }

  .doctor-actions .btn {
    border-radius: 12px;
    font-weight: 850;
  }

  .doctor-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
  }

  .doctor-empty-icon {
    width: 78px;
    height: 78px;
    margin: 0 auto 1rem;
    border-radius: 24px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 2.25rem;
  }

  .doctor-cards {
    display: none;
    padding: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .doctor-card {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 10px 26px rgba(15,23,42,.045);
  }

  .doctor-card-top {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    margin-bottom: .9rem;
  }

  .doctor-pagination {
    padding: 1rem;
    border-top: 1px solid #edf2f7;
    background: #f8fafc;
  }

  @media (max-width: 992px) {
    .doctor-stats-grid {
      grid-template-columns: 1fr;
    }

    .doctor-table-wrap {
      display: none;
    }

    .doctor-cards {
      display: grid;
    }
  }

  @media (max-width: 768px) {
    .doctor-page {
      width: 100%;
    }

    .doctor-hero {
      padding: 1rem;
      border-radius: 22px;
    }

    .doctor-cards {
      grid-template-columns: 1fr;
      padding: .85rem;
    }

    .doctor-actions,
    .doctor-actions .btn,
    .doctor-actions form {
      width: 100%;
    }
  }

  .doctor-service-duration {
    display: inline-flex;
    align-items: center;
    background: rgba(13,110,253,.14);
    color: #1d4ed8;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 900;
    padding: .1rem .4rem;
    margin-left: .3rem;
  }

</style>

<div class="doctor-page">
  @include('partials.alerts')

  <section class="doctor-hero">
    <div class="doctor-hero-row">
      <div class="doctor-title-wrap">
        <div class="doctor-title-icon">
          <i class="bi bi-person-badge"></i>
        </div>

        <div>
          <h1 class="doctor-title">Manage Doctors</h1>
          <p class="doctor-subtitle">
            Add, update, and manage doctors assigned to your clinic services.
          </p>
        </div>
      </div>

      <a href="{{ $secUrl('secretary.doctors.create') }}" class="btn btn-light text-primary">
        <i class="bi bi-person-plus me-2"></i>
        New Doctor
      </a>
    </div>
  </section>

  <section class="doctor-stats-grid">
    <a href="{{ $doctorScopeUrl(null) }}"
       class="doctor-stat-card doctor-stat-blue {{ empty($activeServiceScope) ? 'active' : '' }}"
       aria-label="Show all doctors">
      <div class="doctor-stat-content">
        <div class="doctor-stat-icon">
          <i class="bi bi-people"></i>
        </div>

        <div>
          <div class="doctor-stat-value">{{ number_format($doctorCount) }}</div>
          <div class="doctor-stat-label">Total Doctors</div>
          <div class="doctor-stat-help">Doctors listed in this view</div>
        </div>
      </div>
    </a>

    <a href="{{ $doctorScopeUrl('with_services') }}"
       class="doctor-stat-card doctor-stat-green {{ $activeServiceScope === 'with_services' ? 'active' : '' }}"
       aria-label="Show doctors with services">
      <div class="doctor-stat-content">
        <div class="doctor-stat-icon">
          <i class="bi bi-clipboard2-pulse"></i>
        </div>

        <div>
          <div class="doctor-stat-value">{{ number_format($withServicesCount) }}</div>
          <div class="doctor-stat-label">With Services</div>
          <div class="doctor-stat-help">Can receive appointments</div>
        </div>
      </div>
    </a>

    <a href="{{ $doctorScopeUrl('needs_setup') }}"
       class="doctor-stat-card doctor-stat-yellow {{ $activeServiceScope === 'needs_setup' ? 'active' : '' }}"
       aria-label="Show doctors needing setup">
      <div class="doctor-stat-content">
        <div class="doctor-stat-icon">
          <i class="bi bi-exclamation-circle"></i>
        </div>

        <div>
          <div class="doctor-stat-value">{{ number_format($withoutServicesCount) }}</div>
          <div class="doctor-stat-label">Needs Setup</div>
          <div class="doctor-stat-help">No services assigned yet</div>
        </div>
      </div>
    </a>
  </section>

  <section class="doctor-panel">
    <div class="doctor-panel-head">
      <h2 class="doctor-panel-title">
        <i class="bi bi-list-ul"></i>
        Doctors List
      </h2>

      <span class="doctor-pill">
        <i class="bi bi-check2-circle"></i>
        {{ number_format($matchingDoctorCount) }} doctors
      </span>

      @if($activeServiceScope)
        <a href="{{ $doctorScopeUrl(null) }}" class="doctor-pill text-decoration-none">
          <i class="bi bi-funnel"></i>
          {{ $activeServiceScope === 'with_services' ? 'With services' : 'Needs setup' }}
          <i class="bi bi-x-lg"></i>
        </a>
      @endif
    </div>

    @if($doctorItems->isEmpty())
      <div class="doctor-empty">
        <div class="doctor-empty-icon">
          <i class="bi bi-person-plus"></i>
        </div>

        <h5 class="fw-bold">No doctors added yet</h5>
        <p class="mb-3">Start by adding your first doctor profile.</p>

        <a href="{{ $secUrl('secretary.doctors.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-2"></i>
          Add Doctor
        </a>
      </div>
    @else
      <div class="doctor-table-wrap table-responsive">
        <table class="table doctor-table table-hover align-middle">
          <thead>
            <tr>
              <th>Doctor</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Services</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>

          <tbody>
            @foreach($doctorItems as $d)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="doctor-avatar">
                      {{ strtoupper(substr($d->name ?? 'D', 0, 1)) }}
                    </div>

                    <div>
                      <div class="doctor-name">Dr. {{ $d->name ?? 'Unknown Doctor' }}</div>
                      <div class="doctor-contact">
                        {{ $d->address ?: 'No address listed' }}
                      </div>
                    </div>
                  </div>
                </td>

                <td>{{ $d->email ?? 'N/A' }}</td>
                <td>{{ $d->phone ?: 'N/A' }}</td>

                <td>
                  @if(isset($d->services) && $d->services && $d->services->count())
                    <div class="doctor-service-tags">
                      @foreach($d->services->take(3) as $service)
                        <span class="doctor-service-tag">
                          {{ $service->name }}
                          @if($service->pivot?->duration_minutes)
                            <span class="doctor-service-duration">{{ $service->pivot->duration_minutes }}m</span>
                          @endif
                        </span>
                      @endforeach

                      @if($d->services->count() > 3)
                        <span class="doctor-service-tag">+{{ $d->services->count() - 3 }} more</span>
                      @endif
                    </div>
                  @else
                    <span class="text-muted">No services assigned</span>
                  @endif
                </td>

                <td>
                  <div class="doctor-actions">
                    <a href="{{ $secUrl('secretary.doctors.show', ['doctor' => $d->id]) }}"
                       class="btn btn-sm btn-outline-info">
                      <i class="bi bi-eye me-1"></i>
                      View
                    </a>

                    <a href="{{ $secUrl('secretary.doctors.edit', ['doctor' => $d->id]) }}"
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil me-1"></i>
                      Edit
                    </a>

                    <form method="POST"
                          action="{{ $secUrl('secretary.doctors.destroy', ['doctor' => $d->id]) }}"
                          data-confirm="Unassign this doctor from the active clinic? Their clinic schedules and service assignments here will be removed, but appointment history will stay intact."
                          data-confirm-title="Remove Doctor"
                          data-confirm-btn="Remove">
                      @csrf
                      @method('DELETE')

                      <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-person-dash me-1"></i>
                        Remove
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="doctor-cards">
        @foreach($doctorItems as $d)
          <article class="doctor-card">
            <div class="doctor-card-top">
              <div class="doctor-avatar">
                {{ strtoupper(substr($d->name ?? 'D', 0, 1)) }}
              </div>

              <div>
                <h5 class="mb-1 fw-bold">Dr. {{ $d->name ?? 'Unknown Doctor' }}</h5>
                <div class="doctor-contact">{{ $d->email ?? 'No email' }}</div>
                <div class="doctor-contact">{{ $d->phone ?: 'No phone' }}</div>
              </div>
            </div>

            <div class="mb-3">
              @if(isset($d->services) && $d->services && $d->services->count())
                <div class="doctor-service-tags">
                  @foreach($d->services as $service)
                    <span class="doctor-service-tag">
                      {{ $service->name }}
                      @if($service->pivot?->duration_minutes)
                        <span class="doctor-service-duration">{{ $service->pivot->duration_minutes }}m</span>
                      @endif
                    </span>
                  @endforeach
                </div>
              @else
                <span class="text-muted">No services assigned</span>
              @endif
            </div>

            <div class="doctor-actions">
              <a href="{{ $secUrl('secretary.doctors.show', ['doctor' => $d->id]) }}"
                 class="btn btn-sm btn-outline-info">
                <i class="bi bi-eye me-1"></i>
                View
              </a>

              <a href="{{ $secUrl('secretary.doctors.edit', ['doctor' => $d->id]) }}"
                 class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>
                Edit
              </a>
            </div>
          </article>
        @endforeach
      </div>

      @if(isset($doctors) && method_exists($doctors, 'links'))
        <div class="doctor-pagination">
          {{ $doctors->appends(request()->except('page'))->links() }}
        </div>
      @endif
    @endif
  </section>
</div>
@endsection