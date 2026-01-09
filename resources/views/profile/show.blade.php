@extends('layouts.app')

@section('title','My Profile')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <!-- PROFILE CARD -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h4 class="mb-0">My Profile</h4>
    </div>
    <div class="card-body">
      <p><strong>Name:</strong> {{ $user->name }}</p>
      <p><strong>Email:</strong> {{ $user->email }}</p>
      <p><strong>Phone:</strong> {{ $user->phone ?? '—' }}</p>
      <p><strong>Address:</strong> {{ $user->address ?? '—' }}</p>

      @if($user->medical_document)
        <p>
          <strong>Medical Document:</strong>
          <a href="{{ Storage::url($user->medical_document) }}" target="_blank">
            View / Download
          </a>
        </p>
      @endif

      <a href="{{ route('profile.edit') }}" class="btn btn-primary">
        Edit Profile
      </a>
    </div>
  </div>

  <!-- MEDICAL HISTORY -->
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Medical History</h4>
      <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
        Add Record
      </a>
    </div>

    <div class="card-body">
      @forelse($user->patientHistories->sortByDesc('date_of_visit') as $history)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between">
            <strong>{{ $history->clinic_name }}</strong>
            <span class="text-muted">
              {{ $history->date_of_visit?->format('M d, Y') ?? '—' }}
            </span>
          </div>

          <p class="mb-1"><strong>Doctor:</strong> {{ $history->doctor_name ?? '—' }}</p>
          <p class="mb-1"><strong>Diagnosis:</strong> {{ $history->diagnosis }}</p>
          <p class="mb-1"><strong>Treatment:</strong> {{ $history->treatment ?? '—' }}</p>

          @if($history->document_path)
            <p class="mb-0">
              <a href="{{ Storage::url($history->document_path) }}" target="_blank">
                View Attached Document
              </a>
            </p>
          @endif
        </div>
      @empty
        <p class="text-muted mb-0">No medical history records available.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection
