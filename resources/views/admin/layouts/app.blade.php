<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin – @yield('title') | {{ config('app.name', 'CliniQ') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Leaflet CSS (for the lat/lng picker) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

  <!-- Custom CSS -->
  <style>
    body {
      font-family: 'Figtree', sans-serif;
      background: #f8f9fa;
    }

    .sidebar {
      width: 250px;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .map-picker {
      height: 250px;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }

    .dropdown-menu.show {
      z-index: 1051;
    }

    .sidebar-heading {
      background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
      position: relative;
      overflow: hidden;
    }

    .sidebar-heading::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      transition: left 0.5s;
    }

    .sidebar-heading:hover::before {
      left: 100%;
    }

    .list-group-item {
      border: none;
      border-radius: 0;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .list-group-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 0;
      background: linear-gradient(90deg, rgba(13, 110, 253, 0.1), transparent);
      transition: width 0.3s ease;
    }

    .list-group-item:hover::before {
      width: 100%;
    }

    .list-group-item:hover {
      background-color: #f8f9fa;
      transform: translateX(8px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .list-group-item.active {
      background: linear-gradient(135deg, #e7f1ff 0%, #d1e7ff 100%);
      color: #0d6efd;
      border-left: 4px solid #0d6efd;
      font-weight: 600;
      transform: translateX(5px);
    }

    .list-group-item.active::before {
      display: none;
    }

    .card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      transition: all 0.2s ease;
    }

    .card:hover {
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

    .btn {
      border-radius: 0.5rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    .table {
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .table thead th {
      background-color: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      color: #495057;
    }
  </style>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  @livewireStyles
</head>
<body>

  <div class="d-flex min-vh-100 flex-nowrap">
    <!-- Sidebar -->
    <nav class="border-end bg-white flex-shrink-0 sidebar" id="sidebar-wrapper">
      <div class="sidebar-heading border-bottom text-white p-4">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <i class="bi bi-heart-pulse-fill fs-1 text-white"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0">CliniQ</h4>
            <small class="text-white-50 opacity-75">Admin Portal</small>
          </div>
        </div>
      </div>

      <div class="list-group list-group-flush">
        <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action py-3 px-4 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <i class="bi bi-speedometer2 me-3"></i> Dashboard
        </a>
        <a href="{{ route('admin.clinics.index') }}" class="list-group-item list-group-item-action py-3 px-4 {{ request()->routeIs('admin.clinics.*') && !request()->routeIs('admin.clinics.create') ? 'active' : '' }}">
          <i class="bi bi-hospital me-3"></i> Clinics
        </a>
        <a href="{{ route('admin.clinic-types.index') }}" class="list-group-item list-group-item-action py-3 px-4 {{ request()->routeIs('admin.clinic-types.*') ? 'active' : '' }}">
          <i class="bi bi-building me-3"></i> Clinic Types
        </a>
        <a href="{{ route('admin.services.index') }}" class="list-group-item list-group-item-action py-3 px-4 {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
          <i class="bi bi-gear me-3"></i> Services
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3 px-4">
          <i class="bi bi-people me-3"></i> Administrators
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3 px-4">
          <i class="bi bi-sliders me-3"></i> Settings
        </a>

        <div class="mt-auto p-3">
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100 rounded-pill">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
          </form>
        </div>
      </div>
    </nav>

    <main class="flex-grow-1 p-4" style="min-width:0;">
      @include('partials.alerts')
      @yield('content')

      {{ $slot ?? '' }}

      @yield('scripts')
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
  @livewireScripts
</body>
</html>
