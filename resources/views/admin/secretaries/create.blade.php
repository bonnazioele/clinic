@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">Add Secretary</h3>
  @include('partials.alerts')

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.secretaries.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="{{ old('phone') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input name="address" class="form-control" value="{{ old('address') }}" />
          </div>
          <div class="col-12">
            <label class="form-label">Assign to Clinics</label>
            <select name="clinic_ids[]" class="form-select" multiple size="6">
              @foreach($clinics as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple clinics.</small>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
          <a href="{{ route('admin.secretaries.index') }}" class="btn btn-link">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
