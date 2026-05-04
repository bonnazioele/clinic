<style>
  body.patient-sidebar-open {
    overflow: hidden;
  }

  .patient-sidebar-backdrop {
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

  body.patient-sidebar-open .patient-sidebar-backdrop {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .patient-sidebar {
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

  body.patient-sidebar-open .patient-sidebar {
    transform: translateX(0);
  }

  .sidebar-close {
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

  .sidebar-close:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
  }

  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 42px 20px 2px;
    margin-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    text-decoration: none;
  }

  .sidebar-logo {
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

  .sidebar-title {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
  }

  .sidebar-title strong {
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .sidebar-title span {
    color: #9fb5cc;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .sidebar-link {
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

  .sidebar-link i {
    width: 25px;
    text-align: center;
    font-size: 22px;
    flex: 0 0 auto;
  }

  .sidebar-link span {
    white-space: nowrap;
  }

  .sidebar-link:hover,
  .sidebar-link.active {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.35);
  }

  .sidebar-bottom {
    margin-top: auto;
    padding-top: 18px;
  }

  .sidebar-bottom form {
    margin: 0;
  }

  .sidebar-bottom .sidebar-link {
    color: #fecaca;
  }

  .sidebar-bottom .sidebar-link:hover {
    background: rgba(220, 38, 38, 0.18);
    color: #ffffff;
    box-shadow: none;
  }
</style>

<div class="patient-sidebar-backdrop" id="patientSidebarBackdrop"></div>

<aside class="patient-sidebar" id="patientSidebar" aria-hidden="true">
  <button type="button" class="sidebar-close" id="patientSidebarClose" aria-label="Close sidebar">
    <i class="bi bi-x-lg"></i>
  </button>

  <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="sidebar-brand">
    <span class="sidebar-logo">
      <i class="bi bi-heart-pulse"></i>
    </span>
    <span class="sidebar-title">
      <strong>CliniQ</strong>
      <span>Patient Menu</span>
    </span>
  </a>

  <nav class="sidebar-nav">
    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="bi bi-house-heart"></i>
      <span>Home</span>
    </a>

    <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}"
       class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
      <i class="bi bi-calendar-check"></i>
      <span>Appointments</span>
    </a>

    <a href="{{ Route::has('queue.status') ? route('queue.status') : url('/queue/status') }}"
       class="sidebar-link {{ request()->routeIs('queue.*') ? 'active' : '' }}">
      <i class="bi bi-people"></i>
      <span>Queue Status</span>
    </a>

    <a href="{{ Route::has('clinics.index') ? route('clinics.index') : url('/clinics') }}"
       class="sidebar-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}">
      <i class="bi bi-hospital"></i>
      <span>Clinics</span>
    </a>

  </nav>

  <div class="sidebar-bottom">
    <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
      @csrf
      <button type="submit" class="sidebar-link">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>

<script>
  (function () {
    function initPatientSidebar() {
      const body = document.body;
      const sidebar = document.getElementById('patientSidebar');
      const backdrop = document.getElementById('patientSidebarBackdrop');
      const closeBtn = document.getElementById('patientSidebarClose');
      const toggles = document.querySelectorAll('#sidebarToggle, .sidebar-toggle, [data-sidebar-toggle]');

      if (!sidebar || !backdrop || !closeBtn || toggles.length === 0) {
        return;
      }

      function openSidebar() {
        body.classList.add('patient-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'false');
        toggles.forEach(function (toggle) {
          toggle.setAttribute('aria-expanded', 'true');
        });
      }

      function closeSidebar() {
        body.classList.remove('patient-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'true');
        toggles.forEach(function (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        });
      }

      toggles.forEach(function (toggle) {
        if (toggle.dataset.sidebarReady === 'true') {
          return;
        }

        toggle.dataset.sidebarReady = 'true';

        toggle.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          if (body.classList.contains('patient-sidebar-open')) {
            closeSidebar();
          } else {
            openSidebar();
          }
        });
      });

      if (closeBtn.dataset.sidebarCloseReady !== 'true') {
        closeBtn.dataset.sidebarCloseReady = 'true';
        closeBtn.addEventListener('click', function (event) {
          event.preventDefault();
          closeSidebar();
        });
      }

      if (backdrop.dataset.sidebarBackdropReady !== 'true') {
        backdrop.dataset.sidebarBackdropReady = 'true';
        backdrop.addEventListener('click', closeSidebar);
      }

      if (document.documentElement.dataset.sidebarEscReady !== 'true') {
        document.documentElement.dataset.sidebarEscReady = 'true';
        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            closeSidebar();
          }
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initPatientSidebar);
    } else {
      initPatientSidebar();
    }
  })();
</script>
