<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Admin Portal') | CliniQ</title>

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root {
      --admin-primary: #0d6efd;
      --admin-primary-2: #178bff;
      --admin-bg: #edf7ff;
      --admin-text: #0f172a;
      --admin-muted: #64748b;
      --admin-border: rgba(15, 23, 42, 0.08);
      --admin-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
      --transition: 0.2s ease;
      --border-radius: 24px;
      --border-radius-sm: 16px;
    }

    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      min-height: 100vh;
      margin: 0;
      font-family: "Instrument Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--admin-text);
      background:
        radial-gradient(circle at top left, rgba(13, 110, 253, 0.13), transparent 30%),
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.14), transparent 30%),
        linear-gradient(180deg, #f8fbff 0%, #edf7ff 48%, #f8fafc 100%);
      overflow-x: hidden;
    }

    a {
      text-decoration: none;
    }

    .admin-page {
      min-height: 100vh;
      width: 100%;
    }

    /*
      Important:
      No left padding here.
      The admin sidebar is slide-in like secretary sidebar,
      so content should occupy the full width.
    */
    .admin-main {
      width: 100%;
      min-height: 100vh;
    }

    /*
      Important:
      No 100px top padding here.
      The navbar is sticky and already consumes its own space.
    */
    .admin-content-wrap {
      width: 100%;
      padding: 28px 24px 44px;
    }

    .admin-content-inner {
      width: min(100%, 1540px);
      margin: 0 auto;
    }

    .medical-card,
    .dashboard-card,
    .card {
      border: 1px solid var(--admin-border) !important;
      border-radius: 28px !important;
      background: rgba(255, 255, 255, 0.84) !important;
      box-shadow: var(--admin-shadow) !important;
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      overflow: hidden;
    }

    .card-header {
      border-bottom: 1px solid var(--admin-border) !important;
      background:
        radial-gradient(circle at top left, rgba(13, 110, 253, 0.14), transparent 32%),
        linear-gradient(135deg, #ffffff, #eef7ff) !important;
      color: var(--admin-text) !important;
      padding: 22px 24px !important;
    }

    .medical-card h1,
    .medical-card h2,
    .medical-card h3,
    .medical-card h4,
    .medical-card h5,
    .dashboard-card h1,
    .dashboard-card h2,
    .dashboard-card h3,
    .dashboard-card h4,
    .dashboard-card h5,
    .card h1,
    .card h2,
    .card h3,
    .card h4,
    .card h5 {
      font-weight: 900;
      letter-spacing: -0.02em;
    }

    .medical-icon {
      color: var(--admin-primary);
    }

    .form-control,
    .form-select {
      border-radius: 16px;
      border: 1px solid rgba(15, 23, 42, 0.12);
      padding: 12px 14px;
      color: var(--admin-text);
      background-color: #ffffff;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: rgba(13, 110, 253, 0.55);
      box-shadow: 0 0 0 0.22rem rgba(13, 110, 253, 0.12);
    }

    .form-label {
      color: #334155;
      font-weight: 800;
    }

    .btn {
      border-radius: 15px;
      font-weight: 800;
    }

    .btn-primary {
      border-color: var(--admin-primary);
      background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-2));
      box-shadow: 0 10px 20px rgba(13, 110, 253, 0.18);
    }

    .btn-primary:hover {
      border-color: #0b5ed7;
      background: linear-gradient(135deg, #0b5ed7, #0d6efd);
      transform: translateY(-1px);
    }

    .btn-light,
    .btn-outline-secondary,
    .btn-outline-primary,
    .btn-outline-danger {
      border-radius: 15px;
      font-weight: 800;
    }

    .breadcrumb {
      margin-bottom: 10px;
    }

    .breadcrumb a {
      color: var(--admin-primary);
      font-weight: 800;
    }

    .table {
      --bs-table-bg: transparent;
    }

    .table thead th {
      color: #475569;
      font-size: 0.76rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      background: #f8fafc !important;
      border-bottom: 1px solid var(--admin-border);
      padding: 15px 14px;
    }

    .table tbody td {
      border-color: rgba(15, 23, 42, 0.06);
      padding: 16px 14px;
    }

    .alert {
      border: 0;
      border-radius: 20px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .dropdown-menu {
      border: 1px solid var(--admin-border);
      border-radius: 18px;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    @media (max-width: 991.98px) {
      .admin-content-wrap {
        padding: 22px 16px 36px;
      }
    }

    @media (max-width: 767.98px) {
      .admin-content-wrap {
        padding: 18px 12px 30px;
      }

      .medical-card,
      .dashboard-card,
      .card {
        border-radius: 22px !important;
      }
    }
  </style>

  @stack('styles')
</head>

<body>
  <div class="admin-page">
    @include('partials.sidebars.admin-sidebar')

    <div class="admin-main">
      @include('partials.navbars.admin-navbar')

      <main class="admin-content-wrap">
        <div class="admin-content-inner">
          @yield('content')
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>