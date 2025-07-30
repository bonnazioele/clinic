<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','CliniQ')</title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-……"
    crossorigin="anonymous"
  >

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"
  />

  <!-- Bootstrap Icons CSS (for notification bell) -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
  />

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
    .clinic-card .card-body { padding: 1rem; }
    .badge { margin-right: .25rem; }
    .input-group .form-control { border-right: 0; }
    .input-group .btn { border-left: 0; }
  </style>
</head>
<body>
  {{-- Navbar --}}
  <nav class="navbar navbar-expand-md">
    <div class="container">
      @php
        if(auth()->check()) {
          if(auth()->user()->is_admin) {
            $home = route('admin.clinics.index');
          } elseif(auth()->user()->is_secretary) {
            $home = route('secretary.appointments.index');
          } else {
            $home = route('dashboard');
          }
        } else {
          $home = route('welcome');
        }
      @endphp

      <a class="navbar-brand" href="{{ $home }}">CliniQ</a>
      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navMenu"
              aria-controls="navMenu"
              aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        {{-- Left links --}}
        <ul class="navbar-nav me-auto">
          @guest
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
                 href="{{ route('clinics.index') }}">Find Clinics</a>
            </li>
          @else
            @if(auth()->user()->is_admin)
              {{-- Admin sees Manage Clinics + Services --}}
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

            @elseif(auth()->user()->is_secretary)
              {{-- Secretary sees Manage Appointments + Manage Doctors --}}
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('secretary.appointments.*')) active @endif"
                   href="{{ route('secretary.appointments.index') }}">
                  Manage Appointments
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('secretary.doctors.*')) active @endif"
                   href="{{ route('secretary.doctors.index') }}">
                  Manage Doctors
                </a>
              </li>

            @else
              {{-- Patient sees Find Clinics + My Appointments --}}
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
                   href="{{ route('clinics.index') }}">Find Clinics</a>
              </li>
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('appointments.*')) active @endif"
                   href="{{ route('appointments.index') }}">My Appointments</a>
              </li>
            @endif
          @endguest
        </ul>

        {{-- Right dropdown --}}
        <ul class="navbar-nav ms-auto">
          @guest
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
          @else
            {{-- Notifications Bell --}}
            <li class="nav-item dropdown me-3">
              @php $unread = auth()->user()->unreadNotifications->count(); @endphp
              <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-bell" style="font-size:1.2rem;"></i>
                @if($unread)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $unread }}
                  </span>
                @endif
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:300px;">
                @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $note)
                  <li>
                    <a class="dropdown-item {{ $note->read_at ? '' : 'fw-bold' }}"
                       href="{{ route('notifications.index') }}">
                      {{ \Illuminate\Support\Str::limit($note->data['message'], 50) }}
                      <br><small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                    </a>
                  </li>
                @empty
                  <li><span class="dropdown-item text-muted">No notifications</span></li>
                @endforelse
                <li><hr class="dropdown-divider"></li>
                <li class="text-center">
                  <a class="dropdown-item" href="{{ route('notifications.index') }}">View All</a>
                </li>
              </ul>
            </li>

            {{-- User dropdown --}}
            <li class="nav-item dropdown">
              <a id="userDropdown"
                 class="nav-link dropdown-toggle"
                 href="#"
                 data-bs-toggle="dropdown"
                 aria-expanded="false">
                {{ Auth::user()->name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="{{ route('profile.show') }}">My Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Edit Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="#"
                     onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                  </a>
                </li>
              </ul>
            </li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
          @endguest
        </ul>
      </div>
    </div>
  </nav>

  {{-- Alerts & Content --}}
  <main class="container py-4">
    @yield('content')
  </main>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-……" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  @yield('scripts')
</body>
</html>
