@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Medical History</h3>
    </div>

    @forelse($user->patientHistories->sortByDesc('date_of_visit') as $history)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h5 class="mb-1">{{ $history->clinic_name }}</h5>
                        <div class="text-muted small">
                            Visit Date:
                            <strong>{{ optional($history->date_of_visit)->format('M d, Y') ?? $history->date_of_visit }}</strong>

                            <span class="mx-2">•</span>

                            Doctor:
                            <strong>{{ $history->doctor ?? 'Pending' }}</strong>
                        </div>
                    </div>

                    @if(!empty($history->document_path))
                        <div>
                            <a
                                class="btn btn-outline-primary btn-sm"
                                href="{{ asset('storage/' . $history->document_path) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                View Document
                            </a>
                        </div>
                    @endif
                </div>

                <hr class="my-3">

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="fw-semibold">Diagnosis</div>
                        <div class="text-muted">
                            {{ $history->diagnosis ?? 'Pending / Not yet added by doctor' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="fw-semibold">Treatment</div>
                        <div class="text-muted">
                            {{ $history->treatment ?? 'Pending / Not yet added by doctor' }}
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <span class="badge bg-secondary">
                        Recorded: {{ $history->created_at->format('M d, Y g:i A') }}
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border">
            <span class="text-muted">No medical history recorded.</span>
        </div>
    @endforelse
</div>
@endsection