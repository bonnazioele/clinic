@extends('layouts.patient-dashboard')

@section('title','Edit Profile')
@section('page-title','Edit Profile')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <!-- EDIT PROFILE -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h4 class="mb-0">Edit Profile</h4>
    </div>

    <div class="card-body">
      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone',$user->phone) }}">
        </div>

        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control">{{ old('address',$user->address) }}</textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Medical Document (optional)</label>
          <input type="file" name="medical_document" class="form-control">
        </div>

        <button class="btn btn-primary">Save Profile</button>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary ms-2">Cancel</a>
      </form>
    </div>
  </div>


</div>
@endsection
