<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Secretary Dashboard') - {{ config('app.name', 'CliniQ') }}</title>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Compiled CSS with consistent fonts, colors, and typography -->
    @vite(['resources/sass/app.scss', 'resources/sass/secretary.scss', 'resources/js/app.js'])
</head>
<body>
    <!-- Sidebar -->
    <nav class="secretary-sidebar d-none d-md-block" id="sidebar">
        <button class="secretary-sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        
        <a href="{{ route('secretary.dashboard') }}" class="secretary-sidebar-brand">
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
            
            <!-- Notifications -->
            <li class="nav-item">
                <div class="dropdown dropup">
                    @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                    <a class="nav-link position-relative d-flex align-items-center"
                       href="#"
                       id="sidebarNotifDropdown"
                       data-bs-toggle="dropdown"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="right" 
                       title="Notifications"
                       aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <span class="nav-text">Notifications</span>
                        @if($unread)
                            <span class="position-absolute top-0 start-0 translate-middle
                                         badge rounded-pill bg-danger notification-badge">
                                {{ $unread }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarNotifDropdown" style="min-width:300px;">
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
            </li>
            
            <!-- User Profile -->
            <li class="nav-item">
                <div class="dropdown dropup">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       id="sidebarUserDropdown" 
                       data-bs-toggle="dropdown"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="right" 
                       title="User Profile"
                       aria-expanded="false">
                        <div class="secretary-user-avatar me-2">
                            {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                        </div>
                        <span class="nav-text">{{ auth()->user()->name }}</span>
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
            </li>
        </ul>
    </nav>
    <!-- Mobile Overlay -->
    <div class="secretary-mobile-overlay d-md-none" id="mobileOverlay"></div>

    <!-- Mobile Sidebar -->
    <nav class="secretary-sidebar d-md-none" id="mobileSidebar">
        <div class="d-flex justify-content-between align-items-center p-3">
            <a href="{{ route('secretary.dashboard') }}" class="secretary-sidebar-brand text-white text-decoration-none">
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
                
                <!-- Mobile Notifications -->
                <li class="nav-item">
                    <div class="dropdown dropup">
                        @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                        <a class="nav-link text-white position-relative d-flex align-items-center"
                           href="#"
                           id="mobileNotifDropdown"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="ms-2">Notifications</span>
                            @if($unread)
                                <span class="position-absolute top-0 start-0 translate-middle
                                             badge rounded-pill bg-danger">
                                    {{ $unread }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileNotifDropdown" style="min-width:300px;">
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
                </li>
                
                <!-- Mobile User Profile -->
                <li class="nav-item">
                    <div class="dropdown dropup">
                        <a class="nav-link text-white dropdown-toggle d-flex align-items-center" 
                           href="#" 
                           id="mobileUserDropdown" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <div class="secretary-user-avatar me-2">
                                {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                            </div>
                            <span>{{ auth()->user()->name }}</span>
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
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="secretary-main-content" id="mainContent">
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
                        localStorage.setItem('sidebarCollapsed', 'false');
                    } else {
                        // Collapse sidebar
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('sidebar-collapsed');
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
                    closeMobileSidebarFn();
                } else {
                    // On desktop, restore saved state
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('sidebar-collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('sidebar-collapsed');
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
