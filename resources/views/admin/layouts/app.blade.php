
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – @yield('title')</title>

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- Leaflet CSS (for the lat/lng picker) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  
  <!-- Compiled SCSS styles (includes Bootstrap + our modal animations) -->
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <style>
    body { background:#f8f9fa; }
    .sidebar { width:200px; }
    .map-picker { height:250px; border:1px solid #ccc; margin-bottom:1rem; }

    .dropdown-menu.show {
      z-index: 1051; /* higher than Leaflet controls which default around 1000 */
    }

  </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  @livewireStyles
</head>
<body>

  <div class="d-flex min-vh-100 flex-nowrap">
    <!-- Sidebar -->
    <nav class="border-end bg-white flex-shrink-0" id="sidebar-wrapper" style="width: 220px; min-width: 180px;">
      <div class="sidebar-heading border-bottom bg-primary text-white p-3">
        <div class="d-flex align-items-center">
          <div class="me-2">
            <i class="fas fa-hospital fa-2x"></i>
          </div>
          <div>
            <h4 class="mb-0">CliniQ</h4>
            <small class="text-white-50">Admin Portal</small>
          </div>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action py-3 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="{{ route('admin.clinics.index') }}" class="list-group-item list-group-item-action py-3 {{ request()->routeIs('admin.clinics.*') && !request()->routeIs('admin.clinics.create') ? 'active' : '' }}">
          <i class="fas fa-hospital me-2"></i> Clinics
        </a>
        <a href="{{ route('admin.clinic-types.index') }}" class="list-group-item list-group-item-action py-3 {{ request()->routeIs('admin.clinic-types.*') ? 'active' : '' }}">
          <i class="fas fa-building me-2"></i> Clinic Types
        </a>
        <a href="{{ route('admin.services.index') }}" class="list-group-item list-group-item-action py-3 {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
          <i class="fas fa-concierge-bell me-2"></i> Services
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3">
          <i class="fas fa-users me-2"></i> Administrators
        </a>
        <a href="#" class="list-group-item list-group-item-action py-3">
          <i class="fas fa-cog me-2"></i> Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" class="list-group-item list-group-item-action py-3 border-0 p-0" style="background: none;">
          @csrf
          <button type="submit" class="btn btn-link w-100 text-start px-3 py-3" style="text-decoration: none; color: inherit;">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </button>
        </form>
      </div>
    </nav>

    <main class="flex-grow-1 p-4" style="min-width:0;">
      @include('partials.alerts')
      @yield('content')

      {{ $slot ?? '' }}

      @yield('scripts')
    </main>
  </div>
  @stack('scripts')
@livewireScripts
</body>

