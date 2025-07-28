<nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
  <div class="container">
    {{-- Brand --}}
    <a class="navbar-brand"
       href="{{ auth()->check()
           ? (auth()->user()->is_admin
              ? route('admin.clinics.index')
              : (auth()->user()->is_secretary
                 ? route('secretary.appointments.index')
                 : route('dashboard')))
           : route('welcome') }}">
      CliniQ
    </a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
            aria-controls="navMenu"
            aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      {{-- LEFT SIDE --}}
      <ul class="navbar-nav me-auto">
        @guest
          {{-- Guests see only Find Clinics --}}
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
               href="{{ route('clinics.index') }}">
              Find Clinics
            </a>
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
            {{-- Secretary sees Manage Appointments + Doctors --}}
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
                 href="{{ route('clinics.index') }}">
                Find Clinics
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('appointments.*')) active @endif"
                 href="{{ route('appointments.index') }}">
                My Appointments
              </a>
            </li>
          @endif
        @endguest
      </ul>

      {{-- RIGHT SIDE --}}
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
            <a id="userDropdown"
               class="nav-link dropdown-toggle"
               href="#" role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
              {{ Auth::user()->name }}
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li>
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                  My Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                  Edit Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="#"
                   onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                  Logout
                </a>
              </li>
            </ul>

            <form id="logout-form"
                  action="{{ route('logout') }}"
                  method="POST"
                  class="d-none">
              @csrf
            </form>
          </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>
