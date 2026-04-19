<!-- {{-- resources/views/partials/sidebar.blade.php --}}
@php
  use App\Models\Clinic;

  $dashRoute = 'welcome';
  $role      = 'guest';

  if (auth()->check()) {
    $u = auth()->user();

    if ($u->is_admin) {
      $dashRoute = 'admin.dashboard';
      $role      = 'admin';
    } elseif ($u->is_owner) {
      $clinic    = Clinic::where('created_by_user_id', $u->id)
                     ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
                     ->orderByDesc('id')->first();
      $dashRoute = ($clinic && $clinic->status === 'approved') ? 'secretary.dashboard' : 'welcome';
      $role      = 'owner';
    } elseif ($u->is_doctor) {
      $dashRoute = 'doctor.dashboard';
      $role      = 'doctor';
    } elseif ($u->is_secretary) {
      $dashRoute = 'secretary.dashboard';
      $role      = 'secretary';
    } else {
      $dashRoute = 'dashboard';
      $role      = 'patient';
    }

    $unread = $u->unreadNotifications->count();
  }
@endphp

<style>
    /* ============================================================
   CliniQ Sidebar  –  paste into your main stylesheet
   or @import into app.scss / app.css
   ============================================================ */

/* ── Layout shell ── */
body.with-sidebar {
  display: flex;
  min-height: 100vh;
  background: #f4f6f9;
}

#cliniq-sidebar {
  width: 220px;
  min-width: 220px;
  background: #0f1b35;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  overflow-x: hidden;
  z-index: 1040;
  transition: transform 0.25s ease;
}

.sidebar-main-content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

/* ── Brand ── */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 16px 18px;
  text-decoration: none;
  border-bottom: 1px solid rgba(255,255,255,.07);
  flex-shrink: 0;
}

.brand-icon {
  width: 32px;
  height: 32px;
  background: #2563eb;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1rem;
  flex-shrink: 0;
}

.brand-name {
  font-size: 1rem;
  font-weight: 600;
  color: #fff;
  letter-spacing: .01em;
}

/* ── Nav ── */
.sidebar-nav {
  flex: 1;
  padding: 12px 10px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 6px;
}

.nav-label {
  font-size: .68rem;
  font-weight: 600;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: rgba(255,255,255,.3);
  padding: 10px 8px 4px;
  margin: 0;
}

.sidebar-nav .nav-link {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 8px;
  color: rgba(255,255,255,.55);
  font-size: .82rem;
  font-weight: 400;
  text-decoration: none;
  transition: background .15s, color .15s;
  white-space: nowrap;
}

.sidebar-nav .nav-link i {
  font-size: .95rem;
  flex-shrink: 0;
  width: 16px;
  text-align: center;
}

.sidebar-nav .nav-link:hover {
  background: rgba(255,255,255,.08);
  color: rgba(255,255,255,.9);
}

.sidebar-nav .nav-link.active {
  background: rgba(37,99,235,.25);
  color: #93c5fd;
}

.nav-badge {
  margin-left: auto;
  background: #2563eb;
  color: #fff;
  font-size: .65rem;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 99px;
  flex-shrink: 0;
}

/* ── Footer ── */
.sidebar-footer {
  padding: 12px 10px;
  border-top: 1px solid rgba(255,255,255,.07);
  flex-shrink: 0;
}

.footer-btn {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 8px;
  background: rgba(255,255,255,.07);
  color: rgba(255,255,255,.7);
  font-size: .82rem;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: background .15s, color .15s;
}

.footer-btn:hover {
  background: rgba(255,255,255,.12);
  color: #fff;
}

.footer-btn i { font-size: .95rem; flex-shrink: 0; }

/* User card */
.user-card {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 8px;
  background: rgba(255,255,255,.07);
  border: none;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: background .15s;
  color: inherit;
}

.user-card::after { display: none; }   /* remove BS caret */

.user-card:hover { background: rgba(255,255,255,.12); }

.user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #2563eb;
  color: #fff;
  font-size: .72rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-info { overflow: hidden; }

.user-name {
  font-size: .8rem;
  font-weight: 500;
  color: rgba(255,255,255,.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: .7rem;
  color: rgba(255,255,255,.4);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Notification dropdown width */
.notif-dropdown { min-width: 320px; }

/* ── Top bar (mobile only) ── */
#cliniq-topbar {
  position: sticky;
  top: 0;
  height: 50px;
  background: #0f1b35;
  display: flex;
  align-items: center;
  padding: 0 14px;
  gap: 10px;
  z-index: 1050;
}

.topbar-toggle {
  background: none;
  border: none;
  color: rgba(255,255,255,.8);
  font-size: 1.3rem;
  padding: 0;
  cursor: pointer;
}

.topbar-brand {
  flex: 1;
  color: #fff;
  font-weight: 600;
  font-size: .95rem;
  text-decoration: none;
}

.topbar-icon {
  color: rgba(255,255,255,.8);
  font-size: 1.1rem;
  text-decoration: none;
}

/* ── Mobile sidebar slide-in ── */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  z-index: 1039;
  opacity: 0;
  pointer-events: none;
  transition: opacity .25s;
}

.sidebar-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

@media (max-width: 767.98px) {
  body.with-sidebar {
    display: block;
  }

  #cliniq-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    transform: translateX(-100%);
    z-index: 1045;
  }

  #cliniq-sidebar.sidebar-open {
    transform: translateX(0);
  }

  .sidebar-main-content {
    width: 100%;
  }
}

/* ── Page content area ── */
.page-content {
  flex: 1;
  padding: 24px;
  min-width: 0;
}

/* ── Stat cards ── */
.stat-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.stat-card {
  background: #fff;
  border: 1px solid #e8ecf0;
  border-radius: 12px;
  padding: 16px;
  cursor: default;
}

.stat-card-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  font-size: .9rem;
}

.stat-card-n {
  font-size: 1.5rem;
  font-weight: 600;
  color: #0f1b35;
  line-height: 1;
}

.stat-card-label {
  font-size: .75rem;
  color: #6b7280;
  margin-top: 4px;
}

.stat-card-sub {
  font-size: .7rem;
  margin-top: 6px;
}

/* Dashboard card */
.cliniq-card {
  background: #fff;
  border: 1px solid #e8ecf0;
  border-radius: 12px;
  padding: 18px 20px;
  margin-bottom: 16px;
}

.cliniq-card-title {
  font-size: .875rem;
  font-weight: 600;
  color: #0f1b35;
  display: flex;
  align-items: center;
  gap: 7px;
}

.cliniq-card-title i { color: #2563eb; }

/* Timeline appointments */
.appt-timeline { display: flex; flex-direction: column; }

.appt-tl-item {
  display: flex;
  gap: 12px;
  padding-bottom: 14px;
  position: relative;
}

.appt-tl-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 15px;
  top: 32px;
  bottom: 0;
  width: 1px;
  background: #e8ecf0;
}

.appt-tl-dot {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: .75rem;
  z-index: 1;
}

.appt-tl-dot.soon     { background: #fef3c7; border: 1.5px solid #f59e0b; color: #92400e; }
.appt-tl-dot.upcoming { background: #dbeafe; border: 1.5px solid #3b82f6; color: #1e40af; }

.appt-tl-clinic { font-size: .85rem; font-weight: 600; color: #0f1b35; }
.appt-tl-meta   { font-size: .75rem; color: #6b7280; margin-top: 2px; }

.appt-pill {
  display: inline-block;
  font-size: .67rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 99px;
  margin-top: 4px;
}

.appt-pill.soon     { background: #fef3c7; color: #92400e; }
.appt-pill.upcoming { background: #dbeafe; color: #1e40af; }

/* Action buttons */
.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
}

.action-tile {
  background: #fff;
  border: 1px solid #e8ecf0;
  border-radius: 10px;
  padding: 14px 12px;
  text-align: left;
  text-decoration: none;
  display: block;
  transition: background .15s, border-color .15s;
  color: inherit;
}

.action-tile:hover {
  background: #f0f4ff;
  border-color: #bfdbfe;
  color: inherit;
  text-decoration: none;
}

.action-tile-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
  font-size: .9rem;
}

.action-tile-label {
  font-size: .8rem;
  font-weight: 600;
  color: #0f1b35;
}

.action-tile-sub {
  font-size: .7rem;
  color: #6b7280;
  margin-top: 2px;
}

/* Activity feed */
.activity-item {
  display: flex;
  gap: 9px;
  align-items: flex-start;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child { border-bottom: none; }

.activity-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
}

.activity-text  { font-size: .8rem; color: #374151; line-height: 1.45; }
.activity-time  { font-size: .72rem; color: #9ca3af; margin-top: 1px; }

/* Recent visits table */
.visits-table { font-size: .8rem; }
.visits-table th {
  color: #6b7280;
  font-weight: 500;
  border-top: none;
  padding-bottom: 8px;
}
</style>

<aside id="cliniq-sidebar">

  {{-- Brand --}}
  <a href="{{ auth()->check() ? route($dashRoute) : route('welcome') }}" class="sidebar-brand">
    <span class="brand-icon">
      <i class="bi bi-heart-pulse-fill"></i>
    </span>
    <span class="brand-name">CliniQ</span>
  </a>

  {{-- Nav --}}
  <nav class="sidebar-nav">

    @guest
      <div class="nav-section">
        <a href="{{ route('clinics.index') }}"
           class="nav-link @if(request()->routeIs('clinics.*')) active @endif">
          <i class="bi bi-building"></i><span>Find clinics</span>
        </a>
      </div>
    @else

      {{-- ── PATIENT ── --}}
      @if($role === 'patient')
        <div class="nav-section">
          <a href="{{ route('dashboard') }}"
             class="nav-link @if(request()->routeIs('dashboard')) active @endif">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
          </a>
          <a href="{{ route('clinics.index') }}"
             class="nav-link @if(request()->routeIs('clinics.*')) active @endif">
            <i class="bi bi-building"></i><span>Find clinics</span>
          </a>
          <a href="{{ route('appointments.index') }}"
             class="nav-link @if(request()->routeIs('appointments.*')) active @endif">
            <i class="bi bi-calendar-check"></i>
            <span>My appointments</span>
            @if(isset($upcoming) && $upcoming->count())
              <span class="nav-badge">{{ $upcoming->count() }}</span>
            @endif
          </a>
          <a href="{{ route('queue.status') }}"
             class="nav-link @if(request()->routeIs('queue.status')) active @endif">
            <i class="bi bi-clock"></i><span>Queue status</span>
          </a>
        </div>

      {{-- ── ADMIN ── --}}
      @elseif($role === 'admin')
        <div class="nav-section">
          <a href="{{ route('admin.dashboard') }}"
             class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
          </a>
        </div>
        <div class="nav-section">
          <p class="nav-label">Manage</p>
          <a href="{{ route('admin.clinics.index') }}"
             class="nav-link @if(request()->routeIs('admin.clinics.*')) active @endif">
            <i class="bi bi-building-gear"></i><span>Clinics</span>
          </a>
          <a href="{{ route('admin.services.index') }}"
             class="nav-link @if(request()->routeIs('admin.services.*')) active @endif">
            <i class="bi bi-gear-wide-connected"></i><span>Services</span>
          </a>
          <a href="{{ route('admin.users.index') }}"
             class="nav-link @if(request()->routeIs('admin.users.*')) active @endif">
            <i class="bi bi-people"></i><span>Users</span>
          </a>
        </div>

      {{-- ── SECRETARY / OWNER ── --}}
      @elseif($role === 'secretary' || $role === 'owner')
        <div class="nav-section">
          <a href="{{ route('secretary.dashboard') }}"
             class="nav-link @if(request()->routeIs('secretary.dashboard')) active @endif">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
          </a>
        </div>
        <div class="nav-section">
          <p class="nav-label">Clinic</p>
          <a href="{{ route('secretary.doctors.index') }}"
             class="nav-link @if(request()->routeIs('secretary.doctors.*')) active @endif">
            <i class="bi bi-person-badge"></i><span>Doctors</span>
          </a>
          <a href="{{ route('secretary.services.index') }}"
             class="nav-link @if(request()->routeIs('secretary.services.*')) active @endif">
            <i class="bi bi-gear-wide-connected"></i><span>Services</span>
          </a>
          <a href="{{ route('secretary.queue.overview') }}"
             class="nav-link @if(request()->routeIs('secretary.queue.*')) active @endif">
            <i class="bi bi-people"></i><span>Queue</span>
          </a>
          <a href="{{ route('secretary.patients.index') }}"
             class="nav-link @if(request()->routeIs('secretary.patients.*')) active @endif">
            <i class="bi bi-person-lines-fill"></i><span>Patients</span>
          </a>
        </div>

      {{-- ── DOCTOR ── --}}
      @elseif($role === 'doctor')
        <div class="nav-section">
          <a href="{{ route('doctor.dashboard') }}"
             class="nav-link @if(request()->routeIs('doctor.dashboard')) active @endif">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
          </a>
        </div>
        <div class="nav-section">
          <p class="nav-label">Work</p>
          <a href="{{ route('doctor.queue.index') }}"
             class="nav-link @if(request()->routeIs('doctor.queue.*')) active @endif">
            <i class="bi bi-list-ol"></i><span>Queue</span>
          </a>
          <a href="{{ route('doctor.schedules.index') }}"
             class="nav-link @if(request()->routeIs('doctor.schedules.*')) active @endif">
            <i class="bi bi-calendar-range"></i><span>Schedule</span>
          </a>
        </div>
      @endif

      {{-- ── SHARED: Account section ── --}}
      <div class="nav-section">
        <p class="nav-label">Account</p>
        <a href="{{ route('profile.show') }}"
           class="nav-link @if(request()->routeIs('profile.show')) active @endif">
          <i class="bi bi-person-circle"></i><span>Profile</span>
        </a>
        @if($role === 'secretary')
          @php $firstClinic = auth()->user()->secretaryClinics()->select('clinics.id')->first(); @endphp
          @if($firstClinic)
            <a href="{{ route('secretary.clinic.edit', $firstClinic->id) }}"
               class="nav-link @if(request()->routeIs('secretary.clinic.edit')) active @endif">
              <i class="bi bi-gear"></i><span>Clinic settings</span>
            </a>
          @endif
        @endif
      </div>

    @endguest

  </nav>

  {{-- Bottom: user + notifications --}}
  @auth
    <div class="sidebar-footer">

      {{-- Notifications pill --}}
      <div class="dropdown mb-2">
        <button class="footer-btn w-100 dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-bell"></i>
          <span>Notifications</span>
          @if($unread)
            <span class="nav-badge ms-auto">{{ $unread }}</span>
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg notif-dropdown"
            aria-label="Notifications">
          <li class="dropdown-header d-flex align-items-center">
            <strong>Notifications</strong>
            @if($unread)
              <span class="badge bg-primary ms-auto">{{ $unread }} new</span>
            @endif
          </li>
          @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $note)
            <li>
              <a href="{{ route('notifications.index') }}"
                 class="dropdown-item py-2 {{ $note->read_at ? '' : 'fw-semibold bg-light' }}">
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-info-circle text-primary mt-1 flex-shrink-0"></i>
                  <div>
                    <div>{{ \Illuminate\Support\Str::limit($note->data['message'], 60) }}</div>
                    <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                  </div>
                </div>
              </a>
            </li>
          @empty
            <li class="dropdown-item text-center text-muted py-3">
              <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>No notifications
            </li>
          @endforelse
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-center text-primary fw-semibold"
               href="{{ route('notifications.index') }}">
              View all notifications
            </a>
          </li>
        </ul>
      </div>

      {{-- User card --}}
      <div class="dropdown">
        <button class="user-card dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
          <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">
              @if($role === 'admin') Administrator
              @elseif($role === 'secretary') Secretary
              @elseif($role === 'owner') Clinic owner
              @elseif($role === 'doctor') Doctor
              @else Patient
              @endif
            </div>
          </div>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-label="User menu">
          <li>
            <a class="dropdown-item" href="{{ route('profile.show') }}">
              <i class="bi bi-person-circle me-2"></i>My profile
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
              <i class="bi bi-pencil-square me-2"></i>Edit profile
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="#"
               onclick="event.preventDefault(); document.getElementById('sidebar-logout').submit();">
              <i class="bi bi-box-arrow-right me-2"></i>Log out
            </a>
          </li>
        </ul>
        <form id="sidebar-logout" action="{{ route('logout') }}" method="POST" class="d-none">
          @csrf
        </form>
      </div>

    </div>
  @endauth

  @guest
    <div class="sidebar-footer">
      <a href="{{ route('login') }}" class="footer-btn w-100 mb-2">
        <i class="bi bi-box-arrow-in-right"></i><span>Login</span>
      </a>
      <a href="{{ route('register') }}" class="footer-btn w-100">
        <i class="bi bi-person-plus"></i><span>Register</span>
      </a>
    </div>
  @endguest

</aside>

{{-- Mobile topbar (visible only on small screens) --}}
<header id="cliniq-topbar" class="d-md-none">
  <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle menu">
    <i class="bi bi-list"></i>
  </button>
  <a href="{{ auth()->check() ? route($dashRoute) : route('welcome') }}" class="topbar-brand">
    <i class="bi bi-heart-pulse-fill me-1"></i>CliniQ
  </a>
  @auth
    <div class="topbar-right">
      @if($unread)
        <a href="{{ route('notifications.index') }}" class="topbar-icon position-relative">
          <i class="bi bi-bell-fill"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $unread }}
          </span>
        </a>
      @endif
    </div>
  @endauth
</header>

{{-- Overlay for mobile --}}
<div id="sidebarOverlay" class="sidebar-overlay d-md-none"></div>

<script>
    // sidebar-toggle.js
// Include this script at the bottom of your layout (before </body>)

document.addEventListener('DOMContentLoaded', function () {
  const sidebar  = document.getElementById('cliniq-sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const toggle   = document.getElementById('sidebarToggle');

  if (!sidebar || !toggle) return;

  function openSidebar() {
    sidebar.classList.add('sidebar-open');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('sidebar-open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', function () {
    sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
  });

  overlay.addEventListener('click', closeSidebar);

  // Close on nav link tap (mobile)
  sidebar.querySelectorAll('.nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.innerWidth < 768) closeSidebar();
    });
  });
});
</script> -->
