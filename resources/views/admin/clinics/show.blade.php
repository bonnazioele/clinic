@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-building medical-icon me-2"></i>{{ $clinic->name }}
        </h2>
        <div class="text-muted small">
          <i class="bi bi-geo-alt me-1"></i>{{ $clinic->address }}
          @if($clinic->branch_code)
            <span class="ms-3"><i class="bi bi-tag me-1"></i>{{ $clinic->branch_code }}</span>
          @endif
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.clinics.edit', $clinic) }}" class="btn btn-outline-primary">
          <i class="bi bi-pencil me-1"></i>Edit Clinic
        </a>
        <a href="{{ route('admin.clinics.index') }}" class="btn btn-light">
          <i class="bi bi-arrow-left me-1"></i>Back to list
        </a>
      </div>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-3">
        <div class="p-3 border rounded bg-primary text-white">
          <div class="small">Appointments Today</div>
          <div class="h4 mb-0">{{ $todayAppointments }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 border rounded bg-success text-white">
          <div class="small">Total Appointments</div>
          <div class="h4 mb-0">{{ $totalAppointments }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 border rounded bg-warning text-dark">
          <div class="small">Waiting Now</div>
          <div class="h4 mb-0">{{ $waitingCount }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-3 border rounded bg-info text-white">
          <div class="small">Served Today</div>
          <div class="h4 mb-0">{{ $servedToday }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="medical-card p-4 h-100">
        <h5 class="mb-3"><i class="bi bi-person-badge me-2"></i>Doctors</h5>
        @if($clinic->doctors->isEmpty())
          <p class="text-muted">No doctors assigned.</p>
        @else
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                </tr>
              </thead>
              <tbody>
                @foreach($clinic->doctors as $doc)
                  <tr>
                    <td>{{ $doc->name }}</td>
                    <td>{{ $doc->email }}</td>
                    <td>{{ $doc->phone }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-6">
      <div class="medical-card p-4 h-100">
        <h5 class="mb-3"><i class="bi bi-person-gear me-2"></i>Secretaries</h5>
        @if($clinic->secretaries->isEmpty())
          <p class="text-muted">No secretaries assigned.</p>
        @else
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                </tr>
              </thead>
              <tbody>
                @foreach($clinic->secretaries as $sec)
                  <tr>
                    <td>{{ $sec->name }}</td>
                    <td>{{ $sec->email }}</td>
                    <td>{{ $sec->phone }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mt-4">
    <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Services Offered</h5>
    @if($clinic->services->isEmpty())
      <p class="text-muted">No services attached to this clinic.</p>
    @else
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>Service</th>
              <th>Default Duration</th>
            </tr>
          </thead>
          <tbody>
            @foreach($clinic->services as $srv)
              <tr>
                <td>{{ $srv->name }}</td>
                <td>
                  @if(!is_null($srv->pivot?->duration_minutes))
                    {{ $srv->pivot->duration_minutes }} min
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
