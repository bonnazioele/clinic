@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <div class="card">
        <div class="card-header">Register</div>
        <div class="card-body">
          <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            {{-- First Name --}}
            <div class="mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input id="first_name" type="text"
                     class="form-control @error('first_name') is-invalid @enderror"
                     name="first_name" value="{{ old('first_name') }}">
              @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Last Name --}}
            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input id="last_name" type="text"
                     class="form-control @error('last_name') is-invalid @enderror"
                     name="last_name" value="{{ old('last_name') }}">
              @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ old('email') }}">
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
              <label for="address" class="form-label">Address (optional)</label>
              <textarea id="address"
                        class="form-control @error('address') is-invalid @enderror"
                        name="address">{{ old('address') }}</textarea>
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>


            {{-- Age --}}
            <div class="mb-3">
              <label for="age" class="form-label">Age (optional)</label>
              <input id="age" type="number" min="0" max="120"
                     class="form-control @error('age') is-invalid @enderror"
                     name="age" value="{{ old('age') }}">
              @error('age')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Birthdate --}}
            <div class="mb-3">
              <label for="birthdate" class="form-label">Birthdate</label>
              <input id="birthdate" type="date"
                     class="form-control @error('birthdate') is-invalid @enderror"
                     name="birthdate" value="{{ old('birthdate') }}">
              @error('birthdate')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3 row">
              <div class="col">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password">
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col">
                <label for="password-confirm" class="form-label">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control"
                       name="password_confirmation">
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
