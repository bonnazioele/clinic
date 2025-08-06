@extends('admin.layouts.app')
@section('title','Clinics')
@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fas fa-hospital me-2"></i>Clinics Management</h3>
    <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> New Clinic
    </a>
  </div>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Logo</th>
              <th>Clinic Details</th>
              <th>Contact</th>
              <th>Type</th>
              <th>Services</th>
              <th>Location</th>
              <th width="150">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($clinics as $clinic)
            <tr>
              <td>
                @if($clinic->logo)
                  <img src="{{ asset('storage/' . $clinic->logo) }}" 
                       alt="Logo" 
                       class="img-thumbnail" 
                       style="width: 40px; height: 40px; object-fit: cover;">
                @else
                  <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                       style="width: 40px; height: 40px;">
                    <i class="fas fa-image text-muted"></i>
                  </div>
                @endif
              </td>
              <td>
                <div>
                  <strong>{{ $clinic->name }}</strong>
                  <br>
                  <small class="text-muted">{{ $clinic->branch_code }}</small>
                  <br>
                  <small class="text-muted">{{ Str::limit($clinic->address, 30) }}</small>
                </div>
              </td>
              <td>
                <div class="small">
                  @if($clinic->contact_number)
                    <div><i class="fas fa-phone text-muted me-1"></i>{{ $clinic->contact_number }}</div>
                  @endif
                  @if($clinic->email)
                    <div><i class="fas fa-envelope text-muted me-1"></i>{{ $clinic->email }}</div>
                  @endif
                </div>
              </td>
              <td>
                @if($clinic->type)
                  <span class="badge bg-info">{{ $clinic->type->type_name }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @if($clinic->services->count() > 0)
                  <div class="small">
                    @foreach($clinic->services->take(2) as $service)
                      <span class="badge bg-secondary me-1 mb-1">{{ $service->service_name }}</span>
                    @endforeach
                    @if($clinic->services->count() > 2)
                      <span class="text-muted">+{{ $clinic->services->count() - 2 }} more</span>
                    @endif
                  </div>
                @else
                  <span class="text-muted small">No services</span>
                @endif
              </td>
              <td class="small">
                @if($clinic->gps_latitude && $clinic->gps_longitude)
                  <div>{{ number_format($clinic->gps_latitude, 4) }},</div>
                  <div>{{ number_format($clinic->gps_longitude, 4) }}</div>
                @else
                  <span class="text-muted">No location</span>
                @endif
              </td>
              <td>
                <div class="btn-group-vertical" role="group">
                  <a href="{{ route('admin.clinics.edit', $clinic) }}" 
                     class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                  </a>
                  <form method="POST" 
                        action="{{ route('admin.clinics.destroy', $clinic) }}"
                        class="d-inline" 
                        onsubmit="return confirm('Are you sure you want to delete this clinic?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger w-100">
                      <i class="fas fa-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No clinics found</h5>
                <p class="text-muted">Get started by creating your first clinic.</p>
                <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
                  <i class="fas fa-plus me-1"></i> Create First Clinic
                </a>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($clinics->hasPages())
    <div class="mt-3">
      {{ $clinics->links() }}
    </div>
  @endif
@endsection
