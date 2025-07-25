<nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand"
       href="{{ auth()->check() ? route('dashboard') : route('welcome') }}">
      CliniQ
    </a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMenu"
            aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <!-- Left Side -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
             href="{{ route('clinics.index') }}">
            Find Clinics
          </a>
        </li>
        @auth
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('appointments.*')) active @endif"
               href="{{ route('appointments.index') }}">
              My Appointments
            </a>
          </li>
        @endauth
      </ul>

      <!-- Right Side -->
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
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li>
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                  My Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                  Edit Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
