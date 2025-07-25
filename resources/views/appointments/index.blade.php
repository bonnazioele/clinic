@extends('layouts.app')

@section('title','My Appointments')

@section('content')
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white">
      <h4 class="mb-0">My Appointments</h4>
    </div>
    <div class="card-body">
      @if($appts->isEmpty())
        <p class="text-center text-muted">
          No appointments yet. <a href="{{ route('appointments.create') }}">Book one now</a>.
        </p>
      @else
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Clinic</th>
                <th>Service</th>
                <th>Status</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($appts as $a)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('LL') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:mm A') }}</td>
                  <td>{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->name }}</td>
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
    </div>
  </div>
@endsection
