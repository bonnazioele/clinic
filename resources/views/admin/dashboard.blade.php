@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $registeredClinicsCount = is_numeric($registeredClinics ?? null)
        ? $registeredClinics
        : collect($registeredClinics ?? $clinics ?? [])->count();

    $servicesCount = is_numeric($services ?? null)
        ? $services
        : collect($services ?? [])->count();

    $usersCount = is_numeric($users ?? null)
        ? $users
        : collect($users ?? [])->count();

    $pendingApplications = collect($pendingClinics ?? $pendingApplications ?? $clinicApplications ?? $applications ?? []);

    $pendingApplicationsCount = is_numeric($pendingCount ?? null)
        ? $pendingCount
        : $pendingApplications->count();

    $clinicsUrl = Route::has('admin.clinics.index') ? route('admin.clinics.index') : '#';
    $createClinicUrl = Route::has('admin.clinics.create') ? route('admin.clinics.create') : '#';
    $servicesUrl = Route::has('admin.services.index') ? route('admin.services.index') : '#';
    $usersUrl = Route::has('admin.users.index') ? route('admin.users.index') : '#';

    $applicationsUrl = Route::has('admin.clinics.applications')
        ? route('admin.clinics.applications')
        : $clinicsUrl;
@endphp

<style>
  .admin-dashboard-page {
    width: 100%;
  }

  .admin-dashboard-hero {
    position: relative;
    overflow: hidden;
    border-radius: 34px;
    padding: 30px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background:
      radial-gradient(circle at top left, rgba(22, 119, 255, 0.16), transparent 30%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
      rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
  }

  .admin-dashboard-hero::after {
    content: "";
    position: absolute;
    right: -80px;
    bottom: -90px;
    width: 260px;
    height: 260px;
    border-radius: 999px;
    background: rgba(22, 119, 255, 0.12);
    pointer-events: none;
  }

  .admin-hero-icon {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    background: linear-gradient(135deg, #1677ff, #06b6d4);
    font-size: 2.1rem;
    box-shadow: 0 18px 34px rgba(22, 119, 255, 0.28);
    flex-shrink: 0;
  }

  .admin-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 8px 14px;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 0.82rem;
    font-weight: 900;
  }

  .admin-hero-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 3rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .admin-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 1rem;
  }

  .admin-stat-card {
    height: 100%;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    padding: 22px;
    display: block;
    color: inherit;
    transition: 0.2s ease;
  }

  .admin-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 22px 50px rgba(15, 23, 42, 0.11);
  }

  .admin-stat-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
  }

  .admin-stat-label {
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 900;
    margin-bottom: 6px;
  }

  .admin-stat-value {
    color: #0f172a;
    font-size: 2.45rem;
    font-weight: 900;
    line-height: 1;
    margin: 0;
  }

  .admin-stat-desc {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
  }

  .admin-stat-icon {
    width: 58px;
    height: 58px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  .admin-stat-blue .admin-stat-icon {
    background: rgba(22, 119, 255, 0.12);
    color: #1677ff;
  }

  .admin-stat-green .admin-stat-icon {
    background: rgba(16, 185, 129, 0.14);
    color: #047857;
  }

  .admin-stat-yellow .admin-stat-icon {
    background: rgba(245, 158, 11, 0.18);
    color: #92400e;
  }

  .admin-stat-gray .admin-stat-icon {
    background: rgba(100, 116, 139, 0.14);
    color: #475569;
  }

  .admin-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
    height: 100%;
  }

  .admin-panel-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background:
      radial-gradient(circle at top left, rgba(22, 119, 255, 0.12), transparent 32%),
      linear-gradient(180deg, #ffffff, #f8fafc);
  }

  .admin-panel-title {
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .admin-panel-title i {
    color: #1677ff;
  }

  .admin-panel-subtitle {
    color: #64748b;
    font-size: 0.92rem;
    margin: 6px 0 0;
  }

  .admin-panel-body {
    padding: 22px 24px;
  }

  .admin-action-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .admin-action-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    background: #ffffff;
    padding: 18px;
    color: #0f172a;
    transition: 0.2s ease;
  }

  .admin-action-card:hover {
    background: #f8fbff;
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
  }

  .admin-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 1.25rem;
    margin-bottom: 12px;
  }

  .admin-action-title {
    font-weight: 900;
    margin-bottom: 4px;
  }

  .admin-action-text {
    color: #64748b;
    font-size: 0.86rem;
    margin: 0;
  }

  .admin-empty {
    border: 1px dashed rgba(148, 163, 184, 0.55);
    border-radius: 24px;
    background: #f8fafc;
    padding: 34px 20px;
    text-align: center;
  }

  .admin-empty-icon {
    width: 70px;
    height: 70px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 2rem;
    margin-bottom: 14px;
  }

  .application-item {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    background: #ffffff;
    padding: 16px;
    transition: 0.2s ease;
  }

  .application-item + .application-item {
    margin-top: 12px;
  }

  .application-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
  }

  .application-name {
    color: #0f172a;
    font-weight: 900;
    margin-bottom: 4px;
  }

  .application-meta {
    color: #64748b;
    font-size: 0.86rem;
    margin: 0;
  }

  @media (max-width: 991.98px) {
    .admin-action-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .admin-dashboard-hero {
      padding: 22px;
      border-radius: 24px;
    }

    .admin-hero-icon {
      width: 62px;
      height: 62px;
      border-radius: 20px;
      font-size: 1.75rem;
    }

    .admin-stat-card,
    .admin-panel {
      border-radius: 24px;
    }
  }
</style>

<div class="admin-dashboard-page">

  <div class="admin-dashboard-hero mb-4">
    <div class="row g-4 align-items-center">
      <div class="col-lg-8">
        <div class="d-flex align-items-start gap-3 gap-md-4">
          <div class="admin-hero-icon">
            <i class="bi bi-shield-check"></i>
          </div>

          <div>
            <div class="admin-hero-badge mb-3">
              <i class="bi bi-speedometer2"></i>
              Administrator Dashboard
            </div>

            <h1 class="admin-hero-title">
              Welcome back, {{ auth()->user()->name }}!
            </h1>

            <p class="admin-hero-text">
              Manage clinic registrations, services, users, and pending clinic applications from your admin control center.
            </p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="admin-panel" style="border-radius:24px;">
          <div class="admin-panel-body">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div>
                <div class="text-muted fw-bold small mb-1">Pending Review</div>
                <div class="fs-2 fw-black fw-bold text-dark">{{ $pendingApplicationsCount }}</div>
                <div class="text-muted small">Clinic applications waiting for admin action</div>
              </div>

              <span class="admin-stat-icon" style="background:rgba(245,158,11,0.18);color:#92400e;">
                <i class="bi bi-hourglass-split"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <a href="{{ $clinicsUrl }}" class="admin-stat-card admin-stat-blue">
        <div class="admin-stat-top">
          <div>
            <div class="admin-stat-label">Registered Clinics</div>
            <h2 class="admin-stat-value">{{ $registeredClinicsCount }}</h2>
          </div>
          <span class="admin-stat-icon">
            <i class="bi bi-building"></i>
          </span>
        </div>
        <p class="admin-stat-desc">Total clinics registered in the system.</p>
      </a>
    </div>

    <div class="col-sm-6 col-xl-3">
      <a href="{{ $servicesUrl }}" class="admin-stat-card admin-stat-green">
        <div class="admin-stat-top">
          <div>
            <div class="admin-stat-label">Services</div>
            <h2 class="admin-stat-value">{{ $servicesCount }}</h2>
          </div>
          <span class="admin-stat-icon">
            <i class="bi bi-clipboard2-pulse"></i>
          </span>
        </div>
        <p class="admin-stat-desc">Services available across clinics.</p>
      </a>
    </div>

    <div class="col-sm-6 col-xl-3">
      <a href="{{ $usersUrl }}" class="admin-stat-card admin-stat-yellow">
        <div class="admin-stat-top">
          <div>
            <div class="admin-stat-label">Users</div>
            <h2 class="admin-stat-value">{{ $usersCount }}</h2>
          </div>
          <span class="admin-stat-icon">
            <i class="bi bi-people"></i>
          </span>
        </div>
        <p class="admin-stat-desc">Total platform accounts.</p>
      </a>
    </div>

    <div class="col-sm-6 col-xl-3">
      <a href="#pending-clinic-applicants" class="admin-stat-card admin-stat-gray">
        <div class="admin-stat-top">
          <div>
            <div class="admin-stat-label">Pending Clinics</div>
            <h2 class="admin-stat-value">{{ $pendingApplicationsCount }}</h2>
          </div>
          <span class="admin-stat-icon">
            <i class="bi bi-file-earmark-check"></i>
          </span>
        </div>
        <p class="admin-stat-desc">Applications needing review.</p>
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-xl-5">
      <div class="admin-panel">
        <div class="admin-panel-header">
          <h3 class="admin-panel-title">
            <i class="bi bi-lightning-charge"></i>
            Quick Actions
          </h3>
          <p class="admin-panel-subtitle">
            Common admin tasks for platform management.
          </p>
        </div>

        <div class="admin-panel-body">
          <div class="admin-action-grid">
            <a href="{{ $clinicsUrl }}" class="admin-action-card">
              <span class="admin-action-icon">
                <i class="bi bi-building"></i>
              </span>
              <div class="admin-action-title">Manage Clinics</div>
              <p class="admin-action-text">View, edit, and review clinic records.</p>
            </a>

            <a href="{{ $createClinicUrl }}" class="admin-action-card">
              <span class="admin-action-icon">
                <i class="bi bi-building-add"></i>
              </span>
              <div class="admin-action-title">Add Clinic</div>
              <p class="admin-action-text">Create a new clinic record manually.</p>
            </a>

            <a href="{{ $servicesUrl }}" class="admin-action-card">
              <span class="admin-action-icon">
                <i class="bi bi-clipboard2-pulse"></i>
              </span>
              <div class="admin-action-title">Services</div>
              <p class="admin-action-text">Manage clinic services and service categories.</p>
            </a>

            <a href="{{ $usersUrl }}" class="admin-action-card">
              <span class="admin-action-icon">
                <i class="bi bi-person-gear"></i>
              </span>
              <div class="admin-action-title">Users</div>
              <p class="admin-action-text">Review users, roles, and platform accounts.</p>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-7">
      <div class="admin-panel" id="pending-clinic-applicants">
        <div class="admin-panel-header">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
              <h3 class="admin-panel-title">
                <i class="bi bi-file-earmark-medical"></i>
                Pending Clinic Applications
              </h3>
              <p class="admin-panel-subtitle">
                Clinic registration requests that may need approval or review.
              </p>
            </div>

            <a href="{{ $applicationsUrl }}" class="btn btn-primary">
              <i class="bi bi-arrow-right me-1"></i>
              View All
            </a>
          </div>
        </div>

        <div class="admin-panel-body">
          @if($pendingApplications->count())
            @foreach($pendingApplications->take(5) as $application)
              @php
                $applicationName = $application->clinic_name
                    ?? $application->name
                    ?? $application->clinic?->name
                    ?? 'Clinic Application';

                $applicationEmail = $application->contact_person_email
                    ?? $application->email
                    ?? $application->user?->email
                    ?? null;

                $applicationDate = $application->created_at ?? null;

                $detailUrl = '#';

                if (Route::has('admin.clinics.application-detail')) {
                    try {
                        $detailUrl = route('admin.clinics.application-detail', $application);
                    } catch (\Throwable $e) {
                        $detailUrl = $applicationsUrl;
                    }
                } elseif (Route::has('admin.application-detail')) {
                    try {
                        $detailUrl = route('admin.application-detail', $application);
                    } catch (\Throwable $e) {
                        $detailUrl = $applicationsUrl;
                    }
                } else {
                    $detailUrl = $applicationsUrl;
                }
              @endphp

              <a href="{{ $detailUrl }}" class="application-item d-block">
                <div class="d-flex align-items-center justify-content-between gap-3">
                  <div class="min-w-0">
                    <div class="application-name text-truncate">
                      {{ $applicationName }}
                    </div>

                    <p class="application-meta">
                      @if($applicationEmail)
                        <i class="bi bi-envelope me-1"></i>{{ $applicationEmail }}
                      @else
                        <i class="bi bi-file-earmark-text me-1"></i>Application pending review
                      @endif

                      @if($applicationDate)
                        <span class="ms-2">
                          <i class="bi bi-clock me-1"></i>{{ $applicationDate->diffForHumans() }}
                        </span>
                      @endif
                    </p>
                  </div>

                  <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                    Pending
                  </span>
                </div>
              </a>
            @endforeach

            @if($pendingApplications->count() > 5)
              <div class="text-center mt-3">
                <a href="{{ $applicationsUrl }}" class="btn btn-outline-primary">
                  View {{ $pendingApplications->count() - 5 }} more
                </a>
              </div>
            @endif
          @else
            <div class="admin-empty">
              <div class="admin-empty-icon">
                <i class="bi bi-check2-circle"></i>
              </div>
              <h5 class="fw-bold text-dark mb-1">No pending applications</h5>
              <p class="text-muted mb-0">
                All clinic applications are currently reviewed.
              </p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection