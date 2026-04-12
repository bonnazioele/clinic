@extends('layouts.app')
@section('title','Doctor Queue')

@section('content')

@php
$completionOptions = [
    'completed' => 'Completed',
    'follow_up' => 'Needs Follow-up',
    'referred'  => 'Referred / Transferred',
    'cancelled' => 'Cancelled at Clinic',
];

$failedEntryId = old('queue_entry_id');
@endphp

<style>
    .serve-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
    }

    .serve-modal .modal-header,
    .serve-modal .modal-footer {
        position: sticky;
        z-index: 2;
    }

    .serve-modal .modal-header {
        top: 0;
    }

    .serve-modal .modal-footer {
        bottom: 0;
        background: #fff;
        border-top: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }

    .serve-modal .modal-body {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        padding-bottom: 1.25rem;
    }

    .serve-modal .modal-dialog {
        margin-top: 1.75rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 576px) {
        .serve-modal .modal-body {
            max-height: calc(100vh - 180px);
        }

        .serve-modal .modal-footer {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
        }
    }
</style>

<div class="container py-4">
    <div class="medical-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold text-primary mb-1 d-flex align-items-center">
                    <i class="bi bi-list-ol medical-icon me-2"></i>Queue
                </h2>
                <p class="text-muted mb-0">Patients currently waiting or now serving</p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(isset($clinics) && $clinics->count())
                    <form method="GET" action="{{ route('doctor.queue.index') }}" class="d-flex align-items-center gap-2">
                        <label for="clinic_id" class="fw-semibold mb-0">Clinic</label>
                        <select name="clinic_id" id="clinic_id" class="form-select" onchange="this.form.submit()">
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}" {{ (int)$activeClinicId === (int)$clinic->id ? 'selected' : '' }}>
                                    {{ $clinic->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <span class="badge bg-primary">
                    <i class="bi bi-people me-1"></i>{{ $waiting->count() }} waiting
                </span>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="medical-card p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Clinic</th>
                        <th class="px-4 py-3">Appointment Time</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Document</th>
                        <th class="px-4 py-3" width="200">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($waiting as $entry)
                        <tr class="{{ $entry->status === 'now_serving' ? 'table-warning' : '' }}">
                            <td class="px-4 py-3">
                                <span class="badge {{ $entry->status === 'now_serving' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    #{{ $entry->queue_number }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                {{ $entry->appointment?->user?->name ?? 'Patient' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $entry->clinic->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $entry->appointment?->appointment_time ? time12($entry->appointment->appointment_time) : '-' }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="badge bg-{{ $entry->status_badge_class }} {{ in_array($entry->status_badge_class, ['warning','info']) ? 'text-dark' : '' }}">
                                    <i class="bi {{ $entry->status === 'now_serving' ? 'bi-megaphone' : 'bi-clock' }} me-1"></i>
                                    {{ $entry->status_label }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @if($entry->appointment?->medical_document)
                                    <a href="{{ asset('storage/' . $entry->appointment->medical_document) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-medical me-1"></i>View
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#serveModal_{{ $entry->id }}"
                                >
                                    <i class="bi bi-check2-circle me-1"></i>Done
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No waiting patients for this clinic.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($waiting as $entry)
    <div class="modal fade serve-modal" id="serveModal_{{ $entry->id }}" tabindex="-1" aria-labelledby="serveModalLabel_{{ $entry->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="serveModalLabel_{{ $entry->id }}">
                        Complete Patient Visit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" action="{{ route('doctor.queue.serve', $entry) }}">
                    @csrf
                    <input type="hidden" name="queue_entry_id" value="{{ $entry->id }}">
                    <input type="hidden" name="clinic_id" value="{{ $activeClinicId }}">

                    <div class="modal-body">
                        <div class="alert alert-light border small">
                            <div><strong>Patient:</strong> {{ $entry->appointment?->user?->name ?? 'Patient' }}</div>
                            <div><strong>Clinic:</strong> {{ $entry->clinic->name ?? '-' }}</div>
                            <div><strong>Queue #:</strong> {{ $entry->queue_number }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="disposition_{{ $entry->id }}" class="form-label fw-bold">
                                Patient Disposition *
                            </label>
                            <select
                                class="form-select {{ ($failedEntryId == $entry->id && $errors->has('patient_disposition')) ? 'is-invalid' : '' }}"
                                id="disposition_{{ $entry->id }}"
                                name="patient_disposition"
                                required
                            >
                                <option value="">-- Select Disposition --</option>
                                @foreach($completionOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        {{ ($failedEntryId == $entry->id && old('patient_disposition') === $value) ? 'selected' : '' }}
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @if($failedEntryId == $entry->id && $errors->has('patient_disposition'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('patient_disposition') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="diagnosis_{{ $entry->id }}" class="form-label">Diagnosis</label>
                            <textarea
                                class="form-control {{ ($failedEntryId == $entry->id && $errors->has('diagnosis')) ? 'is-invalid' : '' }}"
                                id="diagnosis_{{ $entry->id }}"
                                name="diagnosis"
                                rows="2"
                                placeholder="e.g., Common cold, mild fever"
                            >{{ $failedEntryId == $entry->id ? old('diagnosis') : '' }}</textarea>
                            @if($failedEntryId == $entry->id && $errors->has('diagnosis'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('diagnosis') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="treatment_{{ $entry->id }}" class="form-label">Treatment / Medication</label>
                            <textarea
                                class="form-control {{ ($failedEntryId == $entry->id && $errors->has('treatment')) ? 'is-invalid' : '' }}"
                                id="treatment_{{ $entry->id }}"
                                name="treatment"
                                rows="2"
                                placeholder="e.g., Rest, paracetamol 500mg twice daily"
                            >{{ $failedEntryId == $entry->id ? old('treatment') : '' }}</textarea>
                            @if($failedEntryId == $entry->id && $errors->has('treatment'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('treatment') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="notes_{{ $entry->id }}" class="form-label">Doctor Notes</label>
                            <textarea
                                class="form-control {{ ($failedEntryId == $entry->id && $errors->has('doctor_notes')) ? 'is-invalid' : '' }}"
                                id="notes_{{ $entry->id }}"
                                name="doctor_notes"
                                rows="2"
                                placeholder="Additional observations or remarks"
                            >{{ $failedEntryId == $entry->id ? old('doctor_notes') : '' }}</textarea>
                            @if($failedEntryId == $entry->id && $errors->has('doctor_notes'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('doctor_notes') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="prescription_{{ $entry->id }}" class="form-label">Prescription</label>
                            <textarea
                                class="form-control {{ ($failedEntryId == $entry->id && $errors->has('prescription')) ? 'is-invalid' : '' }}"
                                id="prescription_{{ $entry->id }}"
                                name="prescription"
                                rows="2"
                                placeholder="Full prescription details"
                            >{{ $failedEntryId == $entry->id ? old('prescription') : '' }}</textarea>
                            @if($failedEntryId == $entry->id && $errors->has('prescription'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('prescription') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="followup_{{ $entry->id }}" class="form-label">Follow-up Date</label>
                            <input
                                type="date"
                                class="form-control {{ ($failedEntryId == $entry->id && $errors->has('follow_up_at')) ? 'is-invalid' : '' }}"
                                id="followup_{{ $entry->id }}"
                                name="follow_up_at"
                                value="{{ $failedEntryId == $entry->id ? old('follow_up_at') : '' }}"
                            >
                            @if($failedEntryId == $entry->id && $errors->has('follow_up_at'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('follow_up_at') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Mark as Served
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@if($errors->any() && $failedEntryId)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('serveModal_{{ $failedEntryId }}');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    </script>
@endif

<script>
    setTimeout(function () {
        const url = new URL(window.location.href);
        window.location.href = url.toString();
    }, 15000);
</script>

@endsection