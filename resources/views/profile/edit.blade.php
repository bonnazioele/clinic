{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Profile')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">Edit Your Profile</h4>
    </div>
    <div class="card-body">
      <form method="POST"
            action="{{ route('profile.update') }}"
            enctype="multipart/form-data">
        @csrf

        {{-- Name --}}
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input
            id="name"
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}"
            required
          >
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Phone --}}
        <div class="mb-3">
          <label for="phone" class="form-label">Phone</label>
          <input
            id="phone"
            type="text"
            name="phone"
            class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $user->phone) }}"
          >
          @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Address --}}
        <div class="mb-3">
          <label for="address" class="form-label">Address</label>
          <textarea
            id="address"
            name="address"
            rows="3"
            class="form-control @error('address') is-invalid @enderror"
          >{{ old('address', $user->address) }}</textarea>
          @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Medical Document --}}
        <div class="mb-3">
          <label for="medical_document" class="form-label">
            Medical Document (PDF, DOC)
          </label>
          @if($user->medical_document)
            <p class="small">
              Current file:
              <a href="{{ Storage::url($user->medical_document) }}" target="_blank">
                Download
              </a>
            </p>
          @endif
          <input
            id="medical_document"
            type="file"
            name="medical_document"
            class="form-control @error('medical_document') is-invalid @enderror"
          >
          @error('medical_document')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn btn-primary">
          Save Changes
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
