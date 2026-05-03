@php
  $user = auth()->user();

  $safeDoctorRoute = function ($routeName, $fallback = '/doctor/dashboard') {
      if (!Route::has($routeName)) {
          return url($fallback);
      }

      try {
          return route($routeName);
      } catch (\Throwable $error) {
          return url($fallback);
      }
  };

  $dashboardUrl = $safeDoctorRoute('doctor.dashboard', '/doctor/dashboard');
  $queueUrl = $safeDoctorRoute('doctor.queue.index', '/doctor/queue');
  $schedulesUrl = $safeDoctorRoute('doctor.schedules.index', '/doctor/schedules');
  $appointmentsUrl = $safeDoctorRoute('doctor.appointments.index', '/doctor/dashboard');
  $profileUrl = Route::has('profile.edit') ? route('profile.edit') : '#';
@endphp

<style>
  body.doctor-sidebar-open {
    overflow: hidden;
  }

  .doctor-sidebar-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1040;
    background: rgba(8, 18, 32, 0.36);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.25s ease, visibility 0.25s ease;
  }

  body.doctor-sidebar-open .doctor-sidebar-backdrop {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .doctor-sidebar {
    width: 290px;
    max-width: 86vw;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    padding: 22px 18px;
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg, #061727 0%, #0b2035 100%);
    color: #ffffff;
    box-shadow: 12px 0 32px rgba(2, 8, 23, 0.24);
    transform: translateX(-110%);
    transition: transform 0.28s ease;
  }

  body.doctor-sidebar-open .doctor-sidebar {
    transform: translateX(0);
  }

  .doctor-sidebar-close {
    position: absolute;
    top: 14px;
    right: 12px;
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.10);
    color: #d8e6f4;
    font-size: 20px;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .doctor-sidebar-close:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
  }

  .doctor-sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 42px 20px 2px;
    margin-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    text-decoration: none;
  }

  .doctor-sidebar-logo {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    color: #ffffff;
    font-size: 28px;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    box-shadow: 0 12px 26px rgba(13, 110, 253, 0.32);
    flex: 0 0 auto;
  }

  .doctor-sidebar-title {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
  }

  .doctor-sidebar-title strong {
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .doctor-sidebar-title span {
    color: #9fb5cc;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
  }

  .doctor-sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .doctor-sidebar-link {
    width: 100%;
    min-height: 52px;
    border: 0;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 13px;
    color: #d8e6f4;
    background: transparent;
    text-decoration: none;
    font-size: 15px;
    font-weight: 700;
    padding: 0 14px;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .doctor-sidebar-link i {
    width: 25px;
    text-align: center;
    font-size: 22px;
    flex: 0 0 auto;
  }

  .doctor-sidebar-link span {
    white-space: nowrap;
  }

  .doctor-sidebar-link:hover,
  .doctor-sidebar-link.active {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.35);
  }

  .doctor-sidebar-section-label {
    margin: 12px 4px 2px;
    color: #8ea7bf;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .doctor-sidebar-bottom {
    margin-top: auto;
    padding-top: 18px;
  }

  .doctor-sidebar-bottom form {
    margin: 0;
  }

  .doctor-sidebar-bottom .doctor-sidebar-link {
    color: #fecaca;
  }

  .doctor-sidebar-bottom .doctor-sidebar-link:hover {
    background: rgba(220, 38, 38, 0.18);
    color: #ffffff;
    box-shadow: none;
  }
</style>

<div class="doctor-sidebar-backdrop" id="doctorSidebarBackdrop"></div>

<aside class="doctor-sidebar" id="doctorSidebar" aria-hidden="true">
  <button type="button" class="doctor-sidebar-close" id="doctorSidebarClose" aria-label="Close sidebar">
    <i class="bi bi-x-lg"></i>
  </button>

  <a href="{{ $dashboardUrl }}" class="doctor-sidebar-brand">
    <span class="doctor-sidebar-logo">
      <i class="bi bi-heart-pulse"></i>
    </span>

    <span class="doctor-sidebar-title">
      <strong>CliniQ</strong>
      <span>Doctor Menu</span>
    </span>
  </a>

  <nav class="doctor-sidebar-nav">
    <div class="doctor-sidebar-section-label">Main</div>

    <a href="{{ $dashboardUrl }}"
       class="doctor-sidebar-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <a href="{{ $queueUrl }}"
       class="doctor-sidebar-link {{ request()->routeIs('doctor.queue.*') ? 'active' : '' }}">
      <i class="bi bi-people"></i>
      <span>Queue</span>
    </a>

    <a href="{{ $schedulesUrl }}"
       class="doctor-sidebar-link {{ request()->routeIs('doctor.schedules.*') ? 'active' : '' }}">
      <i class="bi bi-calendar2-week"></i>
      <span>Schedules</span>
    </a>

    @if(Route::has('doctor.appointments.index'))
      <a href="{{ $appointmentsUrl }}"
         class="doctor-sidebar-link {{ request()->routeIs('doctor.appointments.*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check"></i>
        <span>Appointments</span>
      </a>
    @endif

    <div class="doctor-sidebar-section-label">Account</div>

    <a href="{{ $profileUrl }}"
       class="doctor-sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
      <i class="bi bi-person-gear"></i>
      <span>Profile Settings</span>
    </a>
  </nav>

  <div class="doctor-sidebar-bottom">
    <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
      @csrf

      <button type="submit" class="doctor-sidebar-link">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>

<script>
  (function () {
    function initDoctorSidebar() {
      const body = document.body;
      const sidebar = document.getElementById('doctorSidebar');
      const backdrop = document.getElementById('doctorSidebarBackdrop');
      const closeBtn = document.getElementById('doctorSidebarClose');
      const toggles = document.querySelectorAll('#doctorSidebarToggle, [data-doctor-sidebar-toggle]');

      if (!sidebar || !backdrop || !closeBtn || toggles.length === 0) return;

      function openSidebar() {
        body.classList.add('doctor-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'false');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));
      }

      function closeSidebar() {
        body.classList.remove('doctor-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'true');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
      }

      toggles.forEach(function (toggle) {
        if (toggle.dataset.doctorSidebarReady === 'true') return;

        toggle.dataset.doctorSidebarReady = 'true';

        toggle.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          body.classList.contains('doctor-sidebar-open') ? closeSidebar() : openSidebar();
        });
      });

      if (closeBtn.dataset.doctorSidebarCloseReady !== 'true') {
        closeBtn.dataset.doctorSidebarCloseReady = 'true';

        closeBtn.addEventListener('click', function (event) {
          event.preventDefault();
          closeSidebar();
        });
      }

      if (backdrop.dataset.doctorSidebarBackdropReady !== 'true') {
        backdrop.dataset.doctorSidebarBackdropReady = 'true';
        backdrop.addEventListener('click', closeSidebar);
      }

      if (document.documentElement.dataset.doctorSidebarEscReady !== 'true') {
        document.documentElement.dataset.doctorSidebarEscReady = 'true';

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') closeSidebar();
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initDoctorSidebar);
    } else {
      initDoctorSidebar();
    }
  })();
</script>