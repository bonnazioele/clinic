<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'CliniQ'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 0.75rem;
            --transition: all 0.3s ease;
        }
        body { font-family: 'Figtree', sans-serif; background-color: var(--light-color); line-height: 1.6; color: var(--dark-color); }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; transition: var(--transition); }
        .navbar-brand:hover { transform: scale(1.05); }
        .nav-link { position: relative; transition: var(--transition); border-radius: 0.5rem; padding: 0.5rem 1rem !important; }
        .nav-link:hover { background-color: rgba(13,110,253,.1); transform: translateY(-1px); }
        .nav-link.active { background-color: var(--primary-color); color: #fff !important; font-weight: 600; }
        .clinic-card { transition: var(--transition); border-radius: var(--border-radius); border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); cursor:pointer; }
        .clinic-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,.1); }
        .btn { border-radius: .5rem; font-weight: 500; transition: var(--transition); padding: .5rem 1.5rem; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 .25rem .5rem rgba(0,0,0,.15); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, #0a58ca 100%); border: none; }
        .form-control, .form-select { border-radius: .5rem; border: 2px solid #e9ecef; transition: var(--transition); padding: .75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); transform: translateY(-1px); }
        .table { border-radius: var(--border-radius); overflow: hidden; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
        .table thead th { background: linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%); border-bottom: 2px solid #dee2e6; font-weight: 600; color: var(--dark-color); padding: 1rem; }
        .table tbody tr { transition: var(--transition); cursor:pointer; }
        .table tbody tr:hover { background: linear-gradient(135deg, rgba(13,110,253,.05) 0%, rgba(13,110,253,.1) 100%); transform: scale(1.01); }
        .badge { border-radius: 1rem; font-weight: 500; padding: .5rem 1rem; }
        .alert { border-radius: var(--border-radius); border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
        .sr-only { position: absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        html { scroll-behavior: smooth; }
        .btn:focus, .form-control:focus, .nav-link:focus { outline: 2px solid var(--primary-color); outline-offset: 2px; }
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
            .btn { width: 100%; margin-bottom: .5rem; }
            .clinic-card:hover, .table tbody tr:hover, .btn:hover { transform: none; }
        }
    </style>

    @stack('styles')
</head>
<body>
<div id="app">
    @include('partials.navbar')

    <main class="py-4">
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
            if (link && !this.querySelector('button, input, select')) window.location.href = link.href;
        });
    });

    // Scroll to top button
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    scrollToTopBtn.className = 'btn btn-primary rounded-circle position-fixed';
    scrollToTopBtn.style.cssText = 'bottom:20px;right:20px;z-index:1000;width:50px;height:50px;display:none;';
    scrollToTopBtn.title = 'Scroll to top';
    document.body.appendChild(scrollToTopBtn);
    window.addEventListener('scroll', () => scrollToTopBtn.style.display = window.pageYOffset > 300 ? 'block' : 'none');
    scrollToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // Keyboard focus style
    document.addEventListener('keydown', e => { if (e.key === 'Tab') document.body.classList.add('keyboard-navigation'); });
    document.addEventListener('mousedown', () => document.body.classList.remove('keyboard-navigation'));
});
</script>

@stack('scripts')
</body>
</html>
