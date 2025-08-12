<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    {{-- Brand --}}
    <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('welcome') }}"
       title="CliniQ - Your Healthcare Partner">
      <i class="bi bi-heart-pulse-fill me-2 fs-4"></i>
      <span>CliniQ</span>
    </a>

    {{-- Mobile Toggle --}}
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    {{-- Navigation Links --}}
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        @guest
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}"
               href="{{ route('clinics.index') }}"
               title="Search and find healthcare clinics near you">
              <i class="bi bi-search me-2"></i>
              <span>Find Clinics</span>
            </a>
          </li>
        @else
          @if(auth()->user()->is_admin)
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                 href="{{ route('admin.dashboard') }}"
                 title="Access administrative functions">
                <i class="bi bi-gear me-2"></i>
                <span>Admin Panel</span>
              </a>
            </li>
          @elseif(auth()->user()->is_secretary)
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('secretary.appointments.*') ? 'active' : '' }}"
                 href="{{ route('secretary.appointments.index') }}"
                 title="Manage clinic appointments">
                <i class="bi bi-calendar-check me-2"></i>
                <span>Appointments</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('secretary.doctors.*') ? 'active' : '' }}"
                 href="{{ route('secretary.doctors.index') }}"
                 title="Manage clinic doctors">
                <i class="bi bi-person-badge me-2"></i>
                <span>Doctors</span>
              </a>
            </li>
          @else
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}"
                 href="{{ route('clinics.index') }}"
                 title="Search and find healthcare clinics">
                <i class="bi bi-search me-2"></i>
                <span>Find Clinics</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}"
                 href="{{ route('appointments.index') }}"
                 title="View and manage your appointments">
                <i class="bi bi-calendar-event me-2"></i>
                <span>My Appointments</span>
              </a>
            </li>
          @endif
        @endguest
      </ul>

      {{-- Right Side --}}
      <ul class="navbar-nav ms-auto">
        @guest
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light btn-sm rounded-pill px-3" href="{{ route('login') }}"
               title="Sign in to your account">
              <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </a>
          </li>
          <li class="nav-item ms-2">
            <a class="nav-link btn btn-light btn-sm rounded-pill px-3" href="{{ route('register') }}"
               title="Create a new account">
              <i class="bi bi-person-plus me-2"></i>Register
            </a>
          </li>
        @else
          {{-- Notifications --}}
          <li class="nav-item dropdown me-3">
            @php $unread = auth()->user()->unreadNotifications->count(); @endphp
            <a class="nav-link position-relative d-flex align-items-center" href="#"
               data-bs-toggle="dropdown" aria-expanded="false"
               title="View notifications">
              <i class="bi bi-bell fs-5"></i>
              @if($unread > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger animate__animated animate__pulse">
                  {{ $unread > 99 ? '99+' : $unread }}
                </span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" style="min-width: 350px;">
              <li class="px-3 py-2">
                <h6 class="dropdown-header fw-semibold text-dark mb-0">
                  <i class="bi bi-bell me-2 text-primary"></i>Recent Notifications
                </h6>
              </li>
              @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                <li>
                  <a class="dropdown-item py-2 {{ $notification->read_at ? '' : 'fw-semibold' }}"
                     href="{{ route('notifications.index') }}">
                    <div class="d-flex align-items-start">
                      <div class="flex-shrink-0 me-3">
                        @if($notification->read_at)
                          <i class="bi bi-circle text-muted"></i>
                        @else
                          <i class="bi bi-circle-fill text-primary"></i>
                        @endif
                      </div>
                      <div class="flex-grow-1">
                        <div class="small text-dark">{{ Str::limit($notification->data['message'] ?? 'Notification', 60) }}</div>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                      </div>
                    </div>
                  </a>
                </li>
              @empty
                <li class="px-3 py-3 text-center">
                  <i class="bi bi-bell-slash text-muted fs-4 mb-2"></i>
                  <div class="text-muted small">No notifications yet</div>
                </li>
              @endforelse
              <li><hr class="dropdown-divider"></li>
              <li class="px-3 pb-2">
                <a class="btn btn-outline-primary btn-sm w-100 rounded-pill" href="{{ route('notifications.index') }}">
                  <i class="bi bi-eye me-2"></i>View All Notifications
                </a>
              </li>
            </ul>
          </li>

          {{-- User Menu --}}
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
               data-bs-toggle="dropdown" aria-expanded="false"
               title="User menu">
              <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                   style="width: 36px; height: 36px;">
                <i class="bi bi-person-fill text-primary"></i>
              </div>
              <span class="fw-medium">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" style="min-width: 200px;">
              <li class="px-3 py-2">
                <h6 class="dropdown-header fw-semibold text-dark mb-0">
                  <i class="bi bi-person-circle me-2 text-primary"></i>Account
                </h6>
              </li>
              <li>
                <a class="dropdown-item py-2" href="{{ route('profile.show') }}" title="View your profile">
                  <i class="bi bi-person me-2 text-muted"></i>My Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}" title="Edit your profile">
                  <i class="bi bi-pencil me-2 text-muted"></i>Edit Profile
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li class="px-3 pb-2">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill"
                          title="Sign out of your account">
                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>
