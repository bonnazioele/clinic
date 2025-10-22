@extends('admin.layouts.app')

@section('content')
<div class="container py-4">

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-people medical-icon me-2"></i>Users Overview
        </h2>
        <p class="text-muted mb-0">Search users and see key info</p>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-lg-5">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="name, email or phone">
      </div>
      <div class="col-lg-3">
        <label class="form-label fw-semibold"><i class="bi bi-filter me-1"></i>Role</label>
        <select name="role" class="form-select">
          @php $current = request('role', $role ?? 'all'); @endphp
          <option value="all" @selected($current==='all')>All</option>
          <option value="patient" @selected($current==='patient')>Patients</option>
          <option value="doctor" @selected($current==='doctor')>Doctors</option>
          <option value="secretary" @selected($current==='secretary')>Secretaries</option>
          <option value="admin" @selected($current==='admin')>Admins</option>
        </select>
      </div>
      <div class="col-lg-2">
        <button class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i>Apply
        </button>
      </div>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
    <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th><i class="bi bi-person me-1"></i>Name</th>
            <th><i class="bi bi-envelope me-1"></i>Email</th>
            @php $current = request('role', $role ?? 'patient'); @endphp
            @if($current === 'doctor')
              <th><i class="bi bi-building me-1"></i>Clinics</th>
              <th><i class="bi bi-briefcase-medical me-1"></i>Services</th>
              <th><i class="bi bi-telephone me-1"></i>Phone</th>
            @elseif($current === 'secretary')
              <th><i class="bi bi-building me-1"></i>Clinics</th>
              <th><i class="bi bi-telephone me-1"></i>Phone</th>
            @elseif($current === 'all')
              <th><i class="bi bi-diagram-3 me-1"></i>Role</th>
              <th><i class="bi bi-telephone me-1"></i>Phone</th>
            @else
              <th><i class="bi bi-telephone me-1"></i>Phone</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @php
            $colspan = 3;
            if ($current === 'doctor') { $colspan = 5; }
            elseif ($current === 'secretary') { $colspan = 4; }
            elseif ($current === 'all') { $colspan = 4; }
          @endphp
          @forelse($users as $u)
            <tr>
              <td class="py-3 ps-4">{{ $u->name }}</td>
              <td class="py-3 ps-4">{{ $u->email }}</td>
              @if($current === 'doctor')
                <td class="py-3 ps-4">
                  @php
                    // prefer clinicsAsDoctor if loaded, fallback to clinics for legacy
                    $doctorClinics = collect();
                    if ($u->relationLoaded('clinicsAsDoctor') && $u->clinicsAsDoctor->isNotEmpty()) {
                        $doctorClinics = $u->clinicsAsDoctor;
                    } elseif ($u->relationLoaded('clinics') && $u->clinics->isNotEmpty()) {
                        $doctorClinics = $u->clinics;
                    }
                  @endphp
                  @if($doctorClinics->isNotEmpty())
                    @foreach($doctorClinics as $c)
                      <span class="badge rounded-pill bg-info text-dark me-1 mb-1">{{ $c->name }}</span>
                    @endforeach
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="py-3 ps-4">
                  @if($u->relationLoaded('services') && $u->services->isNotEmpty())
                    {{ $u->services->pluck('name')->join(', ') }}
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="py-3 ps-4">{{ $u->phone ?? '—' }}</td>
              @elseif($current === 'secretary')
                <td class="py-3 ps-4">
                  @php $sc = $u->relationLoaded('secretaryClinics') ? $u->secretaryClinics : collect(); @endphp
                  @if($sc->isNotEmpty())
                    @foreach($sc as $c)
                      <span class="badge rounded-pill bg-info text-dark me-1 mb-1">{{ $c->name }}</span>
                    @endforeach
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="py-3 ps-4">
                  @php
                    $phoneDisplay = $u->phone;
                    if (!$phoneDisplay && ($current === 'secretary') && $u->relationLoaded('secretaryClinics')) {
                        $firstClinic = $u->secretaryClinics->first();
                        if ($firstClinic && $firstClinic->contact_number) {
                            $phoneDisplay = $firstClinic->contact_number;
                        }
                    }
                  @endphp
                  {{ $phoneDisplay ?? '—' }}
                </td>
              @elseif($current === 'all')
                <td class="py-3 ps-4">
                  @php
                    $roleLabel = 'Patient';
                    $badgeClass = 'bg-info text-dark'; // Patient: cyan
                    if ($u->is_admin) { $roleLabel = 'Admin'; $badgeClass = 'bg-primary'; } // blue
                    elseif ($u->is_doctor) { $roleLabel = 'Doctor'; $badgeClass = 'bg-success'; } // green
                    elseif ($u->is_secretary) { $roleLabel = 'Secretary'; $badgeClass = 'bg-warning text-dark'; } // yellow
                  @endphp
                  <span class="badge rounded-pill {{ $badgeClass }}">{{ $roleLabel }}</span>
                </td>
                <td class="py-3 ps-4">{{ $u->phone ?? '—' }}</td>
              @else
                <td class="py-3 ps-4">{{ $u->phone }}</td>
              @endif
            </tr>
          @empty
            <tr><td colspan="{{ $colspan }}" class="text-center py-4 text-muted">No users found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
