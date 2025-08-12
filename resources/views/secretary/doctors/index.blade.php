@extends('layouts.app')

@section('title', 'Manage Doctors')

@section('content')
<div class="container py-4">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
          <i class="bi bi-person-badge me-2"></i>Manage Doctors
        </h2>
        <a href="{{ route('secretary.doctors.create') }}" class="btn btn-success rounded-pill">
          <i class="bi bi-plus-circle me-2"></i>Add Doctor
        </a>
      </div>

      @include('partials.alerts')

      @if($doctors->count() > 0)
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Doctor</th>
                    <th>Contact</th>
                    <th>Assigned Clinics</th>
                    <th>Specialties</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($doctors as $doctor)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-3"
                               style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill"></i>
                          </div>
                          <div>
                            <div class="fw-semibold">{{ $doctor->first_name }} {{ $doctor->last_name }}</div>
                            <small class="text-muted">{{ $doctor->email }}</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="small">
                          @if($doctor->phone)
                            <div><i class="bi bi-telephone me-1"></i>{{ $doctor->phone }}</div>
                          @endif
                          @if($doctor->address)
                            <div><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($doctor->address, 30) }}</div>
                          @endif
                        </div>
                      </td>
                      <td>
                        @if($doctor->clinics->count() > 0)
                          @foreach($doctor->clinics->take(2) as $clinic)
                            <span class="badge bg-info text-dark me-1 mb-1">{{ $clinic->name }}</span>
                          @endforeach
                          @if($doctor->clinics->count() > 2)
                            <small class="text-muted">+{{ $doctor->clinics->count() - 2 }} more</small>
                          @endif
                        @else
                          <span class="text-muted small">No clinics assigned</span>
                        @endif
                      </td>
                      <td>
                        @if($doctor->services->count() > 0)
                          @foreach($doctor->services->take(2) as $service)
                            <span class="badge bg-success text-white me-1 mb-1">{{ $service->service_name }}</span>
                          @endforeach
                          @if($doctor->services->count() > 2)
                            <small class="text-muted">+{{ $doctor->services->count() - 2 }} more</small>
                          @endif
                        @else
                          <span class="text-muted small">No specialties</span>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="{{ route('secretary.doctors.edit', $doctor) }}"
                             class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <form method="POST" action="{{ route('secretary.doctors.destroy', $doctor) }}"
                                class="d-inline" onsubmit="return confirm('Delete this doctor?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
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
        </div>

        @if($doctors->hasPages())
          <div class="mt-4 d-flex justify-content-center">
            {{ $doctors->links() }}
          </div>
        @endif
      @else
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">No doctors found</h4>
            <p class="text-muted">Start by adding the first doctor to your system.</p>
            <a href="{{ route('secretary.doctors.create') }}" class="btn btn-primary rounded-pill">
              <i class="bi bi-plus-circle me-2"></i>Add First Doctor
            </a>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
