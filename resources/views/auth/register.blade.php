@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Register</div>
        <div class="card-body">
          <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input id="name" type="text"
                     class="form-control @error('name') is-invalid @enderror"
                     name="name" value="{{ old('name') }}" required autofocus>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input id="phone" type="text"
                     class="form-control @error('phone') is-invalid @enderror"
                     name="phone" value="{{ old('phone') }}">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Address --}}
            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <textarea id="address"
                        class="form-control @error('address') is-invalid @enderror"
                        name="address">{{ old('address') }}</textarea>
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Medical Document --}}
            <div class="mb-3">
              <label for="medical_document" class="form-label">Medical Document (PDF/DOC)</label>
              <input id="medical_document" type="file"
                     class="form-control @error('medical_document') is-invalid @enderror"
                     name="medical_document">
              @error('medical_document')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3 row">
              <div class="col">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password" required>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col">
                <label for="password-confirm" class="form-label">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control"
                       name="password_confirmation" required>
              </div>
            </div>

            <button type="submit" class="btn btn-primary">
              Register
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
