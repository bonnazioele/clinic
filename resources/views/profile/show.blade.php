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

    <div class="accordion mb-3" id="medicalHistoryAccordion">
  @foreach($user->patientHistories->sortByDesc('date_of_visit') as $history)
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading{{ $history->id }}">
        <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $history->id }}">
          <div class="d-flex justify-content-between w-100 me-3">
            <strong>{{ $history->clinic_name }}</strong>
            <span class="text-muted">
              {{ $history->date_of_visit?->format('M d, Y') }}
            </span>
          </div>
        </button>
      </h2>

      <div id="collapse{{ $history->id }}"
           class="accordion-collapse collapse"
           data-bs-parent="#medicalHistoryAccordion">

        <div class="accordion-body">
          <p><strong>Doctor:</strong> {{ $history->doctor_name ?? '—' }}</p>
          <p><strong>Diagnosis:</strong> {{ $history->diagnosis }}</p>
          <p><strong>Treatment:</strong> {{ $history->treatment ?? '—' }}</p>

          @if($history->document_path)
            <a href="{{ Storage::url($history->document_path) }}"
               target="_blank"
               class="btn btn-sm btn-outline-primary">
              View Attached Document
            </a>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>

@endsection
