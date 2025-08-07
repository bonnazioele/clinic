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
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .top-bar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 12px 0;
        }
        
        .clinic-name {
            font-weight: 600;
            color: #495057;
        }
        
        .main-content {
            padding: 20px;
            margin-left: 250px;
            margin-top: 70px; /* Add space for the top bar */
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                margin-top: 140px; /* More space on mobile for both top bar and mobile menu */
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
        }
        
        .sidebar-brand:hover {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar d-none d-md-block">
        <a href="{{ route('secretary.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-hospital me-2"></i>
            CliniQ
        </a>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}" 
                   href="{{ route('secretary.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.queue.*') ? 'active' : '' }}" 
                   href="#" onclick="alert('Live Queue Dashboard - Coming Soon')">
                    <i class="bi bi-activity"></i>
                    Live Queue Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.appointments.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    Appointments
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.doctors.index') }}">
                    <i class="bi bi-people"></i>
                    Doctors
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('secretary.services.*') ? 'active' : '' }}" 
                   href="{{ route('secretary.services.index') }}">
                    <i class="bi-clipboard2-pulse"></i>
                    Services
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" 
                   href="#" onclick="alert('Roles & Access Management - Coming Soon')">
                    <i class="bi bi-shield-check"></i>
                    Roles & Access
                </a>
            </li>
        </ul>
    </nav>

    <!-- Top Bar -->
    <div class="top-bar position-fixed" style="z-index: 999; top: 0; left: 250px; right: 0;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Mobile Menu Toggle -->
                <div class="col-auto d-md-none">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
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

    <!-- Mobile Sidebar Offcanvas -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="offcanvas-title text-white">
                <i class="bi bi-hospital me-2"></i>
                CliniQ
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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
                    <a class="nav-link text-white" href="#" onclick="alert('Patients - Coming Soon')">
                        <i class="bi bi-people-fill"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" onclick="alert('Services Management - Coming Soon')">
                        <i class="fas fa-procedures"></i> Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" onclick="alert('Roles & Access Management - Coming Soon')">
                        <i class="bi bi-shield-check"></i>
                        Staff & Admins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" onclick="alert('Settings - Coming Soon')">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
</body>
</html>
