@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0">
          <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-gear me-2"></i>Edit Profile
          </h4>
        </div>
        <div class="card-body p-4">

          @include('partials.alerts')

          <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
              {{-- First Name --}}
              <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                       id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                @error('first_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Last Name --}}
              <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                       id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                @error('last_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control @error('email') is-invalid @enderror"
                     id="email" name="email" value="{{ old('email', $user->email) }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                     id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Address --}}
            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <textarea class="form-control @error('address') is-invalid @enderror"
                        id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Birthdate --}}
            <div class="mb-3">
              <label for="birthdate" class="form-label">Birth Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control @error('birthdate') is-invalid @enderror"
                     id="birthdate" name="birthdate" value="{{ old('birthdate', $user->birthdate) }}" required>
              @error('birthdate')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Profile Photo (Optional) --}}
            {{--
            <div class="mb-3">
              <label for="profile_photo" class="form-label">Profile Photo</label>
              <input type="file" class="form-control @error('profile_photo') is-invalid @enderror"
                     id="profile_photo" name="profile_photo" accept="image/*">
              @error('profile_photo')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            --}}

            {{-- Current Medical Document --}}
            @if($user->medical_document)
              <div class="mb-3">
                <label class="form-label">Current Medical Document</label>
                <div class="d-flex align-items-center">
                  <i class="bi bi-file-earmark-medical text-primary me-2"></i>
                  <span class="me-3">{{ basename($user->medical_document) }}</span>
                  <a href="{{ asset('storage/' . $user->medical_document) }}"
                     class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-eye me-1"></i>View
                  </a>
                </div>
              </div>
            @endif

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary rounded-pill">
                <i class="bi bi-check-circle me-2"></i>Update Profile
              </button>
              <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-2"></i>Back to Profile
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
