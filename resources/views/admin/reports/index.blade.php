@extends('admin.layouts.app')
@section('title','Reports')

@section('content')
<div class="container py-5">
  <div class="medical-card p-5 text-center">
    <div class="mb-4">
      <i class="bi bi-graph-up-arrow text-primary" style="font-size:4rem;"></i>
    </div>
    <h1 class="fw-bold mb-3 text-primary">Reports & Analytics</h1>
    <p class="lead text-muted mb-4" style="max-width:760px;margin:0 auto;">
      Powerful insights into clinics, services performance, and user engagement will appear here soon.
    </p>
    <div class="alert alert-primary d-inline-block mb-4">
      <i class="bi bi-hourglass-split me-2"></i>Coming Soon
    </div>
    <div>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-lg">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
      </a>
    </div>
  </div>
</div>
@endsection
