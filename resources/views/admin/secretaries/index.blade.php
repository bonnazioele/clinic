@extends('admin.layouts.app')

@section('content')
<div class="container py-4">

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-people medical-icon me-2"></i>Secretaries Overview
        </h2>
        <p class="text-muted mb-0">Read-only overview of all secretaries and their assigned clinics</p>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <form method="GET" class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search by name or email</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="e.g. Jane Doe or jane@clinic.com">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i>Filter by clinic</label>
        <select name="clinic_id" class="form-select">
          <option value="">All clinics</option>
          @foreach(\App\Models\Clinic::orderBy('name')->get(['id','name']) as $c)
            <option value="{{ $c->id }}" @selected((string)request('clinic_id') === (string)$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i>Filter
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
            <th><i class="bi bi-building me-1"></i>Clinics</th>
            <th><i class="bi bi-telephone me-1"></i>Phone</th>
          </tr>
        </thead>
        <tbody>
          @forelse($secretaries as $s)
            <tr>
              <td class="py-3">{{ $s->name }}</td>
              <td class="py-3">{{ $s->email }}</td>
              <td class="py-3">
                @if($s->secretaryClinics->isEmpty())
                  <span class="badge bg-secondary">None</span>
                @else
                  <div class="d-flex flex-wrap gap-2">
                    @foreach($s->secretaryClinics as $c)
                      <span class="badge bg-info text-dark">{{ $c->name }}</span>
                    @endforeach
                  </div>
                @endif
              </td>
              <td class="py-3">{{ $s->phone }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No secretaries yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $secretaries->links() }}</div>
</div>
@endsection
