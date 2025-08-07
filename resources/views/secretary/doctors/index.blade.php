@extends('layouts.secretary')
@section('title','Manage Doctors')

@section('content')
<div class="container-fluid">
  @include('partials.alerts')

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="card-title mb-0">
      <i class="bi bi-people me-2"></i>
      Doctors List ({{ $doctors->total() }} total)
    </h3>
    <a href="{{ route('secretary.doctors.create') }}"
       class="btn btn-primary">
       <i class="bi bi-person-plus me-2"></i>
       Add New Doctor
    </a>
  </div>

  @if($doctors->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="bi bi-people display-1 text-muted mb-3"></i>
        <h5 class="text-muted">No Doctors Added Yet</h5>
        <p class="text-muted mb-3">Start by adding your first doctor to the clinic.</p>
        <a href="{{ route('secretary.doctors.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-2"></i>
          Add First Doctor
        </a>
      </div>
    </div>
  @else
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Name</th>
                <th>Services</th>
                <th>Email</th>
                <th>Phone</th>
                <th class="pe-3 text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($doctors as $doctor)
              <tr>
                <td class="ps-3">
                  <div class="d-flex align-items-center">
                    <div class="user-avatar me-3" style="width: 40px; height: 40px; font-size: 16px;">
                      {{ substr($doctor->first_name, 0, 1) }}{{ substr($doctor->last_name, 0, 1) }}
                    </div>
                    <div>
                      <div class="fw-medium">{{ $doctor->name }}</div>
                      <small class="text-muted">Doctor</small>
                    </div>
                  </div>
                </td>
                <td>
                  @php
                    $clinicId = session('current_clinic_id');
                    $services = $doctor->servicesForClinic($clinicId)->get();
                  @endphp
                  @if($services->count() > 0)
                    <div class="d-flex flex-wrap gap-1">
                      @foreach($services->take(2) as $service)
                        <span class="badge bg-light text-dark">{{ $service->name }}</span>
                      @endforeach
                      @if($services->count() > 2)
                        <span class="badge bg-secondary">+{{ $services->count() - 2 }} more</span>
                      @endif
                    </div>
                  @else
                    <span class="text-muted">No services assigned</span>
                  @endif
                </td>
                <td>
                  <a href="mailto:{{ $doctor->email }}" class="text-decoration-none">
                    {{ $doctor->email }}
                  </a>
                </td>
                <td>
                  @if($doctor->phone)
                    <a href="tel:{{ $doctor->phone }}" class="text-decoration-none">
                      {{ $doctor->phone }}
                    </a>
                  @else
                    <span class="text-muted">Not provided</span>
                  @endif
                </td>
                <td class="pe-3 text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('secretary.doctors.edit', $doctor) }}"
                       class="btn btn-sm btn-outline-primary"
                       title="Edit Doctor">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST"
                          action="{{ route('secretary.doctors.destroy', $doctor) }}"
                          class="d-inline"
                          onsubmit="return confirm('Are you sure you want to remove {{ $doctor->name }} from this clinic? This action cannot be undone.')">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger"
                              title="Remove Doctor">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      @if($doctors->hasPages())
        <div class="card-footer bg-white border-top">
          <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
              Showing {{ $doctors->firstItem() }} to {{ $doctors->lastItem() }} of {{ $doctors->total() }} doctors
            </div>
            {{ $doctors->links() }}
          </div>
        </div>
      @endif
    </div>
  @endif
</div>
@endsection
