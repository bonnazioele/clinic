@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
          <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-circle me-2"></i>My Profile
          </h4>
          <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-pencil me-2"></i>Edit Profile
          </a>
        </div>
        <div class="card-body p-4">

          @include('partials.alerts')

          <div class="row">
            <div class="col-md-4 text-center mb-4">
              <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width: 120px; height: 120px;">
                <i class="bi bi-person-fill text-primary" style="font-size: 3rem;"></i>
              </div>
              <h5 class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</h5>
              <p class="text-muted small">{{ $user->email }}</p>
            </div>

            <div class="col-md-8">
              <dl class="row">
                <dt class="col-sm-4">
                  <i class="bi bi-person-badge text-primary me-2"></i>Full Name
                </dt>
                <dd class="col-sm-8">{{ $user->first_name }} {{ $user->last_name }}</dd>

                <dt class="col-sm-4">
                  <i class="bi bi-envelope text-primary me-2"></i>Email
                </dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4">
                  <i class="bi bi-telephone text-primary me-2"></i>Phone
                </dt>
                <dd class="col-sm-8">{{ $user->phone ?? 'Not provided' }}</dd>

                <dt class="col-sm-4">
                  <i class="bi bi-geo-alt text-primary me-2"></i>Address
                </dt>
                <dd class="col-sm-8">{{ $user->address ?? 'Not provided' }}</dd>

                <dt class="col-sm-4">
                  <i class="bi bi-calendar-event text-primary me-2"></i>Birth Date
                </dt>
                <dd class="col-sm-8">
                  {{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('F j, Y') : 'Not provided' }}
                </dd>

                <dt class="col-sm-4">
                  <i class="bi bi-calendar-check text-primary me-2"></i>Member Since
                </dt>
                <dd class="col-sm-8">{{ $user->created_at->format('F j, Y') }}</dd>

                @if($user->medical_document)
                  <dt class="col-sm-4">
                    <i class="bi bi-file-earmark-medical text-primary me-2"></i>Medical Document
                  </dt>
                  <dd class="col-sm-8">
                    <a href="{{ asset('storage/' . $user->medical_document) }}"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                      <i class="bi bi-eye me-1"></i>View Document
                    </a>
                  </dd>
                @endif
              </dl>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
