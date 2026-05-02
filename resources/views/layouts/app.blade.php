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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
        | GLOBAL PATIENT CONTENT WIDTH
        |--------------------------------------------------------------------------
        | This makes all yielded patient pages occupy 90% of the screen width.
        | Example: appointments, queue, clinics, profile, dashboard.
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

        @media (max-width: 768px) {
            .floating-wrapper {
                width: 94%;
            }
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

        @media (max-width: 767px) {
            .position-fixed[style*="top:"] {
                top: 5rem !important;
                right: 1rem !important;
                left: 1rem !important;
                width: auto !important;
                max-width: none !important;
            }
        }

        @media (max-width: 768px) {
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
<div id="app" class="d-flex flex-column min-vh-100">
    @include('partials.navbar')

    @include('partials.alerts', ['toastOffsetTop' => '4.5rem'])

    <main class="flex-grow-1">
        <div class="floating-wrapper">
            @yield('content')
        </div>
    </main>

    @include('partials.footer')
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
@stack('modals')
</body>
</html>