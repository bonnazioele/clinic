<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name', 'CliniQ')) | CliniQ</title>

  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body {
      min-height: 100vh;
      background:
        radial-gradient(circle at top left, rgba(13, 110, 253, 0.10), transparent 28%),
        linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color: #0f172a;
    }

    .public-navbar-wrap {
      position: sticky;
      top: 0;
      z-index: 1030;
      padding: 0.85rem 1rem;
      background: rgba(248, 251, 255, 0.74);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(226, 232, 240, 0.75);
    }

    .public-navbar {
      width: min(1180px, 100%);
      margin: 0 auto;
      border-radius: 22px;
      border: 1px solid rgba(226, 232, 240, 0.95);
      background: rgba(255, 255, 255, 0.92);
      box-shadow: 0 14px 34px rgba(15, 23, 42, 0.075);
      padding: 0.65rem 0.85rem;
    }

    .public-brand {
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
      text-decoration: none;
      color: #0f172a;
      font-weight: 900;
      letter-spacing: -0.03em;
    }

    .public-brand-logo {
      width: 42px;
      height: 42px;
      display: grid;
      place-items: center;
      border-radius: 15px;
      color: #ffffff;
      background: linear-gradient(135deg, #0d6efd, #1287ff);
      box-shadow: 0 10px 22px rgba(13, 110, 253, 0.23);
      font-size: 1.2rem;
    }

    .public-brand-text span {
      display: block;
      color: #64748b;
      font-size: 0.73rem;
      font-weight: 700;
      letter-spacing: 0;
      margin-top: -0.1rem;
    }

    .public-nav-link {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.55rem 0.8rem;
      border-radius: 999px;
      color: #475569;
      text-decoration: none;
      font-size: 0.88rem;
      font-weight: 800;
      transition: 0.18s ease;
    }

    .public-nav-link:hover,
    .public-nav-link.active {
      color: #0d6efd;
      background: #eff6ff;
    }

    .public-auth-btn {
      border-radius: 999px;
      font-size: 0.86rem;
      font-weight: 800;
      padding: 0.55rem 0.9rem;
    }

    .public-main {
      min-height: calc(100vh - 96px);
    }

    .public-footer {
      padding: 1.2rem 1rem;
      color: #64748b;
      text-align: center;
      font-size: 0.82rem;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .public-navbar {
        border-radius: 18px;
      }

      .public-nav-actions {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 0.7rem;
      }
    }
  </style>

  @stack('styles')
</head>

<body>
  <!-- <header class="public-navbar-wrap">
    <nav class="public-navbar">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="public-brand">
          <span class="public-brand-logo">
            <i class="bi bi-heart-pulse"></i>
          </span>

          <span class="public-brand-text">
            CliniQ
            <span>Clinic services made easier</span>
          </span>
        </a>

        <div class="d-flex align-items-center gap-1 public-nav-actions">
          <a href="{{ Route::has('clinics.index') ? route('clinics.index') : url('/clinics') }}"
             class="public-nav-link {{ request()->routeIs('clinics.index') ? 'active' : '' }}">
            <i class="bi bi-hospital"></i>
            Find Clinics
          </a>

          @guest
            <a href="{{ route('login') }}" class="btn btn-outline-primary public-auth-btn">
              <i class="bi bi-box-arrow-in-right me-1"></i>
              Login
            </a>

            @if(Route::has('register'))
              <a href="{{ route('register') }}" class="btn btn-primary public-auth-btn">
                <i class="bi bi-person-plus me-1"></i>
                Register
              </a>
            @endif
          @else
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
               class="btn btn-primary public-auth-btn">
              <i class="bi bi-speedometer2 me-1"></i>
              Dashboard
            </a>
          @endguest
        </div>
      </div>
    </nav>
  </header> -->

  <main class="public-main">
    @yield('content')
  </main>

  <footer class="public-footer">
    &copy; {{ date('Y') }} CliniQ. All rights reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>