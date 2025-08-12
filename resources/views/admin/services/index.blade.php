@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<div class="container-fluid px-0">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary fw-bold">
      <i class="bi bi-tools me-2"></i>Services Offered
    </h4>
    <a href="{{ route('admin.services.create') }}" class="btn btn-success rounded-pill px-4">
      <i class="bi bi-plus-circle me-2"></i>Add Service
    </a>
  </div>

  {{-- Optional Toast --}}
  <x-alerts.toast />

  {{-- Table --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Description</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($services as $service)
            <tr>
              <td class="fw-semibold text-dark">{{ $service->service_name }}</td>
              <td>{{ Str::limit($service->description, 50) ?: '—' }}</td>
              <td class="text-end">
                <a href="{{ route('admin.services.edit', $service) }}"
                   class="btn btn-sm btn-outline-primary rounded-pill me-1">
                  <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <form method="POST"
                      action="{{ route('admin.services.destroy', $service) }}"
                      class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this service?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-trash me-1"></i>Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted py-4">
                <i class="bi bi-info-circle me-2"></i>No services found.<br>
                <a href="{{ route('admin.services.create') }}" class="text-decoration-none fw-semibold text-primary">
                  + Add your first service
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $services->links() }}
  </div>
</div>
@endsection
