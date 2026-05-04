@php
  $safeAdminRoute = function ($routeName, $fallback = '/admin/dashboard') {
      if (!Route::has($routeName)) {
          return url($fallback);
      }

      try {
          return route($routeName);
      } catch (\Throwable $e) {
          return url($fallback);
      }
  };

  $dashboardUrl = $safeAdminRoute('admin.dashboard', '/admin/dashboard');
  $clinicsUrl = $safeAdminRoute('admin.clinics.index', '/admin/clinics');
  $createClinicUrl = $safeAdminRoute('admin.clinics.create', '/admin/clinics/create');
  $applicationsUrl = Route::has('admin.clinics.applications')
      ? route('admin.clinics.applications')
      : $clinicsUrl;
@endphp

<style>
  body.admin-sidebar-open {
    overflow: hidden;
  }

  .admin-sidebar-backdrop {
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

  body.admin-sidebar-open .admin-sidebar-backdrop {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .admin-sidebar {
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

  body.admin-sidebar-open .admin-sidebar {
    transform: translateX(0);
  }

  .admin-sidebar-close {
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

  .admin-sidebar-close:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
  }

  .admin-sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 42px 20px 2px;
    margin-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    text-decoration: none;
  }

  .admin-sidebar-logo {
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

  .admin-sidebar-title {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
  }

  .admin-sidebar-title strong {
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .admin-sidebar-title span {
    color: #9fb5cc;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
  }

  .admin-sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .admin-sidebar-link {
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

  .admin-sidebar-link i {
    width: 25px;
    text-align: center;
    font-size: 22px;
    flex: 0 0 auto;
  }

  .admin-sidebar-link span {
    white-space: nowrap;
  }

  .admin-sidebar-link:hover,
  .admin-sidebar-link.active {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.35);
  }

  .admin-sidebar-section-label {
    margin: 12px 4px 2px;
    color: #8ea7bf;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .admin-sidebar-bottom {
    margin-top: auto;
    padding-top: 18px;
  }

  .admin-sidebar-bottom form {
    margin: 0;
  }

  .admin-sidebar-bottom .admin-sidebar-link {
    color: #fecaca;
  }

  .admin-sidebar-bottom .admin-sidebar-link:hover {
    background: rgba(220, 38, 38, 0.18);
    color: #ffffff;
    box-shadow: none;
  }
</style>

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<aside class="admin-sidebar" id="adminSidebar" aria-hidden="true">
  <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close sidebar">
    <i class="bi bi-x-lg"></i>
  </button>

  <a href="{{ $dashboardUrl }}" class="admin-sidebar-brand">
    <span class="admin-sidebar-logo">
      <i class="bi bi-shield-check"></i>
    </span>

    <span class="admin-sidebar-title">
      <strong>CliniQ</strong>
      <span>Admin Menu</span>
    </span>
  </a>

  <nav class="admin-sidebar-nav">
    <div class="admin-sidebar-section-label">Main</div>

    <a href="{{ $dashboardUrl }}"
       class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <a href="{{ $clinicsUrl }}"
       class="admin-sidebar-link {{ request()->routeIs('admin.clinics.index') || request()->routeIs('admin.clinics.show') || request()->routeIs('admin.clinics.edit') ? 'active' : '' }}">
      <i class="bi bi-building"></i>
      <span>Clinics</span>
    </a>

    <div class="admin-sidebar-section-label">Management</div>

    <a href="{{ $createClinicUrl }}"
       class="admin-sidebar-link {{ request()->routeIs('admin.clinics.create') ? 'active' : '' }}">
      <i class="bi bi-building-add"></i>
      <span>Add Clinic</span>
    </a>

    <a href="{{ $applicationsUrl }}"
       class="admin-sidebar-link {{ request()->routeIs('admin.clinics.applications') || request()->routeIs('admin.clinics.application-detail') ? 'active' : '' }}">
      <i class="bi bi-file-earmark-check"></i>
      <span>Applications</span>
    </a>
  </nav>

  <div class="admin-sidebar-bottom">
    <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
      @csrf

      <button type="submit" class="admin-sidebar-link">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>

<script>
  (function () {
    function initAdminSidebar() {
      const body = document.body;
      const sidebar = document.getElementById('adminSidebar');
      const backdrop = document.getElementById('adminSidebarBackdrop');
      const closeBtn = document.getElementById('adminSidebarClose');
      const toggles = document.querySelectorAll('#adminSidebarToggle, [data-admin-sidebar-toggle]');

      if (!sidebar || !backdrop || !closeBtn || toggles.length === 0) return;

      function openSidebar() {
        body.classList.add('admin-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'false');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));
      }

      function closeSidebar() {
        body.classList.remove('admin-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'true');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
      }

      toggles.forEach(function (toggle) {
        if (toggle.dataset.adminSidebarReady === 'true') return;

        toggle.dataset.adminSidebarReady = 'true';

        toggle.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          body.classList.contains('admin-sidebar-open') ? closeSidebar() : openSidebar();
        });
      });

      if (closeBtn.dataset.adminSidebarCloseReady !== 'true') {
        closeBtn.dataset.adminSidebarCloseReady = 'true';

        closeBtn.addEventListener('click', function (event) {
          event.preventDefault();
          closeSidebar();
        });
      }

      if (backdrop.dataset.adminSidebarBackdropReady !== 'true') {
        backdrop.dataset.adminSidebarBackdropReady = 'true';
        backdrop.addEventListener('click', closeSidebar);
      }

      if (document.documentElement.dataset.adminSidebarEscReady !== 'true') {
        document.documentElement.dataset.adminSidebarEscReady = 'true';

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') closeSidebar();
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initAdminSidebar);
    } else {
      initAdminSidebar();
    }
  })();
</script>