@extends('layouts.app')
@section('title','My Profile')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  {{-- PROFILE CARD --}}
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
<!-- 
  {{-- MEDICAL HISTORY --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-journal-medical me-2"></i>Medical History
      </h4>
      <a href="{{ route('profile.history') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-right me-1"></i>View All
      </a>
    </div>
    <div class="card-body">
      @forelse($user->patientHistories()->latest('date_of_visit')->take(3)->get() as $history)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between align-items-start">
            <strong>{{ $history->clinic_name }}</strong>
            <span class="text-muted small">
              {{ $history->date_of_visit?->format('M d, Y') ?? '—' }}
            </span>
          </div>
          <p class="mb-1 mt-1"><strong>Doctor:</strong> {{ $history->doctor_name ?? '—' }}</p>
          <p class="mb-1"><strong>Diagnosis:</strong> {{ $history->diagnosis }}</p>
          <p class="mb-1"><strong>Treatment:</strong> {{ $history->treatment ?? '—' }}</p>
          @if($history->document_path)
            <a href="{{ Storage::url($history->document_path) }}" target="_blank" class="small">
              <i class="bi bi-file-earmark me-1"></i>View Attached Document
            </a>
          @endif
        </div>
      @empty
        <p class="text-muted mb-0">No medical history records yet. Records are added automatically after completed visits.</p>
      @endforelse
    </div>
  </div>

  {{-- DOWNLOAD MONTHLY REPORTS --}}
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">
        <i class="bi bi-download me-2"></i>Download Monthly Reports
      </h4>
    </div>
    <div class="card-body">
      @forelse($reportMonths as $m)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span>
            <i class="bi bi-calendar-month me-2 text-primary"></i>
            {{ \Carbon\Carbon::parse($m)->format('F Y') }}
          </span>
          <a href="{{ route('profile.report.download', ['month' => $m]) }}"
             class="btn btn-sm btn-outline-primary">
            <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
          </a>
        </div>
      @empty
        <p class="text-muted mb-0 small">No completed visits to export yet.</p>
      @endforelse
    </div>
  </div>
</div> -->
@endsection