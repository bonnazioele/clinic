@php
  $route = Route::currentRouteName() ?? '';
@endphp

<aside class="patient-sidebar">
  <a href="{{ route('dashboard') }}" class="sidebar-logo">
    <i class="bi bi-heart-pulse"></i>
  </a>

  <nav class="sidebar-nav">
    <a href="{{ route('dashboard') }}"
       class="sidebar-link {{ $route === 'dashboard' ? 'active' : '' }}">
      <i class="bi bi-house-heart"></i>
    </a>

    <a href="{{ route('appointments.index') }}"
       class="sidebar-link {{ str_contains($route, 'appointments') ? 'active' : '' }}">
      <i class="bi bi-calendar-check"></i>
    </a>

    <a href="{{ route('queue.status') }}"
       class="sidebar-link {{ str_contains($route, 'queue') ? 'active' : '' }}">
      <i class="bi bi-people"></i>
    </a>

    <a href="{{ route('clinics.index') }}"
       class="sidebar-link {{ str_contains($route, 'clinics') ? 'active' : '' }}">
      <i class="bi bi-hospital"></i>
    </a>

  </nav>

  <div class="sidebar-bottom">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="sidebar-link border-0 bg-transparent">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </form>
  </div>
</aside>