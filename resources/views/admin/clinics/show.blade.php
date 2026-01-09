@extends('admin.layouts.app')

@section('content')
<div class="container py-4">

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
        <a href="{{ route('admin.clinics.index') }}" class="btn btn-light">
          <i class="bi bi-arrow-left me-1"></i>Back to list
        </a>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0"><i class="bi bi-file-earmark-lock me-2"></i>Regulatory Permits</h5>
      <span class="text-muted small">{{ $clinic->permits->count() }} document{{ $clinic->permits->count() === 1 ? '' : 's' }} uploaded</span>
    </div>
    @php $permitsByType = $clinic->permits->keyBy('permit_type'); @endphp
    @if($permitDefinitions->isEmpty())
      <p class="text-muted mb-0">No permit types configured yet.</p>
    @else
      <div class="row g-3 justify-content-center">
        @foreach($permitDefinitions as $key => $definition)
          @php $permit = $permitsByType->get($key); @endphp
          <div class="col-md-4">
            <div class="border rounded p-3 h-100">
              <div class="d-flex align-items-start gap-2 mb-2">
                <i class="bi bi-shield-check text-primary fs-4"></i>
                <div>
                  <strong>{{ $definition['label'] ?? Str::title(str_replace('_',' ', $key)) }}</strong>
                  <div class="small text-muted">{{ $definition['description'] ?? 'Regulatory requirement' }}</div>
                </div>
              </div>
              @if($permit)
                <dl class="row small mb-3">
                  <dt class="col-5 text-muted">Permit #</dt>
                  <dd class="col-7 text-end mb-1">{{ $permit->permit_number ?? '—' }}</dd>
                  <dt class="col-5 text-muted">Issued</dt>
                  <dd class="col-7 text-end mb-1">{{ optional($permit->issued_at)->format('M d, Y') ?? '—' }}</dd>
                  <dt class="col-5 text-muted">Expires</dt>
                  <dd class="col-7 text-end mb-2">{{ optional($permit->expires_at)->format('M d, Y') ?? '—' }}</dd>
                </dl>
                @if($permit->attachment_path)
                  <a href="{{ Storage::disk('public')->url($permit->attachment_path) }}"
                     target="_blank" rel="noopener"
                     class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View Attachment
                  </a>
                @else
                  <span class="badge bg-warning text-dark">No file uploaded</span>
                @endif
              @else
                <p class="text-muted small mb-0">No document submitted for this permit.</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
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
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-person"></i>
                      Name
                    </span>
                  </th>
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-envelope"></i>
                      Email
                    </span>
                  </th>
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-telephone"></i>
                      Phone
                    </span>
                  </th>
                </tr>
              </thead>
              <tbody>
                @foreach($clinic->doctors as $doc)
                  <tr>
                    <td>Dr. {{ $doc->name }}</td>
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
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-person"></i>
                      Name
                    </span>
                  </th>
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-envelope"></i>
                      Email
                    </span>
                  </th>
                  <th class="text-uppercase small fw-semibold">
                    <span class="d-inline-flex align-items-center gap-2">
                      <i class="bi bi-telephone"></i>
                      Phone
                    </span>
                  </th>
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
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-gear"></i>
                  Service
                </span>
              </th>
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-stopwatch"></i>
                  Default Duration
                </span>
              </th>
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
