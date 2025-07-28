@extends('layouts.app')
@section('title','Manage Doctors')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Doctors</h3>
    <a href="{{ route('secretary.doctors.create') }}"
       class="btn btn-primary">+ New Doctor</a>
  </div>

  @if($doctors->isEmpty())
    <p>No doctors added yet.</p>
  @else
    <table class="table">
      <thead>
        <tr>
          <th>Name</th><th>Email</th><th>Phone</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($doctors as $d)
        <tr>
          <td>{{ $d->name }}</td>
          <td>{{ $d->email }}</td>
          <td>{{ $d->phone }}</td>
          <td class="text-end">
            <a href="{{ route('secretary.doctors.edit',$d) }}"
               class="btn btn-sm btn-outline-secondary">Edit</a>
            <form method="POST"
                  action="{{ route('secretary.doctors.destroy',$d) }}"
                  class="d-inline"
                  onsubmit="return confirm('Remove this doctor?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    {{ $doctors->links() }}
  @endif
</div>
@endsection
