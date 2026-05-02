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
      <span class="badge bg-primary">{{ $waiting->count() }} active</span>
    </div>

    @if($waiting->isEmpty())
      <div class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
        <h6 class="mt-3">No Active Queue Entries</h6>
      </div>
    @else
    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-light">
          <tr>
            <th>Queue #</th>
            <th>Patient</th>
            <th>Appointment</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($waiting as $queueEntry)
            <tr>
              <td>#{{ $queueEntry->queue_number }}</td>
              <td>
                <div class="fw-semibold">{{ $queueEntry->display_name }}</div>
                @if($queueEntry->display_email)
                  <div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $queueEntry->display_email }}</div>
                @endif
                @if($queueEntry->display_phone)
                  <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $queueEntry->display_phone }}</div>
                @endif
                @if($queueEntry->is_walk_in)
                  <span class="badge bg-success-subtle text-success mt-1">Walk-In</span>
                @endif
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
                <span class="badge bg-{{ $queueEntry->status_badge_class }}">{{ $queueEntry->status_label }}</span>
              </td>
              <td>
                <div class="d-flex gap-2">

                  <form method="POST" action="{{ route('secretary.queue.call', [$clinic, $queueEntry]) }}">
                    @csrf
                    <button class="btn btn-sm btn-primary">
                      <i class="bi bi-megaphone me-1"></i>Call
                    </button>
                  </form>


                  <form method="POST"
                        action="{{ route('secretary.queue.no_show', [$clinic, $queueEntry]) }}"
                        data-confirm="Mark this patient as NO-SHOW? They will be removed from the queue."
                        data-confirm-title="Mark As No-Show"
                        data-confirm-btn="Mark No-Show">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-person-x me-1"></i>No-Show
                    </button>
                  </form>


                  <button type="button"
                          class="btn btn-sm btn-warning"
                          data-bs-toggle="modal"
                          data-bs-target="#reschedModal"
                          data-action-url="{{ route('secretary.queue.reschedule', [$clinic, $queueEntry]) }}">
                    <i class="bi bi-calendar-event me-1"></i>Resched
                  </button>


                  <form method="POST"
                        action="{{ route('secretary.queue.cancel', [$clinic, $queueEntry]) }}"
                        data-confirm="Cancel this queue entry? The patient will be notified."
                        data-confirm-title="Cancel Queue Entry"
                        data-confirm-btn="Cancel">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
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

document.addEventListener('DOMContentLoaded', function() {
  const reschedModalEl = document.getElementById('reschedModal');
  if (!reschedModalEl) return;
  const reschedModal = new bootstrap.Modal(reschedModalEl);

  document.querySelectorAll('[data-bs-target="#reschedModal"][data-action-url]').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.getAttribute('data-action-url');
      if (url) try { localStorage.setItem('lastReschedActionUrl', url); } catch(e) {}
    });
  });
  reschedModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    if (!button) return;
    const actionUrl = button.getAttribute('data-action-url');
    const form = reschedModalEl.querySelector('form');
    form.setAttribute('action', actionUrl);
  });

  const hasErrors = reschedModalEl.querySelector('.text-danger');
  if (hasErrors) {
    const stored = (() => { try { return localStorage.getItem('lastReschedActionUrl'); } catch(e) { return null; }})();
    const form = reschedModalEl.querySelector('form');
    if (stored && form) form.setAttribute('action', stored);
    reschedModal.show();
  }
});

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

@push('modals')

<div class="modal fade" id="reschedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="#" class="modal-content" id="reschedForm">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Reschedule Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">New Date</label>
          <input type="date" name="new_date" class="form-control" value="{{ old('new_date') }}" required>
          @error('new_date')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label class="form-label">New Time</label>
          <input type="time" name="new_time" class="form-control" value="{{ old('new_time') }}" required>
          @error('new_time')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
@endpush
