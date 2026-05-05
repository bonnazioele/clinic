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
  $walkinUrl = $safeSecretaryRoute('secretary.walkin.index', '/secretary/dashboard');
  $clinicSettingsUrl = $safeSecretaryRoute('secretary.clinic.edit', '/secretary/dashboard');
  $dashboardUrl = safe_secretary_route('secretary.dashboard', '/secretary/dashboard');
  $appointmentsUrl = safe_secretary_route('secretary.appointments.index', '/secretary/dashboard');
  $queueUrl = safe_secretary_route('secretary.queue.index', '/secretary/dashboard');
  $doctorsUrl = safe_secretary_route('secretary.doctors.index', '/secretary/dashboard');
  $servicesUrl = safe_secretary_route('secretary.services.index', '/secretary/dashboard');
  $clinicSettingsUrl = safe_secretary_route('secretary.clinic.edit', '/secretary/dashboard');

  $activeClinicServices = collect();
  if ($secretaryClinicId) {
      $activeClinicServices = \App\Models\Service::query()
          ->forClinics([$secretaryClinicId])
          ->orderBy('name')
          ->get(['id', 'name']);
  }

    $servicesExpanded = request()->routeIs('secretary.services.*')
      || request()->routeIs('secretary.services.queue.index');
    $currentServiceParam = request()->route('service_id')
      ?? request()->query('service');
@endphp

<style>
  body.secretary-sidebar-open {
    overflow: hidden;
  }

  .secretary-sidebar-backdrop {
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

  body.secretary-sidebar-open .secretary-sidebar-backdrop {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .secretary-sidebar {
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
    overflow-y: auto;
  }

  body.secretary-sidebar-open .secretary-sidebar {
    transform: translateX(0);
  }

  .secretary-sidebar-close {
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

  .secretary-sidebar-close:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
  }

  .secretary-sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 42px 20px 2px;
    margin-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    text-decoration: none;
  }

  .secretary-sidebar-logo {
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

  .secretary-sidebar-title {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
  }

  .secretary-sidebar-title strong {
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .secretary-sidebar-title span {
    color: #9fb5cc;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
  }

  .secretary-sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .secretary-sidebar-link {
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

  .secretary-sidebar-link i {
    width: 25px;
    text-align: center;
    font-size: 22px;
    flex: 0 0 auto;
  }

  .secretary-sidebar-link span {
    white-space: nowrap;
  }

  .secretary-sidebar-link:hover,
  .secretary-sidebar-link.active {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.35);
  }

  .secretary-sidebar-section-label {
    margin: 12px 4px 2px;
    color: #8ea7bf;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .secretary-sidebar-bottom {
    margin-top: auto;
    padding-top: 18px;
  }

  .secretary-sidebar-bottom form {
    margin: 0;
  }

  .secretary-sidebar-bottom .secretary-sidebar-link {
    color: #fecaca;
  }

  .secretary-sidebar-bottom .secretary-sidebar-link:hover {
    background: rgba(220, 38, 38, 0.18);
    color: #ffffff;
    box-shadow: none;
  }

  .secretary-sidebar-collapse-toggle {
    width: 100%;
    min-height: 52px;
    border: 0;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
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

  .secretary-sidebar-collapse-toggle:hover,
  .secretary-sidebar-collapse-toggle.is-open {
    background: rgba(13, 110, 253, 0.2);
    color: #ffffff;
  }

  .secretary-sidebar-collapse-toggle i {
    width: 25px;
    text-align: center;
    font-size: 22px;
    flex: 0 0 auto;
  }

  .secretary-sidebar-collapse-caret {
    font-size: 18px;
    transition: transform 0.2s ease;
  }

  .secretary-sidebar-collapse-toggle.is-open .secretary-sidebar-collapse-caret {
    transform: rotate(180deg);
  }

  .secretary-sidebar-collapse {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height 0.25s ease, opacity 0.2s ease;
    padding-left: 8px;
  }

  .secretary-sidebar-collapse.is-open {
    opacity: 1;
    max-height: 420px;
  }

  .secretary-sidebar-service-link {
    width: 100%;
    min-height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #c8d8ea;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    padding: 0 12px 0 42px;
    transition: 0.2s ease;
  }

  .secretary-sidebar-service-link:hover,
  .secretary-sidebar-service-link.active {
    background: rgba(13, 110, 253, 0.25);
    color: #ffffff;
  }
</style>

<div class="secretary-sidebar-backdrop" id="secretarySidebarBackdrop"></div>

<aside class="secretary-sidebar" id="secretarySidebar" aria-hidden="true">
  <button type="button" class="secretary-sidebar-close" id="secretarySidebarClose" aria-label="Close sidebar">
    <i class="bi bi-x-lg"></i>
  </button>

  <a href="{{ $dashboardUrl }}" class="secretary-sidebar-brand">
    <span class="secretary-sidebar-logo">
      <i class="bi bi-heart-pulse"></i>
    </span>

    <span class="secretary-sidebar-title">
      <strong>CliniQ</strong>
      <span>Secretary Menu</span>
    </span>
  </a>

  <nav class="secretary-sidebar-nav">
    <div class="secretary-sidebar-section-label">Main</div>

    <a href="{{ $dashboardUrl }}"
       class="secretary-sidebar-link {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <a href="{{ $appointmentsUrl }}"
       class="secretary-sidebar-link {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}">
      <i class="bi bi-calendar-check"></i>
      <span>Appointments</span>
    </a>

    <a href="{{ $queueUrl }}"
       class="secretary-sidebar-link {{ request()->routeIs('secretary.queue.*') ? 'active' : '' }}">
      <i class="bi bi-people"></i>
      <span>Queue Status</span>
    </a>

    <a href="{{ $walkinUrl }}"
        class="secretary-sidebar-link {{ request()->routeIs('secretary.walkin.*') ? 'active' : '' }}">
        <i class="bi bi-person-plus"></i>
        <span>Walk-in Patients</span>
      </a>

    <div class="secretary-sidebar-section-label">Management</div>

    <a href="{{ $doctorsUrl }}"
       class="secretary-sidebar-link {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}">
      <i class="bi bi-person-badge"></i>
      <span>Doctors</span>
    </a>

    <button
      type="button"
      class="secretary-sidebar-collapse-toggle {{ $servicesExpanded ? 'is-open' : '' }}"
      id="secretaryServicesToggle"
      aria-expanded="{{ $servicesExpanded ? 'true' : 'false' }}"
      aria-controls="secretaryServicesCollapse">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-clipboard2-pulse"></i>
        <span>Services</span>
      </span>
      <i class="bi bi-chevron-down secretary-sidebar-collapse-caret"></i>
    </button>
    <div
      class="secretary-sidebar-collapse {{ $servicesExpanded ? 'is-open' : '' }}"
      id="secretaryServicesCollapse">
      @forelse ($activeClinicServices as $service)
        <a
          href="{{ safe_secretary_route('secretary.services.queue.index', '/secretary/dashboard', ['service_id' => $service->id]) }}"
          class="secretary-sidebar-service-link {{ $currentServiceParam == $service->id ? 'active' : '' }}">
          <i class="bi bi-dot"></i>
          <span>{{ $service->name }}</span>
        </a>
      @empty
        <span class="secretary-sidebar-service-link">
          <i class="bi bi-dot"></i>
          <span>No services yet</span>
        </span>
      @endforelse
    </div>

    <a href="{{ route('secretary.analytics.index') }}"
        class="secretary-sidebar-link {{ request()->routeIs('secretary.analytics.*') ? 'active' : '' }}">
      <i class="bi bi-graph-up-arrow"></i>
      <span>Analytics Report</span>
    </a>

    <a href="{{ $clinicSettingsUrl }}"
       class="secretary-sidebar-link {{ request()->routeIs('secretary.clinic.*') ? 'active' : '' }}">
      <i class="bi bi-building-gear"></i>
      <span>Clinic Settings</span>
    </a>
  </nav>

  <div class="secretary-sidebar-bottom">
    <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
      @csrf

      <button type="submit" class="secretary-sidebar-link">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>

<script>
  (function () {
    function initSecretarySidebar() {
      const body = document.body;
      const sidebar = document.getElementById('secretarySidebar');
      const backdrop = document.getElementById('secretarySidebarBackdrop');
      const closeBtn = document.getElementById('secretarySidebarClose');
      const toggles = document.querySelectorAll('#secretarySidebarToggle, [data-secretary-sidebar-toggle]');

      if (!sidebar || !backdrop || !closeBtn || toggles.length === 0) return;

      function openSidebar() {
        body.classList.add('secretary-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'false');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));
      }

      function closeSidebar() {
        body.classList.remove('secretary-sidebar-open');
        sidebar.setAttribute('aria-hidden', 'true');
        toggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
      }

      toggles.forEach(function (toggle) {
        if (toggle.dataset.secretarySidebarReady === 'true') return;

        toggle.dataset.secretarySidebarReady = 'true';

        toggle.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          body.classList.contains('secretary-sidebar-open') ? closeSidebar() : openSidebar();
        });
      });

      if (closeBtn.dataset.secretarySidebarCloseReady !== 'true') {
        closeBtn.dataset.secretarySidebarCloseReady = 'true';

        closeBtn.addEventListener('click', function (event) {
          event.preventDefault();
          closeSidebar();
        });
      }

      if (backdrop.dataset.secretarySidebarBackdropReady !== 'true') {
        backdrop.dataset.secretarySidebarBackdropReady = 'true';
        backdrop.addEventListener('click', closeSidebar);
      }

      if (document.documentElement.dataset.secretarySidebarEscReady !== 'true') {
        document.documentElement.dataset.secretarySidebarEscReady = 'true';

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') closeSidebar();
        });
      }

      const servicesToggle = document.getElementById('secretaryServicesToggle');
      const servicesCollapse = document.getElementById('secretaryServicesCollapse');

      if (servicesToggle && servicesCollapse && servicesToggle.dataset.servicesReady !== 'true') {
        servicesToggle.dataset.servicesReady = 'true';

        servicesToggle.addEventListener('click', function () {
          const isOpen = servicesCollapse.classList.toggle('is-open');
          servicesToggle.classList.toggle('is-open', isOpen);
          servicesToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initSecretarySidebar);
    } else {
      initSecretarySidebar();
    }
  })();
</script>
