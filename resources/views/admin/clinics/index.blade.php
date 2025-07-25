@extends('admin.layouts.app')
@section('title','Clinics')
@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Clinics</h3>
    <a href="{{ route('admin.clinics.create') }}" class="btn btn-sm btn-primary">
      + New Clinic
    </a>
  </div>
  <table class="table table-striped">
    <thead>
      <tr><th>Name</th><th>Address</th><th>Lat, Lng</th><th></th></tr>
    </thead>
    <tbody>
      @foreach($clinics as $c)
      <tr>
        <td>{{ $c->name }}</td>
        <td>{{ $c->address }}</td>
        <td>{{ $c->latitude }}, {{ $c->longitude }}</td>
        <td class="text-end">
          <a href="{{ route('admin.clinics.edit',$c) }}" class="btn btn-sm btn-outline-secondary">
            Edit
          </a>
          <form method="POST" action="{{ route('admin.clinics.destroy',$c) }}"
                class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $clinics->links() }}
@endsection
