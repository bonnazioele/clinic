@extends('admin.layouts.app')

@section('title','Edit Secretary')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-person-gear me-2"></i>
      <h5 class="mb-0">Edit Secretary</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.secretaries.update',$secretary) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-person me-1"></i>Full Name</label>
            <input name="name" class="form-control" value="{{ old('name',$secretary->name) }}" required />
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email',$secretary->email) }}" required />
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-key me-1"></i>New Password (optional)</label>
            <input type="password" name="password" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-key-fill me-1"></i>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-telephone me-1"></i>Phone</label>
            <input name="phone" class="form-control" value="{{ old('phone',$secretary->phone) }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
            <input name="address" class="form-control" value="{{ old('address',$secretary->address) }}" />
          </div>
          <div class="col-12">
            <label class="form-label"><i class="bi bi-building me-1"></i>Assign to Clinics</label>
            <select name="clinic_ids[]" class="form-select enhanced-multiselect" multiple size="6">
              @foreach($clinics as $c)
                <option value="{{ $c->id }}" @selected(in_array($c->id,$selected))>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
          <a href="{{ route('admin.secretaries.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
