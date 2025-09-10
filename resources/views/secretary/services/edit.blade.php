@extends('layouts.app')
@section('title','Edit Service')
@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4">
    <h2 class="fw-bold text-primary mb-3"><i class="bi bi-pencil me-2"></i>Edit Service</h2>
    <form method="POST" action="{{ route('secretary.services.update',$service) }}">
      @csrf @method('PUT')
      <div class="mb-3">
        <label class="form-label fw-semibold">Name</label>
        <input type="text" name="name" value="{{ old('name',$service->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description',$service->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Clinics</label>
        <select name="clinic_ids[]" class="form-select @error('clinic_ids') is-invalid @enderror" multiple required>
          @foreach($clinics as $c)
            <option value="{{ $c->id }}" @if(old('clinic_ids') ? collect(old('clinic_ids'))->contains($c->id) : $service->clinics->contains($c->id)) selected @endif>{{ $c->name }}</option>
          @endforeach
        </select>
        <div class="form-text">Hold CTRL (Windows) to select multiple.</div>
        @error('clinic_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Save</button>
        <a href="{{ route('secretary.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
