@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
          <i class="bi bi-bell-fill me-2"></i>Notifications
        </h2>
        @if(auth()->user()->unreadNotifications->count() > 0)
          <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary rounded-pill">
              <i class="bi bi-check-all me-2"></i>Mark All as Read
            </button>
          </form>
        @endif
      </div>

      @if(auth()->user()->notifications->count() > 0)
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-0">
            @foreach(auth()->user()->notifications()->latest()->paginate(20) as $notification)
              <div class="p-3 border-bottom {{ $loop->last ? 'border-bottom-0' : '' }} {{ $notification->read_at ? 'bg-light' : 'bg-white' }}">
                <div class="d-flex align-items-start">
                  <div class="flex-shrink-0 me-3">
                    @if($notification->read_at)
                      <i class="bi bi-circle text-muted"></i>
                    @else
                      <i class="bi bi-circle-fill text-primary"></i>
                    @endif
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <p class="mb-1 {{ $notification->read_at ? 'text-muted' : 'fw-semibold' }}">
                          {{ $notification->data['message'] ?? 'Notification' }}
                        </p>
                        <small class="text-muted">
                          {{ $notification->created_at->diffForHumans() }}
                        </small>
                      </div>
                      @if($notification->data['link'] ?? false)
                        <a href="{{ $notification->data['link'] }}" class="btn btn-sm btn-outline-primary rounded-pill">
                          View
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
          {{ auth()->user()->notifications()->latest()->paginate(20)->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
          <h4 class="text-muted mt-3">No notifications yet</h4>
          <p class="text-muted">You'll see important updates and reminders here.</p>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
