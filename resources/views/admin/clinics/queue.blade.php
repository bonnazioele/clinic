@extends('admin.layouts.app')
@section('title',"Queue — {$clinic->name}")

@section('content')
<h3>Queue for {{ $clinic->name }}</h3>

@if($waiting->isEmpty())
  <p>No one is waiting.</p>
@else
  <table class="table">
    <thead><tr><th>#</th><th>Patient</th><th></th></tr></thead>
    <tbody>
      @foreach($waiting as $q)
      <tr>
        <td>{{ $q->queue_number }}</td>
        <td>{{ $q->user->name ?? 'Guest' }}</td>
        <td class="text-end">
          <form method="POST" action="{{ route('admin.clinics.queue.serve', [$clinic,$q]) }}">
            @csrf
            <button class="btn btn-sm btn-success">Serve</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@endif
@endsection
