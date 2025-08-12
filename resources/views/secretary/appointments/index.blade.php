@extends('layouts.app')

@section('title','Manage Appointments')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <h3>All Appointments</h3>
  <table class="table table-striped">
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
        <td>{{ ucfirst($a->status) }}</td>
        <td class="text-end">
          <a href="{{ route('secretary.appointments.edit',$a) }}"
             class="btn btn-sm btn-outline-primary">Manage</a>
          <form method="POST"
                action="{{ route('secretary.appointments.destroy',$a) }}"
                class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $appointments->links() }}
</div>
@endsection
