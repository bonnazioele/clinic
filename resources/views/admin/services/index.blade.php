@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Services</h3>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add Service
    </a>
  </div>

  @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ $errors->first('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      @if($services->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($services as $service)
                <tr>
                  <td>{{ $service->id }}</td>
                  <td>
                    <strong>{{ $service->service_name }}</strong>
                  </td>
                  <td>
                    @if($service->description)
                      {{ Str::limit($service->description, 50) }}
                    @else
                      <span class="text-muted">No description</span>
                    @endif
                  </td>
                  <td>
                    @if($service->is_active)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>{{ $service->created_at->format('M d, Y') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="{{ route('admin.services.edit', $service) }}" 
                         class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i>
                      </a>
                      <form method="POST" 
                            action="{{ route('admin.services.destroy', $service) }}" 
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this service?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $services->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No services found</h5>
          <p class="text-muted">Start by adding your first service.</p>
          <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add First Service
          </a>
        </div>
      @endif
    </div>
  </div>
@endsection
