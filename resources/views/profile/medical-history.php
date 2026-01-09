@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Medical History</h3>

    @forelse($user->patientHistories as $history)
        <div class="card mb-2">
            <div class="card-body">
                <h5>{{ $history->condition }}</h5>
                <small class="text-muted">
                    Diagnosed: {{ $history->diagnosis_date ?? 'N/A' }}
                </small>
                <p class="mt-2">{{ $history->notes }}</p>
            </div>
        </div>
    @empty
        <p class="text-muted">No medical history recorded.</p>
    @endforelse
</div>
@endsection
