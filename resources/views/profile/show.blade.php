@extends('layouts.app')

@section('title','My Profile')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">My Profile</h4>
    </div>
    <div class="card-body">
      <p><strong>Name:</strong> {{ $user->name }}</p>
      <p><strong>Email:</strong> {{ $user->email }}</p>
      <p><strong>Phone:</strong> {{ $user->phone ?? '—' }}</p>
      <p><strong>Address:</strong> {{ $user->address ?? '—' }}</p>
      @if($user->medical_document)
        <p>
          <strong>Medical Document:</strong>
          <a href="{{ Storage::url($user->medical_document) }}" target="_blank">
            View / Download
          </a>
        </p>
      @endif

      <a href="{{ route('profile.edit') }}" class="btn btn-primary">
        Edit Profile
      </a>
    </div>
  </div>
</div>
@endsection
