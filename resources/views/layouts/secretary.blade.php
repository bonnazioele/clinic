<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Secretary Dashboard') - {{ config('app.name', 'CliniQ') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: hidden;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar.collapsed .nav-link {
            padding: 12px 8px;
            text-align: center;
            transform: none;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar:not(.collapsed) .nav-link:hover,
        .sidebar:not(.collapsed) .nav-link.active {
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            transition: margin 0.3s ease;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        
        .nav-text {
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .nav-text {
            opacity: 0;
            display: none;
        }
        
        .sidebar-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle i {
            font-size: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }
        
        .toggle-icon-bar {
            width: 20px;
            height: 2px;
            background-color: white;
            margin: 3px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
        }
        
        .sidebar-toggle.hamburger {
            flex-direction: column;
            padding: 8px;
        }
        
        .sidebar.collapsed .toggle-icon-bar:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        
        .sidebar.collapsed .toggle-icon-bar:nth-child(2) {
            opacity: 0;
        }
        
        .sidebar.collapsed .toggle-icon-bar:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
        
        .top-bar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 12px 0;
            position: fixed;
            z-index: 999;
            top: 0;
            left: 250px;
            right: 0;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .top-bar.sidebar-collapsed {
            left: 70px;
        }
        
        .clinic-name {
            font-weight: 600;
            color: #495057;
        }
        
        .main-content {
            padding: 20px;
            margin-left: 250px;
            margin-top: 70px; /* Add space for the top bar */
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - 70px);
        }
        
        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }
        
        .top-bar {
            left: 250px;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .top-bar.sidebar-collapsed {
            left: 70px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                margin-top: 140px; /* More space on mobile for both top bar and mobile menu */
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 0;
            }
            
            .top-bar {
                left: 0 !important;
            }
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .mobile-overlay.show {
                opacity: 1;
                visibility: visible;
            }
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .sidebar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            padding: 20px;
            display: block;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-brand {
            padding: 20px 10px;
            font-size: 1.2rem;
            text-align: center;
        }
        
        .sidebar-brand:hover {
            color: white;
            text-decoration: none;
        }
        
        .brand-text {
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .brand-text {
            opacity: 0;
            display: none;
        }
        
        .brand-icon {
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed .brand-icon {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar d-none d-md-block" id="sidebar">
        <!-- Modern Toggle Button -->
        <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        
        <a href="{{ route('secretary.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-hospital brand-icon me-2"></i>
            <span class="brand-text">CliniQ</span>
        </a>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}" 
                   href="{{ route('secretary.dashboard') }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.queue.*') ? 'active' : '' }}" 
                   href="#" onclick="alert('Live Queue Dashboard - Coming Soon')"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Live Queue Dashboard">
                    <i class="bi bi-activity"></i>
                    <span class="nav-text">Live Queue Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.appointments.index') }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Appointments">
                    <i class="bi bi-calendar-check"></i>
                    <span class="nav-text">Appointments</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.doctors.index') }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Doctors">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Doctors</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.services.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.services.index') }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Services">
                    <i class="bi-clipboard2-pulse"></i>
                    <span class="nav-text">Services</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" 
                   href="#" onclick="alert('Roles & Access Management - Coming Soon')"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="right" 
                   title="Roles & Access">
                    <i class="bi bi-shield-check"></i>
                    <span class="nav-text">Roles & Access</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Top Bar -->
    <div class="top-bar" id="topBar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Mobile Menu Toggle -->
                <div class="col-auto d-md-none">
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="mobileSidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                
                <!-- Today's Date -->
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar3 me-2 text-muted"></i>
                        <span class="fw-medium">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
                
                <!-- Clinic Name (Center) -->
                <div class="col text-center">
                    <span class="clinic-name">
                        <i class="bi bi-building me-2"></i>
                        {{ session('current_clinic_name', 'Clinic Name') }}
                    </span>
                </div>

                
                <!-- Notifications -->
                <div class="col-auto">
                    <div class="dropdown">
                        @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                        <a class="nav-link position-relative d-flex align-items-center"
                           href="#"
                           id="notifDropdown"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                            @if($unread)
                                <span class="position-absolute top-0 start-100 translate-middle
                                             badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    {{ $unread }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:300px;">
                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $note)
                                <li>
                                    <a href="{{ route('notifications.index') }}"
                                       class="dropdown-item {{ $note->read_at ? '' : 'fw-bold' }}">
                                        {{ \Illuminate\Support\Str::limit($note->data['message'], 50) }}
                                        <br>
                                        <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item text-muted">No notifications</span></li>
                            @endforelse
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center"
                                   href="{{ route('notifications.index') }}">
                                    View All
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- User Profile (Right) -->
                <div class="col-auto">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" 
                           href="#" 
                           id="userDropdown" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <div class="user-avatar me-2">
                                {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                            </div>
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person me-2"></i>
                                    My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-pencil me-2"></i>
                                    Edit Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay d-md-none" id="mobileOverlay"></div>

    <!-- Mobile Sidebar -->
    <nav class="sidebar d-md-none" id="mobileSidebar">
        <div class="d-flex justify-content-between align-items-center p-3">
            <a href="{{ route('secretary.dashboard') }}" class="sidebar-brand text-white text-decoration-none">
                <i class="bi bi-hospital me-2"></i>
                CliniQ
            </a>
            <button class="btn btn-sm text-white" id="closeMobileSidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-0">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('secretary.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" onclick="alert('Live Queue Dashboard - Coming Soon')">
                        <i class="bi bi-activity"></i>
                        Live Queue Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('secretary.appointments.index') }}">
                        <i class="bi bi-calendar-check"></i>
                        Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('secretary.doctors.index') }}">
                        <i class="bi bi-people"></i>
                        Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('secretary.services.index') }}">
                        <i class="bi-clipboard2-pulse"></i>
                        Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" onclick="alert('Roles & Access Management - Coming Soon')">
                        <i class="bi bi-shield-check"></i>
                        Roles & Access
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('mainContent');
            const topBar = document.getElementById('topBar');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const closeMobileSidebar = document.getElementById('closeMobileSidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            // Initialize tooltips for sidebar navigation
            let tooltipList = [];
            
            function initTooltips() {
                // Dispose existing tooltips
                tooltipList.forEach(tooltip => tooltip.dispose());
                tooltipList = [];
                
                // Initialize tooltips only when sidebar is collapsed and on desktop
                if (sidebar && sidebar.classList.contains('collapsed') && window.innerWidth > 768) {
                    const tooltipTriggerList = sidebar.querySelectorAll('[data-bs-toggle="tooltip"]');
                    tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
                        new bootstrap.Tooltip(tooltipTriggerEl, {
                            trigger: 'hover',
                            delay: { show: 500, hide: 100 }
                        })
                    );
                }
            }
            
            // Load saved sidebar state
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true' && window.innerWidth > 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                topBar.classList.add('sidebar-collapsed');
                initTooltips();
            }
            
            // Desktop sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    
                    if (isCollapsed) {
                        // Expand sidebar
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('sidebar-collapsed');
                        topBar.classList.remove('sidebar-collapsed');
                        localStorage.setItem('sidebarCollapsed', 'false');
                    } else {
                        // Collapse sidebar
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('sidebar-collapsed');
                        topBar.classList.add('sidebar-collapsed');
                        localStorage.setItem('sidebarCollapsed', 'true');
                    }
                    
                    // Reinitialize tooltips
                    setTimeout(initTooltips, 300); // Wait for animation to complete
                });
            }
            
            // Mobile sidebar controls
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function() {
                    mobileSidebar.classList.add('mobile-open');
                    mobileOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            function closeMobileSidebarFn() {
                mobileSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            if (closeMobileSidebar) {
                closeMobileSidebar.addEventListener('click', closeMobileSidebarFn);
            }
            
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', closeMobileSidebarFn);
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768) {
                    // On mobile, ensure classes are removed and close mobile sidebar
                    mainContent.classList.remove('sidebar-collapsed');
                    topBar.classList.remove('sidebar-collapsed');
                    closeMobileSidebarFn();
                } else {
                    // On desktop, restore saved state
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('sidebar-collapsed');
                        topBar.classList.add('sidebar-collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('sidebar-collapsed');
                        topBar.classList.remove('sidebar-collapsed');
                    }
                }
                
                // Reinitialize tooltips
                setTimeout(initTooltips, 300);
            });
            
            // Initialize tooltips on load
            initTooltips();
            
            // Handle escape key for mobile sidebar
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileSidebar.classList.contains('mobile-open')) {
                    closeMobileSidebarFn();
                }
            });
        });
    </script>
</body>
</html>
