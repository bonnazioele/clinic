@php
  $user = auth()->user();
  $userName = $user->name ?? 'Secretary';
  $userInitial = strtoupper(substr($userName, 0, 1));

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

  $safeSecretaryRoute = function ($routeName, $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!Route::has($routeName)) {
          return url($fallback);
      }

      try {
          return route($routeName);
      } catch (\Throwable $firstError) {
          if ($secretaryClinicId) {
              try {
                  return route($routeName, ['clinic' => $secretaryClinicId]);
              } catch (\Throwable $secondError) {
                  return url($fallback);
              }
          }

          return url($fallback);
      }
  };

  $dashboardUrl = $safeSecretaryRoute('secretary.dashboard', '/secretary/dashboard');
  $appointmentsUrl = $safeSecretaryRoute('secretary.appointments.index', '/secretary/dashboard');
  $queueUrl = $safeSecretaryRoute('secretary.queue.index', '/secretary/dashboard');
  $doctorsUrl = $safeSecretaryRoute('secretary.doctors.index', '/secretary/dashboard');
  $servicesUrl = $safeSecretaryRoute('secretary.services.index', '/secretary/dashboard');
  $clinicSettingsUrl = $safeSecretaryRoute('secretary.clinic.edit', '/secretary/dashboard');
@endphp

<style>
.secretary-navbar-wrap {
  position: sticky;
  top: 0;
  z-index: 1030;
  padding: 0;
  margin: 0;
  width: 100%;
}

.secretary-navbar {
  width: 100%;
  min-height: 96px;
  padding: 16px 28px;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid rgba(255, 255, 255, 0.55);
  border-radius: 0 0 28px 28px;
  box-shadow:
    0 18px 45px rgba(15, 23, 42, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(22px) saturate(180%);
  -webkit-backdrop-filter: blur(22px) saturate(180%);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  position: relative;
  overflow: hidden;
}

.secretary-navbar::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  border-radius: 0 0 28px 28px;
  background:
    linear-gradient(
      135deg,
      rgba(13, 110, 253, 0.10),
      rgba(255, 255, 255, 0.20),
      rgba(56, 189, 248, 0.08)
    );
  z-index: -1;
}

.secretary-navbar .navbar-left,
.secretary-navbar .navbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.secretary-navbar .secretary-sidebar-toggle {
  width: 50px;
  height: 50px;
  border: 0;
  border-radius: 17px;
  background: #f8fafc;
  color: #0f172a;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.45rem;
  cursor: pointer;
  transition: 0.2s ease;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  flex-shrink: 0;
}

.secretary-navbar .secretary-sidebar-toggle:hover {
  background: #eef4ff;
  color: #2563eb;
  transform: translateY(-1px);
}

.secretary-navbar .navbar-brand {
  display: flex;
  align-items: center;
  gap: 16px;
  text-decoration: none;
}

.secretary-navbar .navbar-logo {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  background: linear-gradient(135deg, #0d6efd, #178bff);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.65rem;
  box-shadow: 0 14px 30px rgba(13, 110, 253, 0.28);
  flex-shrink: 0;
}

.secretary-navbar .navbar-brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1.05;
}

.secretary-navbar .navbar-brand-text strong {
  font-size: 1.95rem;
  font-weight: 900;
  color: #020617;
  letter-spacing: -0.05em;
}

.secretary-navbar .navbar-brand-text span {
  font-size: 0.86rem;
  color: #64748b;
  margin-top: 5px;
  font-weight: 700;
}

.secretary-navbar .navbar-icon-btn {
  width: 56px;
  height: 56px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: #ffffff;
  color: #0f172a;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  cursor: pointer;
  transition: 0.2s ease;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
}

.secretary-navbar .navbar-icon-btn:hover {
  background: #eef4ff;
  color: #2563eb;
  transform: translateY(-1px);
}

.secretary-navbar .navbar-profile {
  position: relative;
}

.secretary-navbar .profile-btn {
  min-height: 58px;
  padding: 8px 16px 8px 8px;
  border: 1px solid #dbe3ee;
  border-radius: 999px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  color: #0f172a;
  font-weight: 800;
  transition: 0.2s ease;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.secretary-navbar .profile-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.secretary-navbar .profile-btn.dropdown-toggle::after {
  display: none;
}

.secretary-navbar .profile-avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: #bfd0ff;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 900;
  flex-shrink: 0;
}

.secretary-navbar .profile-name {
  font-size: 1rem;
  color: #334155;
  white-space: nowrap;
}

.secretary-navbar .profile-chevron {
  font-size: 0.9rem;
  color: #64748b;
}

.secretary-navbar .dropdown-menu {
  margin-top: 12px !important;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 10px;
  min-width: 230px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
}

.secretary-navbar .dropdown-item {
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 700;
}

.secretary-navbar .dropdown-item:hover {
  background: #f8fafc;
}

@media (max-width: 992px) {
  .secretary-navbar {
    min-height: 82px;
    padding: 14px 18px;
    border-radius: 24px;
  }

  .secretary-navbar .navbar-brand-text strong {
    font-size: 1.65rem;
  }

  .secretary-navbar .navbar-brand-text span {
    font-size: 0.8rem;
  }

  .secretary-navbar .navbar-icon-btn {
    width: 50px;
    height: 50px;
  }

  .secretary-navbar .profile-btn {
    min-height: 52px;
  }
}

@media (max-width: 768px) {
  .secretary-navbar-wrap {
    top: 8px;
    padding: 8px 10px 0;
  }

  .secretary-navbar {
    min-height: 74px;
    padding: 12px 14px;
    border-radius: 22px;
    gap: 10px;
  }

  .secretary-navbar .navbar-left,
  .secretary-navbar .navbar-right {
    gap: 10px;
  }

  .secretary-navbar .secretary-sidebar-toggle {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    font-size: 1.25rem;
  }

  .secretary-navbar .navbar-logo {
    width: 46px;
    height: 46px;
    border-radius: 15px;
    font-size: 1.35rem;
  }

  .secretary-navbar .navbar-brand-text strong {
    font-size: 1.45rem;
  }

  .secretary-navbar .navbar-brand-text span {
    display: none;
  }

  .secretary-navbar .navbar-icon-btn {
    width: 44px;
    height: 44px;
    font-size: 1.1rem;
  }

  .secretary-navbar .profile-btn {
    min-height: 46px;
    padding: 4px 6px 4px 4px;
    gap: 8px;
  }

  .secretary-navbar .profile-avatar {
    width: 36px;
    height: 36px;
    font-size: 0.95rem;
  }

  .secretary-navbar .profile-name,
  .secretary-navbar .profile-chevron {
    display: none;
  }
}
</style>

<div class="secretary-navbar-wrap">
  <header class="secretary-navbar">
    <div class="navbar-left">
      <button type="button"
              class="secretary-sidebar-toggle"
              id="secretarySidebarToggle"
              data-secretary-sidebar-toggle
              aria-label="Open sidebar"
              aria-expanded="false">
        <i class="bi bi-list"></i>
      </button>

      <a href="{{ $dashboardUrl }}" class="navbar-brand">
        <span class="navbar-logo">
          <i class="bi bi-heart-pulse"></i>
        </span>

        <span class="navbar-brand-text">
          <strong>CliniQ</strong>
          <span>Secretary portal</span>
        </span>
      </a>
    </div>

    <div class="navbar-right">
      <button type="button" class="navbar-icon-btn" aria-label="Notifications">
        <i class="bi bi-bell"></i>
      </button>

      <div class="navbar-profile dropdown">
        <button class="profile-btn dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
          <span class="profile-avatar">{{ $userInitial }}</span>
          <span class="profile-name">{{ $userName }}</span>
          <i class="bi bi-chevron-down profile-chevron"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="{{ $dashboardUrl }}">
              <i class="bi bi-speedometer2 me-2"></i>
              Dashboard
            </a>
          </li>

          <li>
            <a class="dropdown-item" href="{{ $appointmentsUrl }}">
              <i class="bi bi-calendar-check me-2"></i>
              Appointments
            </a>
          </li>

          <li>
            <a class="dropdown-item" href="{{ $queueUrl }}">
              <i class="bi bi-people me-2"></i>
              Today’s Queue
            </a>
          </li>

          <li>
            <a class="dropdown-item" href="{{ $doctorsUrl }}">
              <i class="bi bi-person-badge me-2"></i>
              Doctors
            </a>
          </li>

          <li>
            <a class="dropdown-item" href="{{ $servicesUrl }}">
              <i class="bi bi-clipboard2-pulse me-2"></i>
              Services
            </a>
          </li>

          <li>
            <a class="dropdown-item" href="{{ $clinicSettingsUrl }}">
              <i class="bi bi-building-gear me-2"></i>
              Clinic Settings
            </a>
          </li>

          <li><hr class="dropdown-divider"></li>

          <li>
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </header>
</div>