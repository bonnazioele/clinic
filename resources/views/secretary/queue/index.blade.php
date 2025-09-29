@extends('layouts.app')
@section('title',"Queue — {$clinic->name}")

@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <h2 class="fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Queue Management</h2>
    <p class="text-muted">{{ $clinic->name }} — {{ $clinic->address }}</p>
  </div>

  <div class="medical-card p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5><i class="bi bi-people me-2"></i>Active Queue</h5>
      <span class="badge bg-primary">{{ $waiting->count() }} waiting</span>
    </div>

    @if($waiting->isEmpty())
      <div class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
        <h6 class="mt-3">Queue is Empty</h6>
      </div>
    @else
    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-light">
          <tr>
            <th>Queue #</th>
            <th>Patient</th>
            <th>Appointment</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($waiting as $queueEntry)
            <tr>
              <td>#{{ $queueEntry->queue_number }}</td>
              <td>
                <strong>{{ $queueEntry->user->name }}</strong><br>
                <small class="text-muted">{{ $queueEntry->user->email }}</small>
              </td>
              <td>
                @if($queueEntry->appointment)
                  {{ $queueEntry->appointment->appointment_date->format('M j, Y') }}
                  at {{ $queueEntry->appointment->appointment_time }}
                @else
                  Walk-in
                @endif
              </td>
              <td>
                <div class="d-flex gap-2">
                  <!-- Call -->
                  <form method="POST" action="{{ route('secretary.queue.call', [$clinic, $queueEntry]) }}">
                    @csrf
                    <button class="btn btn-sm btn-primary">
                      <i class="bi bi-megaphone me-1"></i>Call
                    </button>
                  </form>

                  <!-- Reschedule -->
                  <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#reschedModal{{ $queueEntry->id }}">
                    <i class="bi bi-calendar-event me-1"></i>Resched
                  </button>

                  <!-- Cancel -->
                  <form method="POST" action="{{ route('secretary.queue.cancel', [$clinic, $queueEntry]) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                  </form>
                </div>

                <!-- Reschedule Modal -->
                <div class="modal fade" id="reschedModal{{ $queueEntry->id }}" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('secretary.queue.reschedule', [$clinic, $queueEntry]) }}" class="modal-content">
                      @csrf
                      <div class="modal-header">
                        <h5 class="modal-title">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label>New Date</label>
                          <input type="date" name="new_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label>New Time</label>
                          <input type="time" name="new_time" class="form-control" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
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
  Echo.private('user.notifications.{{ auth()->id() }}')
    .listen('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {
      if(e.notification.role === 'secretary') {
        Swal.fire({
          title: 'Update',
          text: e.notification.message,
          icon: 'info',
          confirmButtonText: 'OK'
        });
      }
    });
</script>
@endpush
@endsection
