@extends('admin.layouts.app')

@section('title', 'Clinic Types')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Clinic Types</h3>
    <a href="{{ route('admin.clinic-types.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add Clinic Type
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      @if($clinicTypes->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type Name</th>
                <th>Description</th>
                <th>Clinics Count</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($clinicTypes as $clinicType)
                <tr>
                  <td>{{ $clinicType->id }}</td>
                  <td>
                    <strong>{{ $clinicType->type_name }}</strong>
                  </td>
                  <td>
                    @if($clinicType->description)
                      {{ Str::limit($clinicType->description, 50) }}
                    @else
                      <span class="text-muted">No description</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-info">{{ $clinicType->clinics()->count() }}</span>
                  </td>
                  <td>{{ $clinicType->created_at->format('M d, Y') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('admin.clinic-types.edit', $clinicType) }}" 
                            class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" wire:click="confirmDelete({{ $clinicType->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $clinicTypes->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-building fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No clinic types found</h5>
          <p class="text-muted">Start by adding your first clinic type.</p>
          <a href="{{ route('admin.clinic-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add First Clinic Type
          </a>
        </div>
      @endif
    </div>
  </div>
@endsection
