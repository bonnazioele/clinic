<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @auth
    <meta name="user-id" content="{{ auth()->id() }}">
  @endauth

  <title>@yield('title', 'CliniQ')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

  <style>
    :root {
      --cliniq-blue: #0d6efd;
      --cliniq-blue-2: #178bff;
      --cliniq-bg: #f6faff;
      --cliniq-text: #0f172a;
      --cliniq-muted: #64748b;
      --cliniq-card: #ffffff;
      --cliniq-border: #e2e8f0;
      --cliniq-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
      --cliniq-radius: 24px;

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

    * {
      box-sizing: border-box;
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
      margin: 0;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.13), transparent 25%),
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 22%),
        linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
      color: var(--cliniq-text);
      line-height: 1.6;
    }

    a {
      text-decoration: none;
    }

    #app {
      min-height: 100vh;
    }

    main {
      width: 100%;
    }

    .patient-page {
      min-height: 100vh;
    }

    .patient-main {
      width: 100%;
      padding: 22px 22px 36px;
    }

    .dashboard-container,
    .patient-content-container {
      width: 100%;
      max-width: 1540px;
      margin: 0 auto;
    }

    .page-card,
    .dash-card {
      background: rgba(255, 255, 255, 0.94);
      border: 1px solid rgba(226, 232, 240, 0.96);
      border-radius: var(--cliniq-radius);
      box-shadow: var(--cliniq-shadow);
      padding: 24px;
    }

    .page-card {
      margin-top: 16px;
    }

    .welcome-card,
    .welcome-panel,
    .welcome-banner {
      border-radius: 24px;
      padding: 30px 34px;
      color: #ffffff;
      background:
        radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.15), transparent 18%),
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
      overflow: hidden;
      position: relative;
      margin-bottom: 24px;
    }

    .welcome-card h1,
    .welcome-panel h1,
    .welcome-banner h1 {
      font-size: clamp(26px, 3vw, 36px);
      font-weight: 900;
      letter-spacing: -0.04em;
      margin: 0 0 8px;
    }

    .welcome-card p,
    .welcome-panel p,
    .welcome-banner p {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
      opacity: 0.95;
    }

    .stat-card {
      min-height: 170px;
      border-radius: 20px;
      padding: 24px;
      color: #ffffff;
      position: relative;
      overflow: hidden;
      box-shadow: var(--cliniq-shadow);
      border: 0;
    }

    .stat-card::after {
      content: "";
      position: absolute;
      right: -25px;
      bottom: -25px;
      width: 130px;
      height: 130px;
      border-radius: 40px;
      background: rgba(255, 255, 255, 0.13);
      transform: rotate(3deg);
    }

    .stat-blue {
      background: linear-gradient(135deg, #0866f2, #2993ff);
    }

    .stat-green {
      background: linear-gradient(135deg, #087b3d, #2bbf6a);
    }

    .stat-yellow {
      background: linear-gradient(135deg, #ffd85a, #ffc107);
      color: #162033;
    }

    .stat-cyan {
      background: linear-gradient(135deg, #a9efff, #d9f8ff);
      color: #123047;
    }

    .stat-purple {
      background: linear-gradient(135deg, #7c3aed, #a78bfa);
    }

    .stat-gray {
      background: linear-gradient(135deg, #475569, #94a3b8);
    }

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
      background: rgba(255, 255, 255, 0.18);
      font-size: 30px;
      flex: 0 0 auto;
    }

    .stat-value {
      font-size: 38px;
      font-weight: 900;
      line-height: 1;
      margin-bottom: 8px;
      letter-spacing: -0.04em;
    }

    .stat-title {
      font-size: 16px;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .stat-sub {
      opacity: 0.92;
      margin: 0;
      font-weight: 500;
    }

    .dash-card-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 20px;
    }

    .dash-card-title h2,
    .dash-card-title h3 {
      font-size: 24px;
      font-weight: 900;
      letter-spacing: -0.04em;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .title-icon {
      color: var(--cliniq-blue);
    }

    .appointment-row {
      display: grid;
      grid-template-columns: 86px 1fr auto auto;
      gap: 22px;
      align-items: center;
      padding: 20px 8px;
      border-top: 1px solid var(--cliniq-border);
    }

    .appointment-row:first-child {
      border-top: 0;
    }

    .date-box {
      width: 78px;
      height: 92px;
      border-radius: 16px;
      background: linear-gradient(180deg, var(--cliniq-blue), #238dff);
      color: #ffffff;
      display: grid;
      place-items: center;
      text-align: center;
      box-shadow: 0 12px 24px rgba(13, 110, 253, 0.25);
    }

    .date-box .month {
      font-weight: 900;
      font-size: 15px;
    }

    .date-box .day {
      font-weight: 900;
      font-size: 28px;
      line-height: 1;
    }

    .date-box .weekday {
      font-size: 14px;
    }

    .appointment-name {
      font-size: 18px;
      font-weight: 900;
      margin-bottom: 8px;
    }

    .appointment-meta {
      color: #536178;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 6px;
      font-weight: 500;
    }

    .status-pill {
      border-radius: 999px;
      padding: 8px 14px;
      background: #eef5ff;
      color: var(--cliniq-blue);
      font-weight: 700;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: fit-content;
    }

    .empty-box {
      text-align: center;
      padding: 50px 20px;
      color: var(--cliniq-muted);
    }

    .empty-box i {
      font-size: 58px;
      display: block;
      margin-bottom: 14px;
      color: #64748b;
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

    .activity-list {
      display: flex;
      flex-direction: column;
    }

    .activity-item {
      display: flex;
      gap: 14px;
      padding: 15px 0;
      border-bottom: 1px solid var(--cliniq-border);
    }

    .activity-item:last-child {
      border-bottom: 0;
    }

    .activity-icon {
      color: var(--cliniq-blue);
      font-size: 20px;
      margin-top: 2px;
      flex: 0 0 auto;
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
      border-bottom: 1px solid var(--cliniq-border);
    }

    .recent-activity-card .activity-list,
    .past-appointments-card .past-appointments-list {
      flex: 1;
    }

    .recent-activity-card .activity-item {
      padding: 18px 0;
      min-height: 78px;
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

    .past-appointments-card .history-link-wrap {
      margin-top: auto;
      padding-top: 14px;
      text-align: center;
    }

    .compact .stat-card.mini,
    .stat-card.mini {
      padding: 16px;
      min-height: 110px;
    }

    .compact .stat-value,
    .stat-card.mini .stat-value {
      font-size: 24px;
      margin-bottom: 3px;
    }

    .dash-card.compact {
      padding: 18px;
    }

    .appointment-row.compact {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-top: 1px solid var(--cliniq-border);
    }

    .date-mini {
      font-size: 12px;
      font-weight: 700;
      min-width: 55px;
    }

    .back-top {
      position: fixed;
      right: 28px;
      bottom: 26px;
      width: 54px;
      height: 54px;
      border-radius: 999px;
      border: 0;
      background: linear-gradient(135deg, var(--cliniq-blue), var(--cliniq-blue-2));
      color: #ffffff;
      box-shadow: 0 14px 28px rgba(13, 110, 253, 0.35);
      z-index: 100;
      display: grid;
      place-items: center;
    }

    .medical-gradient {
      background: linear-gradient(135deg, var(--medical-blue) 0%, var(--primary-color) 100%);
      width: 100%;
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
      .patient-main {
        padding: 18px 14px 28px;
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
      .patient-main {
        padding: 14px 10px 24px;
      }

      .page-card,
      .dash-card {
        border-radius: 22px;
        padding: 18px;
      }

      .welcome-card,
      .welcome-panel,
      .welcome-banner {
        padding: 24px 20px;
        border-radius: 22px;
      }

      .stat-card {
        min-height: 140px;
        padding: 20px;
      }

      .stat-icon {
        width: 54px;
        height: 54px;
        font-size: 25px;
      }

      .stat-value {
        font-size: 30px;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }

      .back-top {
        right: 16px;
        bottom: 18px;
        width: 48px;
        height: 48px;
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

  $rawRole = strtolower((string) (
    $user?->role
    ?? $user?->user_type
    ?? $user?->type
    ?? ''
  ));

  if (request()->is('secretary/*') || request()->routeIs('secretary.*')) {
    $roleKey = 'secretary';
  } elseif (request()->is('doctor/*') || request()->routeIs('doctor.*') || $user?->is_doctor) {
    $roleKey = 'doctor';
  } elseif (
    request()->is('owner/*') ||
    request()->is('admin/*') ||
    request()->routeIs('owner.*') ||
    request()->routeIs('admin.*') ||
    in_array($rawRole, ['owner', 'clinic_owner', 'admin', 'clinic_admin', 'super_admin', 'superadmin'], true)
  ) {
    $roleKey = 'owner';
  } elseif (in_array($rawRole, ['secretary', 'staff', 'clinic_staff'], true)) {
    $roleKey = 'secretary';
  } else {
    $roleKey = 'patient';
  }

  $navbarView = "partials.navbars.{$roleKey}-navbar";
  $sidebarView = "partials.sidebars.{$roleKey}-sidebar";
@endphp

<div id="app" class="min-vh-100">
  @auth
    <div class="patient-page">
      @includeIf($navbarView)
      @includeIf($sidebarView)

      @include('partials.alerts', ['toastOffsetTop' => '7rem'])

      <main class="patient-main">
        @yield('content')
      </main>
    </div>
  @else
    <div class="d-flex flex-column min-vh-100">
      @include('partials.navbar')

      @include('partials.alerts', ['toastOffsetTop' => '4.5rem'])

      <main class="flex-grow-1">
        <div class="container py-4">
          @yield('content')
        </div>
      </main>

      @include('partials.footer')
    </div>
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

<button type="button" class="back-top" onclick="window.scrollTo({top:0, behavior:'smooth'})" aria-label="Back to top">
  <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

        if (
          form.hasAttribute('data-confirm')
          || btn.hasAttribute('data-confirm')
          || form.hasAttribute('data-keep-enabled')
          || btn.hasAttribute('data-keep-enabled')
        ) return;

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
      console.warn('Echo is not available');
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
