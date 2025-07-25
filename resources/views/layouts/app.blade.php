<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','CliniQ Patient')</title>

  <!-- Bootstrap CSS CDN -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-……"
    crossorigin="anonymous"
  >

  <!-- Leaflet CSS CDN -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"
  />

  <!-- ▶️ Inline Custom Styles -->
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #0056b3 !important;
    }
    .navbar-brand, .navbar .nav-link {
      color: #fff !important;
      font-weight: 500;
    }
    .navbar .nav-link.active {
      text-decoration: underline;
    }
    .map-container {
      height: 300px;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .clinic-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      transition: transform .2s, box-shadow .2s;
    }
    .clinic-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .clinic-card .card-body {
      padding: 1rem;
    }
    .badge {
      margin-right: 0.25rem;
    }
    .input-group .form-control {
      border-right: 0;
    }
    .input-group .btn {
      border-left: 0;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-md">
    <div class="container">
      <a class="navbar-brand" href="{{ url('/') }}">CliniQ</a>
      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
               href="{{ route('clinics.index') }}">Find Clinics</a>
          </li>
          @auth
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('appointments.*')) active @endif"
               href="{{ route('appointments.index') }}">My Appointments</a>
          </li>
          @endauth
        </ul>
        <ul class="navbar-nav ms-auto">
          @guest
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('register') }}">Register</a>
          </li>
          @else
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              {{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
              <li>
                <a class="dropdown-item" href="#"
                   onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                  Logout
                </a>
              </li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
              @csrf
            </form>
          </li>
          @endguest
        </ul>
      </div>
    </div>
  </nav>

  <!-- Alerts & Content -->
  <main class="container py-4">
    @include('partials.alerts')
    @yield('content')
  </main>

  <!-- Bootstrap JS CDN -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-……"
    crossorigin="anonymous"
  ></script>

  <!-- Leaflet JS CDN -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  @yield('scripts')
</body>
</html>
