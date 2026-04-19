@extends('layouts.app')
@section('title','Doctor Queue')
@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-primary mb-1 d-flex align-items-center"><i class="bi bi-list-ol medical-icon me-2"></i>Queue</h2>
        <p class="text-muted mb-0">Patients currently waiting or now serving</p>
      </div>
      <span class="badge bg-primary"><i class="bi bi-people me-1"></i>{{ $waiting->count() }} waiting</span>
    </div>
  </div>

  <div class="medical-card p-0">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Patient</th>
            <th class="px-4 py-3">Appointment Time</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Document</th>
            <th class="px-4 py-3" width="140">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($waiting as $entry)
            <tr @class(['table-warning'=> $entry->status==='now_serving'])>
              <td class="px-4 py-3"><span class="badge bg-warning text-dark">#{{ $entry->queue_number }}</span></td>
              <td class="px-4 py-3">{{ $entry->appointment?->user?->name ?? 'Patient' }}</td>
              <td class="px-4 py-3">{{ $entry->appointment?->appointment_time ? time12($entry->appointment->appointment_time) : '-' }}</td>
              <td class="px-4 py-3">
                <span class="badge bg-{{ $entry->status_badge_class }} {{ in_array($entry->status_badge_class,['warning','info']) ? 'text-dark' : '' }}">
                  <i class="bi {{ $entry->status==='now_serving' ? 'bi-megaphone' : 'bi-clock' }} me-1"></i>
                  {{ $entry->status_label }}
                </span>
              </td>
              <td class="px-4 py-3">
                @if($entry->appointment?->medical_document)
                  <a href="{{ asset('storage/' . $entry->appointment->medical_document) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-medical me-1"></i>View
                  </a>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="px-4 py-3">
                <button type="button"
                        class="btn btn-sm btn-success complete-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#completeModal"
                        data-action-url="{{ route('doctor.queue.serve', $entry) }}"
                        data-queue="#{{ $entry->queue_number }}"
                        data-patient="{{ $entry->appointment?->user?->name ?? 'Walk-in Patient' }}">
                  <i class="bi bi-check2-circle me-1"></i>Complete
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">No waiting patients.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <form method="POST" class="modal-content" id="completeForm">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-check2-circle me-2"></i>Complete Consultation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-1">Mark <strong id="completePatientName"></strong> as done?</p>
        <p class="text-muted small mb-0" id="completeQueueNumber"></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Yes, Complete</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('completeForm');
  document.querySelectorAll('.complete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      form.setAttribute('action', btn.getAttribute('data-action-url'));
      document.getElementById('completePatientName').textContent = btn.getAttribute('data-patient');
      document.getElementById('completeQueueNumber').textContent = btn.getAttribute('data-queue');
    });
  });
});
</script>
@endpush