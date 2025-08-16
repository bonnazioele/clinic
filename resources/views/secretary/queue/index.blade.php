@extends('layouts.app')
@section('title',"Queue — {$clinic->name}")

@section('content')
<div class="container py-4">
  <!-- Header -->
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-clock-history medical-icon me-2"></i>Queue Management
        </h2>
        <p class="text-muted mb-0">{{ $clinic->name }} — {{ $clinic->address }}</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-primary">
          <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button class="btn btn-outline-secondary" onclick="refreshQueue()">
          <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat">{{ $waiting->count() }}</div>
          <small class="text-muted">Currently Waiting</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat text-success">{{ \App\Models\QueueEntry::where('clinic_id',$clinic->id)->where('status','served')->whereDate('served_at', now())->count() }}</div>
          <small class="text-muted">Served Today</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat text-info">{{ \App\Models\QueueEntry::where('clinic_id',$clinic->id)->where('status','waiting')->whereDate('created_at', now())->count() }}</div>
          <small class="text-muted">Joined Today</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat text-warning">{{ \App\Models\Appointment::where('clinic_id',$clinic->id)->whereDate('appointment_date', now())->count() }}</div>
          <small class="text-muted">Today's Appointments</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Queue Table -->
  <div class="medical-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="mb-0"><i class="bi bi-people medical-icon me-2"></i>Active Queue</h5>
      <span class="badge bg-primary">{{ $waiting->count() }} waiting</span>
    </div>

    @if($waiting->isEmpty())
      <div class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
        <h6 class="text-success mt-3 mb-2">Queue is Empty</h6>
        <p class="text-muted mb-0">No patients are currently waiting.</p>
      </div>
    @else
    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-light">
          <tr>
            <th class="border-0 px-4 py-3">Queue #</th>
            <th class="border-0 px-4 py-3">Patient</th>
            <th class="border-0 px-4 py-3">Wait Time</th>
            <th class="border-0 px-4 py-3">Appointment</th>
            <th class="border-0 px-4 py-3" width="200">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($waiting as $queueEntry)
            <tr>
              <td class="px-4 py-4">
                <div class="d-flex align-items-center">
                  <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                    <span class="text-white fw-bold">{{ $loop->iteration }}</span>
                  </div>
                  <div>
                    <h6 class="fw-semibold mb-0">#{{ $queueEntry->queue_number }}</h6>
                    <small class="text-muted">Position {{ $loop->iteration }}</small>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="fw-semibold">{{ $queueEntry->user->name }}</div>
                <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i>{{ $queueEntry->user->email }}</small>
                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $queueEntry->user->phone }}</small>
              </td>
              <td class="px-4 py-4">
                <div class="small">
                  <div class="mb-1"><i class="bi bi-clock me-1"></i>{{ $queueEntry->created_at->diffForHumans() }}</div>
                  <small class="text-muted"><i class="bi bi-clock me-1"></i>Joined at {{ $queueEntry->formatted_created_time }}</small>
                </div>
              </td>
              <td class="px-4 py-4">
                @if($queueEntry->appointment)
                  <div class="small">
                    <div class="mb-1"><i class="bi bi-calendar-check me-1"></i>{{ $queueEntry->appointment->appointment_date->format('M j, Y') }}</div>
                    <div><i class="bi bi-clock me-1"></i>{{ $queueEntry->appointment->appointment_time?->format('g:i A') }}</div>
                  </div>
                @else
                  <small class="text-muted">Walk-in</small>
                @endif
              </td>
              <td class="px-4 py-4">
                <div class="d-flex gap-2">
                  <form method="POST" action="{{ route('secretary.queue.serve', [$clinic, $queueEntry]) }}" onsubmit="return confirm('Mark as served?')">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Serve</button>
                  </form>
                  <form method="POST" action="{{ route('secretary.queue.cancel', [$clinic, $queueEntry]) }}" onsubmit="return confirm('Cancel this entry?')">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
  function refreshQueue(){ window.location.reload(); }
  setInterval(()=>{ if(!document.hidden){ refreshQueue(); } }, 30000);
</script>
@endpush
@endsection
