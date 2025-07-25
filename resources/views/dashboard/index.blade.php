@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="container py-4">
  <h1 class="mb-4">Welcome, {{ Auth::user()->name }}!</h1>

  <div class="row g-3">
    {{-- Upcoming Appointments --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header">Upcoming Appointments</div>
        <div class="card-body">
          @if($upcoming->isEmpty())
            <p class="text-center text-muted">
              No upcoming appointments. 
              <a href="{{ route('appointments.create') }}">Book one now</a>.
            </p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($upcoming as $a)
                <li class="list-group-item">
                  <strong>{{ Carbon::parse($a->appointment_date)->format('M j, Y') }}</strong>
                  at {{ Carbon::parse($a->appointment_time)->format('g:i A') }}<br>
                  {{ $a->clinic->name }} — {{ $a->service->name }}
                </li>
              @endforeach
            </ul>
            <a href="{{ route('appointments.index') }}" class="btn btn-link mt-2">
              View all appointments
            </a>
          @endif
        </div>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header">Quick Actions</div>
        <div class="card-body d-flex flex-column">
          <a href="{{ route('appointments.create') }}" 
             class="btn btn-primary mb-2 w-100">Book Appointment</a>
          <a href="{{ route('clinics.index') }}" 
             class="btn btn-outline-primary mb-2 w-100">Find a Clinic</a>
          <a href="{{ route('profile.edit') }}" 
             class="btn btn-outline-secondary mb-2 w-100">Edit Profile</a>
        </div>
      </div>
    </div>

    {{-- Past Appointments --}}
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header">Recent Visits</div>
        <div class="card-body">
          @if($past->isEmpty())
            <p class="text-center text-muted">
              No past visits recorded.
            </p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($past as $a)
                <li class="list-group-item">
                  {{ Carbon::parse($a->appointment_date)->format('M j, Y') }} —
                  {{ $a->clinic->name }}
                </li>
              @endforeach
            </ul>
            <a href="{{ route('appointments.index') }}" class="btn btn-link mt-2">
              View appointment history
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
