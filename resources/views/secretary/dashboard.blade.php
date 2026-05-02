<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth

    <title>@yield('title', config('app.name', 'CliniQ'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Leaflet --}}
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="">

    {{-- Choices.js --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0a58ca;
            --primary-light: #e7f1ff;

            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;

            --light-color: #f8f9fa;
            --dark-color: #212529;

            --medical-blue: #1e88e5;
            --medical-green: #43a047;
            --medical-red: #e53935;
            --medical-orange: #ff9800;

            --border-radius: 0.75rem;
            --border-radius-sm: 0.5rem;

            --transition: all 0.3s ease;

            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        html {
            scroll-behavior: smooth;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 32%),
                linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%);
            line-height: 1.6;
            color: var(--dark-color);
        }

        #app {
            min-height: 100vh;
        }

        main {
            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | SHARED ROLE-BASED APP LAYOUT
        |--------------------------------------------------------------------------
        */

        .role-layout {
            min-height: 100vh;
            display: flex;
        }

        .role-sidebar {
            position: fixed;
            top: 12px;
            left: 14px;
            bottom: 12px;
            width: 78px;
            z-index: 1040;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(18px);
            box-shadow:
                0 16px 42px rgba(15, 23, 42, 0.09),
                inset 0 1px 0 rgba(255, 255, 255, 0.82);
            padding: 0.75rem 0.55rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: 0.25s ease;
        }

        .role-sidebar-brand {
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            display: grid;
            place-items: center;
            border-radius: 17px;
            background: linear-gradient(135deg, #0d6efd, #1287ff);
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
            margin-bottom: 0.9rem;
            font-size: 1.35rem;
        }

        .role-sidebar-nav {
            display: grid;
            gap: 0.45rem;
            width: 100%;
        }

        .role-sidebar-link {
            position: relative;
            width: 54px;
            height: 54px;
            margin: 0 auto;
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: #64748b;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .role-sidebar-link i {
            font-size: 1.22rem;
        }

        .role-sidebar-link span {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(-4px);
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            border-radius: 999px;
            padding: 0.42rem 0.72rem;
            background: #0f172a;
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 800;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
            transition: 0.18s ease;
        }

        .role-sidebar-link:hover span {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        .role-sidebar-link:hover {
            background: #eff6ff;
            color: #0d6efd;
        }

        .role-sidebar-link.active {
            background: #0d6efd;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(13, 110, 253, 0.22);
        }

        .role-sidebar-link.active::before {
            content: "";
            position: absolute;
            left: -8px;
            width: 4px;
            height: 24px;
            border-radius: 999px;
            background: #0d6efd;
        }

        .role-main {
            min-height: 100vh;
            width: 100%;
            padding-left: 104px;
            transition: 0.25s ease;
        }

        .role-navbar-wrap {
            position: sticky;
            top: 12px;
            z-index: 1030;
            padding: 12px 1.25rem 0;
        }

        .role-navbar {
            width: 100%;
            min-height: 74px;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(18px);
            box-shadow:
                0 14px 36px rgba(15, 23, 42, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.82);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
        }

        .role-navbar-left {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .role-navbar-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }

        .role-navbar-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #0d6efd, #1287ff);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
            font-size: 1.35rem;
        }

        .role-navbar-title {
            margin: 0;
            color: #071225;
            font-size: 1.28rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .role-navbar-subtitle {
            margin: 0.22rem 0 0;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.25;
        }

        .role-navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .role-action-btn {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid #dbe3ef;
            background: #ffffff;
            color: #475569;
            display: grid;
            place-items: center;
            transition: 0.18s ease;
            text-decoration: none;
        }

        .role-action-btn:hover {
            color: #0d6efd;
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .role-profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border-radius: 999px;
            padding: 0.35rem 0.7rem 0.35rem 0.35rem;
            background: #ffffff;
            border: 1px solid #dbe3ef;
            color: #0f172a;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
            max-width: 240px;
        }

        .role-profile-pill:hover {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #0d6efd;
        }

        .role-profile-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #eff6ff;
            color: #0d6efd;
            font-size: 0.85rem;
            font-weight: 900;
        }

        .role-profile-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .role-content {
            width: 100%;
            padding: 0 1.25rem 1.5rem;
        }

        .role-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.36);
            backdrop-filter: blur(3px);
            z-index: 1035;
            opacity: 0;
            pointer-events: none;
            transition: 0.2s ease;
        }

        .role-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | GLOBAL CONTENT WIDTH
        |--------------------------------------------------------------------------
        */

        .floating-wrapper {
            width: 90%;
            max-width: 1700px;
            margin: 0 auto;
        }

        .patient-main,
        .patient-tab-content,
        .dashboard-container,
        .patient-content-container,
        .appointments-container,
        .queue-container,
        .clinics-container,
        .profile-container {
            width: 100%;
            max-width: none;
            margin-left: auto;
            margin-right: auto;
        }

        .patient-main .container,
        .patient-main .container-sm,
        .patient-main .container-md,
        .patient-main .container-lg,
        .patient-main .container-xl,
        .patient-main .container-xxl,
        .patient-tab-content .container,
        .patient-tab-content .container-sm,
        .patient-tab-content .container-md,
        .patient-tab-content .container-lg,
        .patient-tab-content .container-xl,
        .patient-tab-content .container-xxl,
        .floating-wrapper > .container,
        .floating-wrapper > .container-sm,
        .floating-wrapper > .container-md,
        .floating-wrapper > .container-lg,
        .floating-wrapper > .container-xl,
        .floating-wrapper > .container-xxl {
            width: 100%;
            max-width: none !important;
        }

        .medical-gradient {
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--primary-color) 100%);
        }

        .medical-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(30, 136, 229, 0.1);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .medical-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(30, 136, 229, 0.3);
        }

        .avatar-circle {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--medical-blue) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.9rem;
        }

        .clinic-card {
            transition: var(--transition);
            border-radius: var(--border-radius);
            border: 1px solid rgba(30, 136, 229, 0.1);
            box-shadow: var(--shadow);
            cursor: pointer;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .clinic-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: rgba(30, 136, 229, 0.3);
        }

        .btn {
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 1.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--medical-blue) 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, var(--medical-green) 100%);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, var(--medical-red) 100%);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, var(--medical-orange) 100%);
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: var(--border-radius-sm);
            border: 2px solid #e9ecef;
            transition: var(--transition);
            padding: 0.75rem 1rem;
            background: #ffffff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
            transform: translateY(-1px);
            background: #ffffff;
        }

        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: #ffffff;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--light-color) 0%, #e9ecef 100%);
            border-bottom: 2px solid var(--medical-blue);
            font-weight: 600;
            color: var(--dark-color);
            padding: 1rem;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(30, 136, 229, 0.05) 0%, rgba(30, 136, 229, 0.1) 100%);
        }

        .badge {
            border-radius: 1rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow);
            border-left: 4px solid;
        }

        .alert-primary {
            border-left-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-light) 0%, #e7f1ff 100%);
        }

        .alert-success {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .alert-warning {
            border-left-color: var(--warning-color);
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .alert-danger {
            border-left-color: var(--danger-color);
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }

        .medical-icon {
            color: var(--medical-blue);
            font-size: 1.2rem;
        }

        .medical-icon-success {
            color: var(--medical-green);
        }

        .medical-icon-warning {
            color: var(--medical-orange);
        }

        .medical-icon-danger {
            color: var(--medical-red);
        }

        .queue-status {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid var(--medical-blue);
            border-radius: var(--border-radius);
            padding: 1rem;
            margin: 1rem 0;
        }

        .queue-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--medical-blue);
            text-align: center;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .btn:focus,
        .form-control:focus,
        .form-select:focus {
            outline: 2px solid var(--medical-blue);
            outline-offset: 2px;
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        footer {
            margin-top: auto;
        }

        footer a:hover {
            color: var(--primary-color) !important;
            transform: translateX(3px);
            transition: var(--transition);
        }

        .toast-container {
            z-index: 9999 !important;
        }

        .toast {
            min-width: 320px !important;
            max-width: 400px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: none !important;
            margin-bottom: 0.75rem !important;
            border-radius: 8px !important;
        }

        .toast-body {
            padding: 1rem !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
        }

        .toast .btn-close {
            margin: 0.5rem !important;
        }

        @media (min-width: 768px) {
            .position-fixed[style*="top:"] {
                top: 5rem !important;
                right: 2rem !important;
                left: auto !important;
                width: 400px !important;
                max-width: 400px !important;
                z-index: 9999 !important;
            }
        }

        @media (max-width: 992px) {
            .role-main {
                padding-left: 0;
            }

            .role-sidebar {
                transform: translateX(-120%);
            }

            .role-sidebar.show {
                transform: translateX(0);
            }

            .role-navbar-wrap {
                padding-left: 0.85rem;
                padding-right: 0.85rem;
            }

            .role-content {
                padding-left: 0.85rem;
                padding-right: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            .floating-wrapper {
                width: 94%;
            }

            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .clinic-card:hover,
            .medical-card:hover,
            .btn:hover {
                transform: none;
            }

            .role-navbar {
                align-items: flex-start;
            }

            .role-navbar-title {
                font-size: 1.08rem;
            }

            .role-navbar-subtitle {
                font-size: 0.76rem;
            }

            .role-profile-name {
                display: none;
            }

            .role-profile-pill {
                padding-right: 0.35rem;
            }
        }

        @media (max-width: 767px) {
            .position-fixed[style*="top:"] {
                top: 5rem !important;
                right: 1rem !important;
                left: 1rem !important;
                width: auto !important;
                max-width: none !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
@php
    $user = auth()->user();
    $routeName = request()->route()?->getName();

    $safeRoute = function ($name, $fallback = '#') {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : url($fallback);
    };

    $safeRouteWithParams = function ($name, $params = [], $fallback = '#') {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url($fallback);
    };

    $isActive = function ($patterns) {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    $rawRole = strtolower((string) (
        $user->role
        ?? $user->user_type
        ?? $user->type
        ?? ''
    ));

    if ($user?->is_doctor) {
        $roleKey = 'doctor';
    } elseif (in_array($rawRole, ['secretary', 'staff', 'clinic_staff'], true)) {
        $roleKey = 'secretary';
    } elseif (in_array($rawRole, ['owner', 'clinic_owner', 'admin', 'clinic_admin'], true)) {
        $roleKey = 'owner';
    } elseif (in_array($rawRole, ['super_admin', 'superadmin'], true)) {
        $roleKey = 'superadmin';
    } else {
        $roleKey = 'patient';
    }

    $menus = [
        'patient' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-house-heart',
                'route' => 'dashboard',
                'fallback' => '/dashboard',
                'active' => ['dashboard'],
            ],
            [
                'label' => 'Appointments',
                'icon' => 'bi-calendar2-check',
                'route' => 'appointments.index',
                'fallback' => '/appointments',
                'active' => ['appointments.*'],
            ],
            [
                'label' => 'Queue Status',
                'icon' => 'bi-people-fill',
                'route' => 'queue.status',
                'fallback' => '/queue/status',
                'active' => ['queue.*'],
            ],
            [
                'label' => 'Clinics',
                'icon' => 'bi-hospital',
                'route' => 'clinics.index',
                'fallback' => '/clinics',
                'active' => ['clinics.*'],
            ],
            [
                'label' => 'Profile',
                'icon' => 'bi-person-circle',
                'route' => 'profile.show',
                'fallback' => '/profile',
                'active' => ['profile.*'],
            ],
        ],

        'secretary' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-speedometer2',
                'route' => 'secretary.dashboard',
                'fallback' => '/secretary/dashboard',
                'active' => ['secretary.dashboard'],
            ],
            [
                'label' => 'Appointments',
                'icon' => 'bi-calendar-check',
                'route' => 'secretary.appointments.index',
                'fallback' => '/secretary/appointments',
                'active' => ['secretary.appointments.*'],
            ],
            [
                'label' => 'Queue',
                'icon' => 'bi-people',
                'route' => 'secretary.queue.index',
                'fallback' => '/secretary/queue',
                'active' => ['secretary.queue.*'],
            ],
            [
                'label' => 'Doctors',
                'icon' => 'bi-person-badge',
                'route' => 'secretary.doctors.index',
                'fallback' => '/secretary/doctors',
                'active' => ['secretary.doctors.*'],
            ],
            [
                'label' => 'Services',
                'icon' => 'bi-clipboard2-pulse',
                'route' => 'secretary.services.index',
                'fallback' => '/secretary/services',
                'active' => ['secretary.services.*'],
            ],
        ],

        'doctor' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-heart-pulse',
                'route' => 'doctor.dashboard',
                'fallback' => '/doctor/dashboard',
                'active' => ['doctor.dashboard'],
            ],
            [
                'label' => 'Queue',
                'icon' => 'bi-list-ol',
                'route' => 'doctor.queue.index',
                'fallback' => '/doctor/queue',
                'active' => ['doctor.queue.*'],
            ],
            [
                'label' => 'Appointments',
                'icon' => 'bi-calendar2-check',
                'route' => 'doctor.appointments.index',
                'fallback' => '/doctor/appointments',
                'active' => ['doctor.appointments.*'],
            ],
            [
                'label' => 'Profile',
                'icon' => 'bi-person-circle',
                'route' => 'profile.show',
                'fallback' => '/profile',
                'active' => ['profile.*'],
            ],
        ],

        'owner' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-speedometer2',
                'route' => 'owner.dashboard',
                'fallback' => '/owner/dashboard',
                'active' => ['owner.dashboard', 'admin.dashboard'],
            ],
            [
                'label' => 'Clinics',
                'icon' => 'bi-hospital',
                'route' => 'owner.clinics.index',
                'fallback' => '/owner/clinics',
                'active' => ['owner.clinics.*', 'admin.clinics.*'],
            ],
            [
                'label' => 'Doctors',
                'icon' => 'bi-person-badge',
                'route' => 'owner.doctors.index',
                'fallback' => '/owner/doctors',
                'active' => ['owner.doctors.*', 'admin.doctors.*'],
            ],
            [
                'label' => 'Services',
                'icon' => 'bi-clipboard2-pulse',
                'route' => 'owner.services.index',
                'fallback' => '/owner/services',
                'active' => ['owner.services.*', 'admin.services.*'],
            ],
            [
                'label' => 'Applications',
                'icon' => 'bi-file-earmark-medical',
                'route' => 'owner.applications.index',
                'fallback' => '/owner/applications',
                'active' => ['owner.applications.*', 'admin.applications.*'],
            ],
        ],

        'superadmin' => [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-speedometer2',
                'route' => 'admin.dashboard',
                'fallback' => '/admin/dashboard',
                'active' => ['admin.dashboard', 'superadmin.dashboard'],
            ],
            [
                'label' => 'Clinics',
                'icon' => 'bi-hospital',
                'route' => 'admin.clinics.index',
                'fallback' => '/admin/clinics',
                'active' => ['admin.clinics.*', 'superadmin.clinics.*'],
            ],
            [
                'label' => 'Applications',
                'icon' => 'bi-file-earmark-check',
                'route' => 'admin.applications.index',
                'fallback' => '/admin/applications',
                'active' => ['admin.applications.*', 'superadmin.applications.*'],
            ],
            [
                'label' => 'Users',
                'icon' => 'bi-people-fill',
                'route' => 'admin.users.index',
                'fallback' => '/admin/users',
                'active' => ['admin.users.*', 'superadmin.users.*'],
            ],
        ],
    ];

    $pageMetaByRoute = [
        'dashboard' => [
            'title' => 'Dashboard',
            'subtitle' => 'Overview of your appointments, queues, and clinic activity.',
            'icon' => 'bi-house-heart',
        ],

        'appointments.index' => [
            'title' => 'My Appointments',
            'subtitle' => 'View your upcoming, active, and past appointments.',
            'icon' => 'bi-calendar2-check',
        ],

        'appointments.create' => [
            'title' => 'Book Appointment',
            'subtitle' => 'Choose your clinic, service, doctor, and preferred schedule.',
            'icon' => 'bi-calendar-plus',
        ],

        'appointments.edit' => [
            'title' => 'Edit Appointment',
            'subtitle' => 'Update your appointment details before your visit.',
            'icon' => 'bi-pencil-square',
        ],

        'queue.status' => [
            'title' => 'My Queue Status',
            'subtitle' => 'Check your queue number, waiting status, and estimated call time.',
            'icon' => 'bi-people-fill',
        ],

        'queue.status.entry' => [
            'title' => 'Queue Details',
            'subtitle' => 'Monitor your current queue position and progress.',
            'icon' => 'bi-clock-history',
        ],

        'clinics.index' => [
            'title' => 'Find Clinics',
            'subtitle' => 'Search clinics, view services, and book your visit.',
            'icon' => 'bi-hospital',
        ],

        'clinics.show' => [
            'title' => 'Clinic Details',
            'subtitle' => 'View clinic information, services, doctors, and location.',
            'icon' => 'bi-building',
        ],

        'profile.show' => [
            'title' => 'My Profile',
            'subtitle' => 'View your personal information and medical document.',
            'icon' => 'bi-person-circle',
        ],

        'profile.edit' => [
            'title' => 'Edit Profile',
            'subtitle' => 'Update your personal information and contact details.',
            'icon' => 'bi-pencil-square',
        ],

        'secretary.dashboard' => [
            'title' => 'Secretary Dashboard',
            'subtitle' => 'Manage today’s appointments and clinic queues.',
            'icon' => 'bi-speedometer2',
        ],

        'secretary.appointments.index' => [
            'title' => 'Appointments',
            'subtitle' => 'Manage patient appointments for your active clinic.',
            'icon' => 'bi-calendar-check',
        ],

        'secretary.queue.index' => [
            'title' => 'Queue Management',
            'subtitle' => 'Call, serve, and manage patients in the queue.',
            'icon' => 'bi-people',
        ],

        'secretary.doctors.index' => [
            'title' => 'Doctors',
            'subtitle' => 'Manage doctors assigned to your active clinic.',
            'icon' => 'bi-person-badge',
        ],

        'secretary.services.index' => [
            'title' => 'Services',
            'subtitle' => 'Manage clinic services and service availability.',
            'icon' => 'bi-clipboard2-pulse',
        ],

        'doctor.dashboard' => [
            'title' => 'Doctor Dashboard',
            'subtitle' => 'View your assigned patients and queue activity.',
            'icon' => 'bi-heart-pulse',
        ],

        'doctor.queue.index' => [
            'title' => 'Doctor Queue',
            'subtitle' => 'Review patients currently assigned to your lane.',
            'icon' => 'bi-list-ol',
        ],

        'owner.dashboard' => [
            'title' => 'Clinic Dashboard',
            'subtitle' => 'Manage your clinics, doctors, services, and operations.',
            'icon' => 'bi-speedometer2',
        ],

        'admin.dashboard' => [
            'title' => 'Admin Dashboard',
            'subtitle' => 'Manage platform clinics, users, and applications.',
            'icon' => 'bi-speedometer2',
        ],
    ];

    $activeMeta = $pageMetaByRoute[$routeName] ?? [
        'title' => View::yieldContent('title', config('app.name', 'CliniQ')),
        'subtitle' => match ($roleKey) {
            'secretary' => 'Manage appointments, doctors, services, and queue activity.',
            'doctor' => 'Manage your patient queue and clinic schedule.',
            'owner' => 'Manage your clinic operations.',
            'superadmin' => 'Manage platform records and clinic applications.',
            default => 'Manage your clinic appointments and queue activity.',
        },
        'icon' => match ($roleKey) {
            'secretary' => 'bi-speedometer2',
            'doctor' => 'bi-heart-pulse',
            'owner' => 'bi-hospital',
            'superadmin' => 'bi-shield-check',
            default => 'bi-grid',
        },
    ];

    $currentMenu = $menus[$roleKey] ?? $menus['patient'];
    $profileUrl = $safeRoute('profile.show', '/profile');
@endphp

<div id="app" class="min-vh-100">
    @auth
        <div class="role-layout">
            <aside class="role-sidebar" id="roleSidebar" aria-label="Main navigation">
                <a href="{{ $safeRoute($currentMenu[0]['route'] ?? 'dashboard', $currentMenu[0]['fallback'] ?? '/dashboard') }}"
                   class="role-sidebar-brand"
                   title="CliniQ">
                    <i class="bi bi-heart-pulse"></i>
                </a>

                <nav class="role-sidebar-nav">
                    @foreach($currentMenu as $item)
                        @php
                            $itemUrl = $safeRoute($item['route'], $item['fallback'] ?? '#');
                            $itemActive = $isActive($item['active'] ?? []);
                        @endphp

                        <a href="{{ $itemUrl }}"
                           class="role-sidebar-link {{ $itemActive ? 'active' : '' }}"
                           title="{{ $item['label'] }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="role-backdrop" id="roleBackdrop"></div>

            <main class="role-main">
                <div class="role-navbar-wrap">
                    <header class="role-navbar">
                        <div class="role-navbar-left">
                            <button type="button"
                                    class="role-action-btn d-lg-none"
                                    id="sidebarToggle"
                                    aria-label="Toggle sidebar">
                                <i class="bi bi-list"></i>
                            </button>

                            <div class="role-navbar-title-wrap">
                                <div class="role-navbar-icon">
                                    <i class="bi {{ $activeMeta['icon'] }}"></i>
                                </div>

                                <div>
                                    <h1 class="role-navbar-title">{{ $activeMeta['title'] }}</h1>
                                    <p class="role-navbar-subtitle">{{ $activeMeta['subtitle'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="role-navbar-actions">
                            <button type="button"
                                    class="role-action-btn"
                                    title="Notifications"
                                    aria-label="Notifications">
                                <i class="bi bi-bell"></i>
                            </button>

                            <a href="{{ $profileUrl }}" class="role-profile-pill" title="Profile">
                                <span class="role-profile-avatar">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="role-profile-name">{{ auth()->user()->name }}</span>
                            </a>
                        </div>
                    </header>
                </div>

                @include('partials.alerts', ['toastOffsetTop' => '5.5rem'])

                <div class="role-content">
                    @yield('content')
                </div>
            </main>
        </div>
    @else
        @include('partials.navbar')

        @include('partials.alerts', ['toastOffsetTop' => '4.5rem'])

        <main class="flex-grow-1">
            <div class="floating-wrapper">
                @yield('content')
            </div>
        </main>

        @include('partials.footer')
    @endauth
</div>

<div class="modal fade" id="queueCallModal" tabindex="-1" aria-labelledby="queueCallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="queueCallModalLabel">You're Being Called!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-center">
                <h4 id="queueCallMessage" class="fw-bold text-dark"></h4>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" data-bs-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>

@include('partials.confirm-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@vite(['resources/js/app.js'])

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const roleSidebar = document.getElementById('roleSidebar');
        const roleBackdrop = document.getElementById('roleBackdrop');

        function closeSidebar() {
            if (roleSidebar) {
                roleSidebar.classList.remove('show');
            }

            if (roleBackdrop) {
                roleBackdrop.classList.remove('show');
            }
        }

        if (sidebarToggle && roleSidebar) {
            sidebarToggle.addEventListener('click', function () {
                roleSidebar.classList.toggle('show');

                if (roleBackdrop) {
                    roleBackdrop.classList.toggle('show');
                }
            });
        }

        if (roleBackdrop) {
            roleBackdrop.addEventListener('click', closeSidebar);
        }

        document.querySelectorAll('.role-sidebar-link').forEach((link) => {
            link.addEventListener('click', closeSidebar);
        });

        document.querySelectorAll('select.enhanced-multiselect[multiple]').forEach((el) => {
            try {
                new Choices(el, {
                    removeItemButton: true,
                    shouldSort: false,
                    searchPlaceholderValue: 'Type to search…'
                });
            } catch (e) {
                console.warn('Choices.js failed to initialize:', e);
            }
        });

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');

                if (!btn) return;

                btn.classList.add('loading');
                btn.disabled = true;

                const loadingText = btn.getAttribute('data-loading-text') || 'Processing...';
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + loadingText;
            });
        });

        document.querySelectorAll('.password-toggle').forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetSelector = btn.getAttribute('data-target');
                const input = targetSelector ? document.querySelector(targetSelector) : btn.previousElementSibling;

                if (!input) return;

                const isText = input.getAttribute('type') === 'text';
                input.setAttribute('type', isText ? 'password' : 'text');

                const icon = btn.querySelector('i');

                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }

                btn.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
            });
        });
    });
</script>

@auth
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Echo === 'undefined') {
            console.error('Echo is not available');
            return;
        }

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('VITE_PUSHER_APP_KEY') }}',
            cluster: '{{ env('VITE_PUSHER_APP_CLUSTER', 'mt1') }}',
            forceTLS: true
        });

        const userId = {{ auth()->id() }};

        Echo.private('user.notifications.' + userId)
            .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', function (e) {
                console.log('Notification received:', e);

                if (!e.notification || e.notification.type !== 'queue_next_up') {
                    return;
                }

                const messageElement = document.getElementById('queueCallMessage');

                if (messageElement) {
                    messageElement.innerText = e.notification.message || 'You are being called!';
                }

                const sound = document.getElementById('queueCallSound');

                if (sound) {
                    sound.play().catch(function () {
                        console.log('Audio play failed or was interrupted');
                    });
                }

                const modalElement = document.getElementById('queueCallModal');

                if (modalElement && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
    });
</script>
@endauth

@stack('scripts')
</body>
</html>