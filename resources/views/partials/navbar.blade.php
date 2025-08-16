<nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-lg">
  <div class="container">
    {{-- Brand --}}
    <a class="navbar-brand d-flex align-items-center"
       href="{{ route('welcome') }}">
      <i class="bi bi-heart-pulse-fill me-2" style="font-size: 1.8rem;"></i>
      <span class="fw-bold">CliniQ</span>
    </a>

    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
            aria-controls="navMenu"
            aria-expanded="false"
            aria-label="Toggle navigation">
      <i class="bi bi-list"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      {{-- LEFT SIDE --}}
      <ul class="navbar-nav me-auto">
        @guest
          {{-- Guests see only Find Clinics --}}
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
               href="{{ route('clinics.index') }}">
              <i class="bi bi-building me-1"></i>Find Clinics
            </a>
          </li>
        @else
          @if(auth()->user()->is_admin)
            {{-- Admin sees Manage Clinics + Services --}}
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('admin.clinics.*')) active @endif"
                 href="{{ route('admin.clinics.index') }}">
                <i class="bi bi-building-gear me-1"></i>Manage Clinics
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('admin.services.*')) active @endif"
                 href="{{ route('admin.services.index') }}">
                <i class="bi bi-gear-wide-connected me-1"></i>Manage Services
              </a>
            </li>


          @elseif(auth()->user()->is_secretary)
            {{-- Secretary sees Manage Appointments + Doctors --}}
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('secretary.appointments.*')) active @endif"
                 href="{{ route('secretary.appointments.index') }}">
                <i class="bi bi-calendar-check me-1"></i>Appointments
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('secretary.doctors.*')) active @endif"
                 href="{{ route('secretary.doctors.index') }}">
                <i class="bi bi-person-badge me-1"></i>Doctors
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('secretary.queue.*')) active @endif"
                 href="{{ route('secretary.queue.overview') }}">
                <i class="bi bi-people me-1"></i>Queue
              </a>
            </li>

          @else
            {{-- Patient sees Find Clinics + My Appointments --}}
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('clinics.*')) active @endif"
                 href="{{ route('clinics.index') }}">
                <i class="bi bi-building me-1"></i>Find Clinics
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('appointments.*')) active @endif"
                 href="{{ route('appointments.index') }}">
                <i class="bi bi-calendar-check me-1"></i>My Appointments
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('queue.status')) active @endif"
                 href="{{ route('queue.status') }}">
                <i class="bi bi-clock me-1"></i>Queue Status
              </a>
            </li>
          @endif
        @endguest
      </ul>

      {{-- RIGHT SIDE --}}
      <ul class="navbar-nav ms-auto align-items-center">
        @guest
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light btn-sm me-2" href="{{ route('login') }}">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-light btn-sm" href="{{ route('register') }}">
              <i class="bi bi-person-plus me-1"></i>Register
            </a>
          </li>
        @else
          {{-- Dashboard Link for all users --}}
          <li class="nav-item me-2">
            <a class="nav-link @if(request()->routeIs('dashboard')) active @endif"
               href="{{ route('dashboard') }}" title="Dashboard">
              <i class="bi bi-speedometer2"></i>
            </a>
          </li>

          {{-- Notifications Bell --}}
          <li class="nav-item dropdown me-3">
            @php $unread = auth()->user()->unreadNotifications->count(); @endphp
            <a class="nav-link position-relative"
               href="#"
               id="notifDropdown"
               data-bs-toggle="dropdown"
               aria-expanded="false"
               title="Notifications">
              <i class="bi bi-bell-fill" style="font-size: 1.2rem;"></i>
              @if($unread)
                <span class="position-absolute top-0 start-100 translate-middle
                             badge rounded-pill bg-danger animate-pulse">
                  {{ $unread }}
                </span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notifDropdown" style="min-width:350px;">
              <li class="dropdown-header d-flex align-items-center">
                <i class="bi bi-bell me-2 text-primary"></i>
                <strong>Notifications</strong>
                @if($unread)
                  <span class="badge bg-primary ms-auto">{{ $unread }} new</span>
                @endif
              </li>
              @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $note)
                <li>
                  <a href="{{ route('notifications.index') }}"
                     class="dropdown-item py-2 {{ $note->read_at ? '' : 'fw-bold bg-light' }}">
                    <div class="d-flex align-items-start">
                      <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
                      <div class="flex-grow-1">
                        <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($note->data['message'], 60) }}</div>
                        <small class="text-muted">
                          <i class="bi bi-clock me-1"></i>{{ $note->created_at->diffForHumans() }}
                        </small>
                      </div>
                    </div>
                  </a>
                </li>
              @empty
                <li class="dropdown-item text-center text-muted py-3">
                  <i class="bi bi-bell-slash fs-4 mb-2"></i>
                  <div>No notifications</div>
                </li>
              @endforelse
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-center text-primary fw-semibold"
                   href="{{ route('notifications.index') }}">
                  <i class="bi bi-arrow-right me-1"></i>View All Notifications
                </a>
              </li>
            </ul>
          </li>

          {{-- User dropdown --}}
          <li class="nav-item dropdown">
            <a id="userDropdown"
               class="nav-link dropdown-toggle d-flex align-items-center"
               href="#"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
              <div class="avatar-circle me-2">
                <i class="bi bi-person-fill"></i>
              </div>
              <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
              <li class="dropdown-header">
                <div class="d-flex align-items-center">
                  <div class="avatar-circle me-2">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  <div>
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <small class="text-muted">
                      @if(Auth::user()->is_admin)
                        <i class="bi bi-shield-check me-1"></i>Administrator
                      @elseif(Auth::user()->is_secretary)
                        <i class="bi bi-person-badge me-1"></i>Secretary
                      @else
                        <i class="bi bi-person me-1"></i>Patient
                      @endif
                    </small>
                  </div>
                </div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                  <i class="bi bi-person-circle me-2"></i>My Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                  <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
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
