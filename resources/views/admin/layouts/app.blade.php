{{-- resources/views/admin/layouts/app.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    {{-- Sidebar --}}
    <nav class="col-md-2 d-none d-md-block bg-light sidebar pt-4">
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link @if(request()->routeIs('admin.clinics.*')) active @endif"
             href="{{ route('admin.clinics.index') }}">
            Manage Clinics
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link @if(request()->routeIs('admin.services.*')) active @endif"
             href="{{ route('admin.services.index') }}">
            Manage Services
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link @if(request()->routeIs('admin.secretaries.*')) active @endif"
             href="{{ route('admin.secretaries.index') }}">
            Manage Secretaries
          </a>
        </li>
        <li class="nav-item mt-4">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-link text-danger p-0">Logout</button>
          </form>
        </li>
      </ul>
    </nav>

    {{-- Main Admin Content --}}
    <main class="col-md-10 ms-sm-auto px-4 py-4">
      @yield('content')
    </main>
  </div>
</div>
@endsection
