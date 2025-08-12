@extends('layouts.app')

@section('title', 'Queue Status')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0">
          <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-list-ol me-2"></i>Queue Status
          </h4>
        </div>
        <div class="card-body p-4">

          @if($queue)
            <div class="text-center mb-4">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width: 80px; height: 80px;">
                <span class="display-6 fw-bold">{{ $queue->queue_number }}</span>
              </div>
              <h5 class="fw-semibold">Queue Number</h5>
            </div>

            <div class="row">
              <div class="col-md-6">
                <dl class="row">
                  <dt class="col-sm-4">Clinic:</dt>
                  <dd class="col-sm-8">{{ $queue->clinic->name }}</dd>

                  <dt class="col-sm-4">Service:</dt>
                  <dd class="col-sm-8">{{ $queue->service->service_name }}</dd>

                  <dt class="col-sm-4">Date:</dt>
                  <dd class="col-sm-8">{{ \Carbon\Carbon::parse($queue->created_at)->format('F j, Y') }}</dd>
                </dl>
              </div>

              <div class="col-md-6">
                <dl class="row">
                  <dt class="col-sm-4">Status:</dt>
                  <dd class="col-sm-8">
                    @if($queue->status === 'waiting')
                      <span class="badge bg-warning text-dark rounded-pill">Waiting</span>
                    @elseif($queue->status === 'in_progress')
                      <span class="badge bg-info text-white rounded-pill">In Progress</span>
                    @elseif($queue->status === 'completed')
                      <span class="badge bg-success text-white rounded-pill">Completed</span>
                    @else
                      <span class="badge bg-secondary text-white rounded-pill">{{ ucfirst($queue->status) }}</span>
                    @endif
                  </dd>

                  <dt class="col-sm-4">Time:</dt>
                  <dd class="col-sm-8">{{ \Carbon\Carbon::parse($queue->created_at)->format('g:i A') }}</dd>
                </dl>
              </div>
            </div>

            @if($queue->status === 'waiting')
              <div class="alert alert-info rounded-3 mt-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Please wait:</strong> Your number will be called when it's your turn.
                You can check this page for updates.
              </div>
            @endif

            <div class="text-center mt-4">
              <a href="{{ route('queue.status') }}" class="btn btn-primary rounded-pill">
                <i class="bi bi-arrow-clockwise me-2"></i>Refresh Status
              </a>
              <a href="{{ route('clinics.index') }}" class="btn btn-outline-secondary rounded-pill ms-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Clinics
              </a>
            </div>
          @else
            <div class="text-center py-5">
              <i class="bi bi-emoji-frown text-muted" style="font-size: 4rem;"></i>
              <h4 class="text-muted mt-3">No Active Queue</h4>
              <p class="text-muted">You don't have an active queue number at the moment.</p>
              <a href="{{ route('clinics.index') }}" class="btn btn-primary rounded-pill">
                <i class="bi bi-search me-2"></i>Find Clinics
              </a>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
