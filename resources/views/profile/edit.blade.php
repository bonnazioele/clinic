@extends('layouts.app')

@section('title','Edit Profile')

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

  <!-- ADD MEDICAL HISTORY -->
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">Add Medical History</h4>
    </div>

    <div class="card-body">
      <form method="POST" action="{{ route('profile.history.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Clinic Name</label>
            <input type="text" name="clinic_name" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Date of Visit</label>
            <input type="date" name="date_of_visit" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Doctor Name</label>
          <input type="text" name="doctor_name" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Diagnosis</label>
          <textarea name="diagnosis" class="form-control" rows="2" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Treatment</label>
          <textarea name="treatment" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Attach Document (optional)</label>
          <input type="file" name="document_path" class="form-control">
        </div>

        <button class="btn btn-success">Add History Record</button>
      </form>
    </div>
  </div>
</div>
@endsection
