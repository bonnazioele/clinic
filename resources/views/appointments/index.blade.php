@extends('layouts.app')

@section('title','My Appointments')

@section('content')
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white">
      <h4 class="mb-0">My Appointments</h4>
    </div>
    <div class="card-body">

      {{-- Upcoming --}}
      <h5 class="mb-3">Upcoming Appointments</h5>
      @if($upcoming->isEmpty())
        <p class="text-center text-muted">
          No upcoming appointments. <a href="{{ route('appointments.create') }}">Book one now</a>.
        </p>
      @else
        <div class="table-responsive mb-4">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Status</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($upcoming as $a)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('LL') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:mm A') }}</td>
                  <td>{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->name }}</td>
                  <td>{{ $a->doctor?->name ?? '—' }}</td>
                  <td>
                    <span class="badge 
                      {{ $a->status=='scheduled' ? 'bg-warning text-dark' : 
                         ($a->status=='completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                  <td class="text-end">
                    @if($a->status=='scheduled')
                      <form method="POST"
                            action="{{ route('appointments.destroy',$a) }}"
                            onsubmit="return confirm('Cancel this appointment?')"
                            class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Cancel</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      {{-- Past --}}
      <h5 class="mt-4 mb-3">Past Appointments</h5>
      @if($past->isEmpty())
        <p class="text-center text-muted">You have no past appointments.</p>
      @else
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($past as $a)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('LL') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:mm A') }}</td>
                  <td>{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->name }}</td>
                  <td>{{ $a->doctor?->name ?? '—' }}</td>
                  <td>
                    <span class="badge 
                      {{ $a->status=='scheduled' ? 'bg-warning text-dark' : 
                         ($a->status=='completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </div>
  </div>
@endsection
