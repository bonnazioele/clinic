@extends('layouts.app')
@section('title','Doctor Queue')
@section('content')
@php($completionOptions = [
  'completed' => 'Completed',
  'follow_up' => 'Needs Follow-up',
  'referred' => 'Referred / Transferred',
  'cancelled' => 'Cancelled at Clinic',
])
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
                        data-patient="{{ $entry->appointment?->user?->name ?? 'Walk-in Patient' }}"
                        data-service="{{ $entry->appointment?->service?->name ?? 'Consultation' }}"
                        data-appointment="{{ optional($entry->appointment?->appointment_date)->format('M j, Y') }} {{ $entry->appointment?->appointment_time ? time12($entry->appointment->appointment_time) : '' }}">
                  <i class="bi bi-check2-circle me-1"></i>Complete
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No waiting patients.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

    @push('modals')
    <div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content" id="completeForm">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-check2-circle me-2"></i>Complete Consultation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-light border" id="completeModalSummary">
              <div class="fw-semibold">Select a patient from the queue to populate this summary.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Patient Outcome</label>
              <select name="patient_disposition" class="form-select @error('patient_disposition') is-invalid @enderror" required>
                <option value="">Select outcome</option>
                @foreach($completionOptions as $value => $label)
                  <option value="{{ $value }}" @selected(old('patient_disposition') === $value)>{{ $label }}</option>
                @endforeach
              </select>
              @error('patient_disposition')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Doctor Notes</label>
              <textarea name="doctor_notes" class="form-control @error('doctor_notes') is-invalid @enderror" rows="3" placeholder="Summarize findings or next steps">{{ old('doctor_notes') }}</textarea>
              @error('doctor_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Prescription / Instructions</label>
              <textarea name="prescription" class="form-control @error('prescription') is-invalid @enderror" rows="3" placeholder="List medications or patient instructions">{{ old('prescription') }}</textarea>
              @error('prescription')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Follow-up Schedule (optional)</label>
              <input type="datetime-local" name="follow_up_at" class="form-control @error('follow_up_at') is-invalid @enderror" value="{{ old('follow_up_at') }}">
              @error('follow_up_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Save &amp; Mark Done</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    @endpush

    @php($completionHasErrors = $errors->has('patient_disposition') || $errors->has('doctor_notes') || $errors->has('prescription') || $errors->has('follow_up_at'))

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      const modalEl = document.getElementById('completeModal');
      const form = document.getElementById('completeForm');
      const summary = document.getElementById('completeModalSummary');
      if (!modalEl || !form) return;
      const modal = new bootstrap.Modal(modalEl);

      document.querySelectorAll('.complete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const action = btn.getAttribute('data-action-url');
          form.setAttribute('action', action);
          const patient = btn.getAttribute('data-patient');
          const queue = btn.getAttribute('data-queue');
          const service = btn.getAttribute('data-service') || 'Consultation';
          const appointment = btn.getAttribute('data-appointment') || '';
          if (summary) {
            summary.innerHTML = '';
            const nameEl = document.createElement('div');
            nameEl.className = 'fw-semibold mb-1';
            nameEl.textContent = patient;
            const metaEl = document.createElement('div');
            metaEl.className = 'small text-muted';
            const metaParts = [queue, service];
            if (appointment) metaParts.push(appointment.trim());
            metaEl.textContent = metaParts.filter(Boolean).join(' • ');
            summary.appendChild(nameEl);
            summary.appendChild(metaEl);
          }
          try {
            localStorage.setItem('lastCompleteActionUrl', action);
          } catch (e) {}
        });
      });

      const hasErrors = {{ $completionHasErrors ? 'true' : 'false' }};
      if (hasErrors) {
        try {
          const stored = localStorage.getItem('lastCompleteActionUrl');
          if (stored) {
            form.setAttribute('action', stored);
          }
        } catch (e) {}
        modal.show();
      }
    });
    </script>
    @endpush
