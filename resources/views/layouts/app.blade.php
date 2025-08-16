<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'CliniQ'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Leaflet CSS (added) -->
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="">

    <!-- Custom CSS -->
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
            --shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            --shadow-lg: 0 20px 40px rgba(0,0,0,.1);
            --shadow-hover: 0 0.5rem 1rem rgba(0,0,0,.15);
        }

        /* Global Styles */
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            line-height: 1.6;
            color: var(--dark-color);
            min-height: 100vh;
        }

        /* Medical Theme Enhancements */
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

        /* Navbar Enhancements */
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            transition: var(--transition);
        }
        .navbar-brand:hover {
            transform: scale(1.05);
            color: #fff !important;
        }
        .nav-link {
            position: relative;
            transition: var(--transition);
            border-radius: var(--border-radius-sm);
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,.1);
            transform: translateY(-1px);
            color: #fff !important;
        }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(255,255,255,.2) 0%, rgba(255,255,255,.1) 100%);
            color: #fff !important;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,.3);
        }

        /* Avatar Circle */
        .avatar-circle {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--medical-blue) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }

        /* Enhanced Cards */
        .clinic-card {
            transition: var(--transition);
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow);
            cursor: pointer;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(30, 136, 229, 0.1);
        }
        .clinic-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: rgba(30, 136, 229, 0.3);
        }

        /* Enhanced Buttons */
        .btn {
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            transition: var(--transition);
            padding: .5rem 1.5rem;
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
        }
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, var(--medical-red) 100%);
        }
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, var(--medical-orange) 100%);
        }

        /* Enhanced Forms */
        .form-control, .form-select {
            border-radius: var(--border-radius-sm);
            border: 2px solid #e9ecef;
            transition: var(--transition);
            padding: .75rem 1rem;
            background: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 .2rem rgba(30, 136, 229, 0.25);
            transform: translateY(-1px);
            background: #fff;
        }

        /* Enhanced Tables */
        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: #fff;
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
            cursor: pointer;
        }
        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(30, 136, 229, 0.05) 0%, rgba(30, 136, 229, 0.1) 100%);
            transform: scale(1.01);
        }

        /* Enhanced Badges */
        .badge {
            border-radius: 1rem;
            font-weight: 500;
            padding: .5rem 1rem;
        }

        /* Enhanced Alerts */
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

        /* Medical Icons */
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

        /* Animations */
        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Enhanced Accessibility */
        .sr-only {
            position: absolute;
            width:1px;
            height:1px;
            padding:0;
            margin:-1px;
            overflow:hidden;
            clip:rect(0,0,0,0);
            white-space:nowrap;
            border:0;
        }

        html { scroll-behavior: smooth; }

        .btn:focus, .form-control:focus, .nav-link:focus {
            outline: 2px solid var(--medical-blue);
            outline-offset: 2px;
        }

        /* Loading States */
        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
            .btn { width: 100%; margin-bottom: .5rem; }
            .clinic-card:hover, .table tbody tr:hover, .btn:hover { transform: none; }
            .navbar-nav .nav-link { text-align: center; margin: 0.25rem 0; }
        }

        /* Footer Enhancements */
        footer {
            margin-top: auto;
        }

        footer a:hover {
            color: var(--primary-color) !important;
            transform: translateX(3px);
            transition: var(--transition);
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(30, 136, 229, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(30, 136, 229, 0.3);
        }

        .dashboard-stat {
            font-size: 2rem;
            font-weight: 700;
            color: var(--medical-blue);
        }

        /* Queue Status Enhancements */
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
    </style>

    @stack('styles')
</head>
<body>
<div id="app" class="d-flex flex-column min-vh-100">
    @include('partials.navbar')

    <main class="py-4 flex-grow-1">
        @yield('content')
    </main>

    @include('partials.footer')
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS (added) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<!-- Enhanced UX scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form loading state
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.classList.add('loading');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }
        });
    });

    // Clickable table rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            const link = this.querySelector('a[href]');
            if (link && !this.querySelector('button, input, select')) {
                window.location.href = link.href;
            }
        });
    });

    // Scroll to top button
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    scrollToTopBtn.className = 'btn btn-primary rounded-circle position-fixed shadow';
    scrollToTopBtn.style.cssText = 'bottom:20px;right:20px;z-index:1000;width:50px;height:50px;display:none;';
    scrollToTopBtn.title = 'Scroll to top';
    document.body.appendChild(scrollToTopBtn);

    window.addEventListener('scroll', () => {
        scrollToTopBtn.style.display = window.pageYOffset > 300 ? 'block' : 'none';
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Keyboard focus style
    document.addEventListener('keydown', e => {
        if (e.key === 'Tab') document.body.classList.add('keyboard-navigation');
    });
    document.addEventListener('mousedown', () => document.body.classList.remove('keyboard-navigation'));

    // Enhanced notifications
    const notificationBadges = document.querySelectorAll('.badge.animate-pulse');
    notificationBadges.forEach(badge => {
        badge.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.clinic-card, .dashboard-card, .medical-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });

    cards.forEach(card => observer.observe(card));

    // Password visibility toggles
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
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

@stack('scripts')
</body>
</html>
