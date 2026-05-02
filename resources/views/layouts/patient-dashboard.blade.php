<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') | CliniQ</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --sidebar: #071827;
      --blue: #0d6efd;
      --bg: #f6faff;
      --text: #172033;
      --muted: #697386;
      --card: #ffffff;
      --border: #e7edf5;
      --shadow: 0 14px 35px rgba(15, 23, 42, .08);
      --radius: 18px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .patient-shell {
      display: flex;
      min-height: 100vh;
      background:
        radial-gradient(circle at top right, rgba(13,110,253,.08), transparent 35%),
        linear-gradient(180deg, #f9fcff 0%, #f1f7ff 100%);
    }

    .patient-sidebar {
      width: 86px;
      background: linear-gradient(180deg, #061727 0%, #0b2035 100%);
      color: white;
      padding: 22px 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: sticky;
      top: 0;
      height: 100vh;
      z-index: 50;
      box-shadow: 8px 0 28px rgba(2, 8, 23, .18);
    }

    .sidebar-logo {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      color: #4aa3ff;
      font-size: 32px;
      margin-bottom: 28px;
      text-decoration: none;
    }

    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 14px;
      width: 100%;
      align-items: center;
    }

    .sidebar-link {
      width: 54px;
      height: 54px;
      border-radius: 15px;
      display: grid;
      place-items: center;
      color: #64748b;
      text-decoration: none;
      font-size: 23px;
      transition: .2s ease;
      cursor: pointer;
    }

    .sidebar-link.active,
    .sidebar-logo.active {
      background: #0d6efd;
      color: #fff;
    }

    .sidebar-link:hover,
    .sidebar-logo:hover {
      background: rgba(13, 110, 253, 0.15);
      color: #0d6efd;
    }

    .sidebar-link:not(.active),
    .sidebar-logo:not(.active) {
      background: transparent;
      color: #64748b;
    }

    .sidebar-bottom {
      margin-top: auto;
    }

    .patient-main {
      flex: 1;
      min-width: 0;
      padding: 24px 36px 40px;
      zoom: 0.8;
    }

    .patient-navbar {
      height: 78px;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 22px;
      box-shadow: var(--shadow);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      padding: 0 24px;
      margin-bottom: 22px;
      position: sticky;
      top: 18px;
      z-index: 40;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: var(--text);
    }

    .nav-brand-icon {
      width: 48px;
      height: 48px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, #0d6efd, #178bff);
      color: #fff;
      font-size: 28px;
      box-shadow: 0 12px 24px rgba(13, 110, 253, .28);
    }

    .nav-brand-text {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: #0f172a;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .notification-btn {
      width: 46px;
      height: 46px;
      border: 1px solid var(--border);
      border-radius: 50%;
      background: #fff;
      font-size: 21px;
      color: #1f2937;
      display: grid;
      place-items: center;
      position: relative;
      text-decoration: none;
      transition: .2s ease;
    }

    .notification-btn:hover {
      color: var(--blue);
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
    }

    .avatar-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid var(--border);
      background: #fff;
      color: #475569;
      border-radius: 999px;
      padding: 7px 12px 7px 7px;
      transition: .2s ease;
    }

    .avatar-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
    }

    .avatar-circle {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, #a7c0ff, #dbe7ff);
      color: white;
      font-weight: 800;
    }

    .welcome-card {
      width: 100%;
      min-height: 128px;
      margin-bottom: 28px;
      padding: 24px 28px;
      border-radius: 22px;
      background: linear-gradient(120deg, #3b82f6 0%, #2563eb 60%, #1d4ed8 100%);
      color: #fff;
      box-shadow: 0 18px 35px rgba(37, 99, 235, .22);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 22px;
    }

    .welcome-card::before {
      content: "";
      position: absolute;
      right: -70px;
      top: -80px;
      width: 230px;
      height: 230px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .10);
      pointer-events: none;
    }

    .welcome-card::after {
      content: "";
      position: absolute;
      right: 105px;
      bottom: -85px;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .06);
      pointer-events: none;
    }

    .welcome-content {
      position: relative;
      z-index: 2;
    }

    .welcome-content h1 {
      font-size: clamp(24px, 3vw, 34px);
      font-weight: 800;
      margin: 0 0 8px;
      color: #fff;
    }

    .welcome-content p {
      margin: 0;
      color: rgba(255,255,255,.9);
      font-size: 16px;
      font-weight: 500;
    }

    .book-appointment-btn {
      position: relative;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: #fff;
      color: #2563eb;
      padding: 13px 20px;
      border-radius: 15px;
      text-decoration: none;
      font-size: 15px;
      font-weight: 800;
      white-space: nowrap;
      box-shadow: 0 12px 24px rgba(15, 23, 42, .16);
      transition: .2s ease;
    }

    .book-appointment-btn:hover {
      color: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 16px 30px rgba(15, 23, 42, .22);
    }

    .dashboard-container {
      max-width: 1540px;
      margin: 0 auto;
    }

    .stat-card {
      min-height: 170px;
      border-radius: 18px;
      padding: 24px;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow);
    }

    .stat-card::after {
      content: "";
      position: absolute;
      right: -25px;
      bottom: -25px;
      width: 130px;
      height: 130px;
      border-radius: 40px;
      background: rgba(255,255,255,.12);
    }

    .stat-blue { background: linear-gradient(135deg, #0866f2, #2993ff); }
    .stat-green { background: linear-gradient(135deg, #087b3d, #2bbf6a); }
    .stat-yellow { background: linear-gradient(135deg, #ffd85a, #ffc107); color: #162033; }
    .stat-cyan { background: linear-gradient(135deg, #a9efff, #d9f8ff); color: #123047; }

    .stat-content {
      display: flex;
      align-items: flex-start;
      gap: 18px;
      position: relative;
      z-index: 2;
    }

    .stat-icon {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      background: rgba(255,255,255,.18);
      font-size: 30px;
    }

    .stat-value {
      font-size: 38px;
      font-weight: 800;
      line-height: 1;
      margin-bottom: 8px;
    }

    .stat-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .stat-sub {
      opacity: .9;
      margin: 0;
    }

    .dash-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 28px;
    }

    .dash-card-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .dash-card-title h2 {
      font-size: 22px;
      font-weight: 800;
      margin: 0;
    }

    .title-icon {
      color: var(--blue);
      margin-right: 10px;
    }

    .appointment-row {
      display: grid;
      grid-template-columns: 86px 1fr auto auto;
      gap: 22px;
      align-items: center;
      padding: 20px 8px;
      border-top: 1px solid var(--border);
    }

    .appointment-row:first-child {
      border-top: 0;
    }

    .date-box {
      width: 78px;
      height: 92px;
      border-radius: 14px;
      background: linear-gradient(180deg, #0d6efd, #238dff);
      color: white;
      display: grid;
      place-items: center;
      text-align: center;
      box-shadow: 0 12px 24px rgba(13, 110, 253, .25);
    }

    .date-box .month {
      font-weight: 800;
      font-size: 15px;
    }

    .date-box .day {
      font-weight: 800;
      font-size: 28px;
      line-height: 1;
    }

    .date-box .weekday {
      font-size: 14px;
    }

    .appointment-name {
      font-size: 18px;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .appointment-meta {
      color: #536178;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 6px;
    }

    .status-pill {
      border-radius: 999px;
      padding: 8px 14px;
      background: #eef5ff;
      color: #0d6efd;
      font-weight: 600;
      font-size: 14px;
    }

    .empty-box {
      text-align: center;
      padding: 50px 20px;
      color: var(--muted);
    }

    .empty-box i {
      font-size: 58px;
      display: block;
      margin-bottom: 14px;
    }

    .info-card {
      background: linear-gradient(135deg, #e8fbff, #f5ffff);
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin: 18px 0 24px;
    }

    .activity-item {
      display: flex;
      gap: 14px;
      padding: 13px 0;
      border-bottom: 1px solid var(--border);
    }

    .activity-item:last-child {
      border-bottom: 0;
    }

    .activity-icon {
      color: var(--blue);
      font-size: 20px;
      margin-top: 2px;
    }

    .back-top {
      position: fixed;
      right: 34px;
      bottom: 30px;
      width: 58px;
      height: 58px;
      border-radius: 50%;
      border: 0;
      background: linear-gradient(135deg, #0d6efd, #178bff);
      color: white;
      box-shadow: 0 14px 28px rgba(13, 110, 253, .35);
      z-index: 100;
    }

    .dashboard-pair-card {
      height: 100%;
      min-height: 642px;
      display: flex;
      flex-direction: column;
    }

    .dashboard-pair-card .dash-card-title {
      margin-bottom: 0;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--border);
    }

    .recent-activity-card .activity-list,
    .past-appointments-card .past-appointments-list {
      flex: 1;
    }

    .recent-activity-card .activity-item {
      padding: 18px 0;
      min-height: 78px;
    }

    .recent-activity-card .activity-item:first-child,
    .past-appointments-card .appointment-row:first-child {
      border-top: 0;
    }

    .past-appointments-card .appointment-row {
      grid-template-columns: 86px 1fr auto;
      gap: 22px;
      padding: 20px 8px;
      margin-bottom: 0 !important;
      min-height: 124px;
    }

    .past-appointments-card .status-pill {
      justify-self: end;
      align-self: center;
      background: #eef5ff;
      padding: 8px 14px;
    }

    .past-appointments-card .status-pill .badge {
      font-size: 11px;
      padding: 6px 10px;
      border-radius: 999px;
    }

    .past-appointments-card .history-link-wrap {
      margin-top: auto;
      padding-top: 14px;
      text-align: center;
    }

    .compact .stat-card.mini {
      padding: 14px;
      min-height: 90px;
    }

    .compact .stat-value {
      font-size: 22px;
      margin-bottom: 2px;
    }

    .compact small {
      font-size: 12px;
    }

    .dash-card.compact {
      padding: 16px;
    }

    .appointment-row.compact {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 0;
      border-top: 1px solid #eee;
    }

    .date-mini {
      font-size: 12px;
      font-weight: 600;
      min-width: 55px;
    }

    .patient-page-shell {
      display: flex;
      min-height: calc(100vh - 0px);
      background: #f6faff;
    }

    .patient-page-content {
      flex: 1;
      min-width: 0;
      padding: 24px;
    }

    @media (max-width: 992px) {
      .patient-sidebar {
        width: 74px;
      }

      .patient-main {
        padding: 22px 18px;
      }

      .patient-navbar {
        top: 12px;
      }

      .appointment-row {
        grid-template-columns: 70px 1fr;
      }

      .appointment-row .status-pill,
      .appointment-row .btn {
        grid-column: 2;
        width: fit-content;
      }

      .dashboard-pair-card {
        min-height: auto;
      }

      .past-appointments-card .appointment-row {
        grid-template-columns: 70px 1fr;
      }

      .past-appointments-card .status-pill {
        grid-column: 2;
        justify-self: start;
      }
    }

    @media (max-width: 768px) {
      .patient-shell {
        display: block;
      }

      .patient-sidebar {
        width: 100%;
        height: auto;
        position: static;
        flex-direction: row;
        justify-content: space-between;
        padding: 12px;
      }

      .sidebar-logo {
        margin-bottom: 0;
      }

      .sidebar-nav {
        flex-direction: row;
        justify-content: center;
        gap: 8px;
      }

      .sidebar-link {
        width: 44px;
        height: 44px;
        font-size: 19px;
      }

      .sidebar-bottom {
        margin-top: 0;
      }

      .patient-navbar {
        position: static;
        height: auto;
        flex-direction: column;
        align-items: stretch;
        padding: 16px;
      }

      .topbar-actions {
        justify-content: flex-end;
      }

      .welcome-card {
        flex-direction: column;
        align-items: flex-start;
      }

      .book-appointment-btn {
        width: 100%;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  @stack('styles')
</head>

<body>
  <div class="patient-shell">
    <aside class="patient-sidebar">
      <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
         class="sidebar-logo {{ request()->routeIs('dashboard') ? 'active' : '' }}"
         title="Dashboard">
        <i class="bi bi-heart-pulse"></i>
      </a>

      <nav class="sidebar-nav">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           title="Dashboard">
          <i class="bi bi-house-heart"></i>
        </a>

        <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}"
           class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}"
           title="Appointments">
          <i class="bi bi-calendar-check"></i>
        </a>

        <a href="{{ Route::has('queue.status') ? route('queue.status') : url('/queue/status') }}"
           class="sidebar-link {{ request()->routeIs('queue.*') ? 'active' : '' }}"
           title="Queue">
          <i class="bi bi-people"></i>
        </a>

        <a href="{{ Route::has('clinics.index') ? route('clinics.index') : url('/clinics') }}"
           class="sidebar-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}"
           title="Clinics">
          <i class="bi bi-hospital"></i>
        </a>

        <a href="{{ Route::has('profile.show') ? route('profile.show') : url('/profile') }}"
           class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
           title="Profile">
          <i class="bi bi-person"></i>
        </a>
      </nav>

      <div class="sidebar-bottom">
        <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
          @csrf
          <button type="submit" class="sidebar-link border-0 bg-transparent" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </form>
      </div>
    </aside>

    <main class="patient-main">
      <nav class="patient-navbar">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="nav-brand">
          <span class="nav-brand-icon">
            <i class="bi bi-heart-pulse"></i>
          </span>
          <span class="nav-brand-text">CliniQ</span>
        </a>

        <div class="topbar-actions">
          <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="notification-btn" title="Notifications">
            <i class="bi bi-bell"></i>
          </a>

          <button class="avatar-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar-circle">
              {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </span>
            <span class="d-none d-md-inline fw-semibold">
              {{ Auth::user()->name ?? 'Patient' }}
            </span>
            <i class="bi bi-chevron-down"></i>
          </button>

          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : url('/profile') }}">
                <i class="bi bi-person me-2"></i>Profile
              </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
              <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}" class="d-inline w-100">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      </nav>

      <div class="welcome-card">
        <div class="welcome-content">
          @php
            $title = 'Patient Dashboard';
            $subtitle = "Here's what's happening with your health journey.";

            if (request()->routeIs('appointments.*')) {
                $title = 'My Appointments';
                $subtitle = 'View and manage your appointments';
            } elseif (request()->routeIs('queue.*')) {
                $title = 'Queue';
                $subtitle = 'Track your queue status';
            } elseif (request()->routeIs('clinics.*')) {
                $title = 'Clinics';
                $subtitle = 'Browse clinics and services';
            } elseif (request()->routeIs('profile.*')) {
                $title = 'My Profile';
                $subtitle = 'Manage your account';
            }
          @endphp

          @if(request()->routeIs('dashboard'))
            <h1>Welcome back, {{ Auth::user()->name ?? 'Patient' }}! 👋</h1>
            <p><i class="bi bi-heart-pulse me-2"></i>{{ $subtitle }}</p>
          @else
            <h1>{{ $title }}</h1>
            <p><i class="bi bi-info-circle me-2"></i>{{ $subtitle }}</p>
          @endif
        </div>

        @if(!request()->routeIs('appointments.create') && Route::has('appointments.create'))
          <a href="{{ route('appointments.create') }}" class="book-appointment-btn">
            <i class="bi bi-calendar-plus"></i>
            <span>Book Appointment</span>
          </a>
        @endif
      </div>

      @yield('content')
    </main>
  </div>

  <button class="back-top" onclick="window.scrollTo({top:0, behavior:'smooth'})">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>