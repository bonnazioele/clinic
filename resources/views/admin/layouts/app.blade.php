<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – @yield('title')</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

  <!-- Leaflet CSS (for the lat/lng picker) -->
  <link rel="stylesheet"
        href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <style>
    body { background:#f8f9fa; }
    .sidebar { width:200px; }
    .map-picker { height:250px; border:1px solid #ccc; margin-bottom:1rem; }
  </style>
</head>
<body>
  <div class="d-flex">
    <nav class="sidebar bg-light p-3">
      <h5>Admin Panel</h5>
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link @if(request()->routeIs('admin.clinics.*')) active @endif"
             href="{{ route('admin.clinics.index') }}">
            Manage Clinics
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link @if(request()->routeIs('admin.services.*')) active @endif"
             href="{{ route('admin.services.index') }}">
            Manage Services
          </a>
        </li>
        <li class="nav-item mt-3">
          <a class="nav-link text-danger" href="#"
             onclick="event.preventDefault();document.getElementById('logout').submit()">
            Logout
          </a>
          <form id="logout" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </li>
      </ul>
    </nav>

    <div class="flex-fill p-4">
      @include('partials.alerts')
      @yield('content')
    </div>
  </div>

  <!-- Bootstrap JS + dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  @yield('scripts')
</body>
</html>
