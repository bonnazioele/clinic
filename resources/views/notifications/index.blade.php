@extends('layouts.app')
@section('title','Notifications')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between mb-3">
    <h3>Notifications</h3>
    <form method="POST" action="{{ route('notifications.markRead') }}">
      @csrf
      <button class="btn btn-sm btn-outline-secondary">Mark all as read</button>
    </form>
  </div>

  @if($all->isEmpty())
    <p>No notifications.</p>
  @else
    <ul class="list-group">
      @foreach($all as $note)
        <li class="list-group-item {{ $note->read_at ? '' : 'fw-bold' }}">
          {{ $note->data['message'] }}
          <small class="text-muted d-block">{{ $note->created_at->diffForHumans() }}</small>
        </li>
      @endforeach
    </ul>
    {{ $all->links() }}
  @endif
</div>
@endsection
