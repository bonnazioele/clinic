@extends('layouts.app')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-building medical-icon me-2"></i>Edit Clinic Profile
        </h2>
        <p class="text-muted mb-0">{{ $clinic->name }}</p>
      </div>
      <a href="{{ route('secretary.appointments.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('secretary.clinic.update', $clinic) }}" enctype="multipart/form-data" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Clinic Name</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $clinic->name) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Address</label>
          <input type="text" name="address" class="form-control" value="{{ old('address', $clinic->address) }}" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" rows="4" class="form-control" placeholder="Describe your clinic...">{{ old('description', $clinic->description) }}</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Queue Strategy <span class="text-danger">*</span></label>
          <select name="queue_mode" class="form-select" required>
            <option value="fcfs" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'fcfs' ? 'selected' : '' }}>First-Come, First-Served (FCFS)</option>
            <option value="priority" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'priority' ? 'selected' : '' }}>Priority-Aware Queue</option>
          </select>
          <div class="small mt-2">
            <strong>1. First-Come, First-Served (FCFS):</strong> Patients are served strictly in order of arrival, regardless of any priority flags. This ignores priority patients.<br>
            <strong>2. Priority-Aware Queue (PAQ):</strong><br>
            The system adjusts the order dynamically, allowing patients with designated priority flags (senior, PWD, pregnant, emergency) to <em>override the queue</em> and be served earlier.
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Logo</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
          @if($clinic->logo)
            <div class="mt-2">
              <img src="{{ asset('storage/'.$clinic->logo) }}" alt="Logo" style="height:60px;" class="rounded">
            </div>
          @endif
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Cover Image</label>
          <input type="file" name="cover_image" class="form-control" accept="image/*">
          @if($clinic->cover_image)
            <div class="mt-2">
              <img src="{{ asset('storage/'.$clinic->cover_image) }}" alt="Cover" style="height:60px;" class="rounded">
            </div>
          @endif
        </div>
      </div>
    </div>
    <div class="card-footer bg-light d-flex justify-content-between">
      <div></div>
      <button class="btn btn-primary">
        <i class="bi bi-save me-1"></i>Save Changes
      </button>
    </div>
  </form>
</div>
@endsection
