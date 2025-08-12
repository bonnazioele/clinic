@extends('layouts.app')
@section('title','Queue Status')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card">
    <div class="card-header">Your Queue Status</div>
    <div class="card-body text-center">
      <h2>#{{ $entry->queue_number }}</h2>
      <p class="lead">
        @if($entry->status==='waiting')
          {{ $ahead }} people ahead of you.
        @else
          You were served at {{ $entry->served_at->format('g:i A') }}.
        @endif
      </p>
    </div>
  </div>
</div>
@endsection
