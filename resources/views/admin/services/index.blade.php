@extends('admin.layouts.app')

@section('title','Services')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Services</h3>
    <a href="{{ route('admin.services.create') }}" class="btn btn-sm btn-primary">
      + New Service
    </a>
  </div>

  <table class="table table-hover">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($services as $service)
        <tr>
          <td>{{ $service->name }}</td>
          <td>{{ Str::limit($service->description, 50) }}</td>
          <td class="text-end">
            <a href="{{ route('admin.services.edit', $service) }}"
               class="btn btn-sm btn-outline-secondary">
              Edit
            </a>
            <form method="POST"
                  action="{{ route('admin.services.destroy', $service) }}"
                  class="d-inline"
                  onsubmit="return confirm('Delete this service?');">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="text-center text-muted">
            No services found. <a href="{{ route('admin.services.create') }}">Create one</a>.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-3">
    {{ $services->links() }}
  </div>
@endsection
