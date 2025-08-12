@extends('layouts.secretary')

@section('title','Manage Appointments')

@section('content')
<div class="container-fluid page-container">
  @include('partials.alerts')

  <!-- Page Header with aligned content -->
  <div class="page-header">
    <h3 class="card-title">
      <i class="bi bi-calendar-event me-2"></i>
      All Appointments
    </h3>
    
    <div class="toolbar">
      <!-- Search Bar (Left side of toolbar) -->
      <div class="input-icon toolbar-search" style="max-width: 350px;">
        <i class="bi bi-search icon"></i>
        <input type="text" class="form-control" placeholder="Search appointments...">
      </div>
      
      <!-- Add Appointment Button (Far Right) -->
      <a href="{{ route('secretary.appointments.create') }}" class="btn btn-add-functionality">
        <i class="bi bi-calendar-plus me-2"></i>
        Add Appointment
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-clean table-hover">
          <thead>
            <tr>
              <th>#</th><th>Patient</th><th>Clinic</th><th>Service</th>
              <th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($appointments as $a)
            <tr>
              <td>{{ $a->id }}</td>
              <td>{{ $a->user->name }}</td>
              <td>{{ $a->clinic->name }}</td>
              <td>{{ $a->service->name }}</td>
              <td>{{ $a->doctor?->name ?? '—' }}</td>
              <td>{{ $a->appointment_date }}</td>
              <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</td>
              <td><span class="badge bg-primary">{{ ucfirst($a->status) }}</span></td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <a href="{{ route('secretary.appointments.edit',$a) }}" class="btn btn-outline-primary">Manage</a>
                  <form method="POST" action="{{ route('secretary.appointments.destroy',$a) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    {{ $appointments->links() }}
  </div>
</div>
@endsection
