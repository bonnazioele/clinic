<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','CliniQ Secretary')</title>
    <!-- Compiled CSS/JS -->
    @vite(['resources/sass/app.scss', 'resources/sass/secretary.scss', 'resources/js/app.js'])
    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>
<body>
    <!-- Sidebar (Desktop) -->
    {{-- Desktop Sidebar --}}
<nav class="secretary-sidebar d-none d-md-flex" id="sidebar">
  <div class="brand-wrap">
    <a href="{{ route('secretary.dashboard') }}" class="secretary-sidebar-brand text-white text-decoration-none">
      <i class="bi bi-hospital brand-icon me-2"></i>
      <span class="brand-text">CliniQ</span>
    </a>

    {{-- Compact chevrons (grid cell) --}}
    <button class="secretary-sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
      <i class="bi bi-chevron-double-left"></i>
    </button>
  </div>

  <div class="nav-section">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}"
           href="{{ route('secretary.dashboard') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
          <i class="bi bi-speedometer2"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('secretary.queue.*') ? 'active' : '' }}"
           href="#" onclick="alert('Live Queue Dashboard - Coming Soon')"
           data-bs-toggle="tooltip" data-bs-placement="right" title="Live Queue Dashboard">
          <i class="bi bi-broadcast"></i>
          <span class="nav-text">Live Queue Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}"
           href="{{ route('secretary.appointments.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Appointments">
          <i class="bi bi-calendar-event"></i>
          <span class="nav-text">Appointments</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}"
           href="{{ route('secretary.doctors.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Doctors">
          <i class="bi bi-person-vcard"></i>
          <span class="nav-text">Doctors</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('secretary.services.*') ? 'active' : '' }}"
           href="{{ route('secretary.services.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Services">
          <i class="bi bi-clipboard2-pulse"></i>
          <span class="nav-text">Services</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link"
           href="#" onclick="alert('Roles & Access Management - Coming Soon')"
           data-bs-toggle="tooltip" data-bs-placement="right" title="Roles & Access">
          <i class="bi bi-people"></i>
          <span class="nav-text">Roles & Access</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link"
           href="#" onclick="alert('Notifications - Coming Soon')"
           data-bs-toggle="tooltip" data-bs-placement="right" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="nav-text">Notifications</span>
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="avatar">
        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
      </div>
      <div class="truncate">
        <div class="name">{{ auth()->user()->name }}</div>
        <div class="meta">Secretary</div>
      </div>
    </div>
  </div>
</nav>


    <!-- Mobile Overlay -->
    <div class="secretary-mobile-overlay d-md-none" id="mobileOverlay"></div>

    <!-- Sidebar (Mobile) -->
    <nav class="secretary-sidebar d-md-none" id="mobileSidebar">
        <div class="d-flex justify-content-between align-items-center p-3">
            <a href="{{ route('secretary.dashboard') }}" class="secretary-sidebar-brand text-white text-decoration-none">
                <i class="bi bi-hospital me-2"></i> CliniQ
            </a>
            <button class="btn btn-sm text-white" id="closeMobileSidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- SCROLLABLE NAV SECTION (Mobile) -->
        <div class="nav-section p-0">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}"
                       href="{{ route('secretary.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}"
                       href="{{ route('secretary.appointments.index') }}">
                        <i class="bi bi-calendar-event"></i>
                        <span class="nav-text">Appointments</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}"
                       href="{{ route('secretary.doctors.index') }}">
                        <i class="bi bi-person-vcard"></i>
                        <span class="nav-text">Doctors</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('secretary.services.*') ? 'active' : '' }}"
                       href="{{ route('secretary.services.index') }}">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <span class="nav-text">Services</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- FOOTER / USER (Mobile) -->
        <div class="sidebar-footer">
            <div class="user-mini text-white">
                <div class="avatar bg-light text-dark">
                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                </div>
                <div class="truncate">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="meta">Secretary</div>
                </div>
                <a class="btn btn-sm btn-outline-light rounded-pill px-3"
                   href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="secretary-main-content" id="mainContent">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="secretary-dashboard">
            @yield('content')
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('mainContent');

            // Desktop toggle
            sidebarToggle?.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore desktop state
            if (window.innerWidth >= 992) {
                const saved = localStorage.getItem('sidebarCollapsed');
                if (saved === 'true') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                }
            }

            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Mobile sidebar
            const mobileSidebar = document.getElementById('mobileSidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const closeMobileSidebar = document.getElementById('closeMobileSidebar');

            function openMobileSidebar() {
                mobileSidebar.classList.add('mobile-open');
                mobileOverlay.classList.add('mobile-open');
                document.body.style.overflow = 'hidden';
            }
            function closeMobileSidebarFn() {
                mobileSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
            closeMobileSidebar?.addEventListener('click', closeMobileSidebarFn);
            mobileOverlay?.addEventListener('click', closeMobileSidebarFn);

            // Escape closes mobile
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileSidebar.classList.contains('mobile-open')) {
                    closeMobileSidebarFn();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
