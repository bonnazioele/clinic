@extends('layouts.app')
@section('title','Doctor Queue')
@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-primary mb-1 d-flex align-items-center"><i class="bi bi-list-ol medical-icon me-2"></i>Queue</h2>
        <p class="text-muted mb-0">Patients currently waiting</p>
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
            <tr>
              <td class="px-4 py-3"><span class="badge bg-warning text-dark">#{{ $entry->queue_number }}</span></td>
              <td class="px-4 py-3">{{ $entry->appointment?->user?->name ?? 'Patient' }}</td>
              <td class="px-4 py-3">{{ $entry->appointment?->appointment_time ? time12($entry->appointment->appointment_time) : '-' }}</td>
              <td class="px-4 py-3"><span class="badge bg-info text-dark"><i class="bi bi-clock me-1"></i>Waiting</span></td>
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
                <form method="POST" action="{{ route('doctor.queue.serve', $entry) }}" onsubmit="return confirm('Mark as served?')">
                  @csrf
                  <button class="btn btn-sm btn-success"><i class="bi bi-check2-circle me-1"></i>Done</button>
                </form>
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
