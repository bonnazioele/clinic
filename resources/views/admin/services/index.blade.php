@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<div class="container py-4">


  {{-- Header Card --}}
  <div class="medical-card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-start align-items-lg-center">
      <div>
        <h1 class="h2 fw-bold text-primary mb-1"><i class="bi bi-gear-wide-connected me-2"></i>Services Management</h1>
        <p class="text-muted mb-0">Manage and configure available medical services</p>
      </div>
      <div class="ms-lg-auto">
        <a href="{{ route('admin.services.create') }}" class="btn btn-success">
          <i class="bi bi-plus-circle me-2"></i>Add Service
        </a>
      </div>
    </div>
  </div>

  
  <div class="medical-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.services.index') }}" class="row g-3 align-items-end">
      <div class="col-md-5 col-lg-4">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-lg" placeholder="Search service name..." />
      </div>
      <div class="col-md-4 col-lg-3">
        <label class="form-label fw-semibold"><i class="bi bi-sort-alpha-down me-1"></i>Order</label>
        <select name="sort" class="form-select form-select-lg">
          <option value="" @selected(request('sort')===null || request('sort')==='')>Newest First</option>
          <option value="az" @selected(request('sort')==='az')>Name A → Z</option>
          <option value="za" @selected(request('sort')==='za')>Name Z → A</option>
        </select>
      </div>
      <div class="col-md-2 col-lg-2">
        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-funnel me-1"></i>Apply</button>
      </div>
      <div class="col-md-2 col-lg-2">
        @if(request()->hasAny(['q','sort']) && (request('q') || request('sort')))
          <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary btn-lg w-100"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
      </div>
    </form>
  </div>

  

  
  <div class="medical-card p-4">
    <div class="d-flex flex-wrap align-items-center mb-4 gap-2">
      <h4 class="mb-0">Available Services <span class="badge bg-primary" style="font-size:.75rem;">{{ $services->total() }}</span></h4>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr class="bg-light">
            <th class="border-0 px-4 py-3 fw-semibold">Service Name</th>
            <th class="border-0 px-4 py-3 fw-semibold">Clinics Using</th>
            <th class="border-0 px-4 py-3 fw-semibold text-end" style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($services as $service)
            <tr>
              <td class="px-4 py-4 fs-6">
                <span class="fw-semibold text-dark">{{ $service->name }}</span>
              </td>
              <td class="px-4 py-4 fs-6">
                <span class="badge bg-info text-dark px-3 py-2">{{ $service->clinics_count }}</span>
              </td>
              <td class="px-4 py-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('admin.services.edit', $service) }}" 
                     class="btn btn-primary d-flex align-items-center justify-content-center" 
                     style="width:36px;height:36px;"
                     title="Edit Service">
                      <i class="bi bi-pencil-square"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="d-inline service-delete-form" id="delete-service-{{ $service->id }}">
          @csrf
          @method('DELETE')
          <button type="button"
              class="btn btn-danger d-flex align-items-center justify-content-center btn-delete-service"
              style="width:36px;height:36px;"
              data-service-name="{{ $service->name }}"
                            data-delete-form-id="delete-service-{{ $service->id }}"
              title="Delete Service">
            <i class="bi bi-trash3"></i>
          </button>
          </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center py-5">
                <div class="medical-card p-4 mt-4">
                  <i class="bi bi-gear-x display-4 text-muted mb-3"></i>
                  <h5 class="text-muted mb-3">No Services Found</h5>
                  <p class="text-muted mb-4">Get started by adding your first medical service.</p>
                  <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add First Service
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    
    @if($services->hasPages())
      <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-3 p-3 border-top small">
        <div class="text-muted order-2 order-md-1">Showing
          <strong>{{ $services->firstItem() }}</strong>–<strong>{{ $services->lastItem() }}</strong>
          of <strong>{{ $services->total() }}</strong>
        </div>
        <div class="order-1 order-md-2">
          {{ $services->appends(request()->only(['q','sort']))->links('vendor.pagination.compact') }}
        </div>
      </div>
      <style>
        .pagination.pagination-sm .page-link { min-width:34px; text-align:center; }
        /* Row hover background only (Bootstrap's table-hover kept, but reinforce consistent subtle shade) */
        .table.table-hover tbody tr:hover td { background-color:#f8f9fa; }
        /* Static action buttons: prevent movement / color shift on hover/focus */
        .action-btn { width:32px; height:32px; pointer-events:auto; box-shadow:none !important; transition:none !important; }
        .action-btn i { line-height:1; }
        .fixed-primary, .fixed-primary:hover, .fixed-primary:focus, .fixed-primary:active {
          background-color: var(--bs-primary) !important;
          border-color: var(--bs-primary) !important;
          color:#fff !important;
        }
        .fixed-danger, .fixed-danger:hover, .fixed-danger:focus, .fixed-danger:active {
          background-color: var(--bs-danger) !important;
          border-color: var(--bs-danger) !important;
          color:#fff !important;
        }
        .action-btn:focus { outline:none !important; box-shadow:none !important; }
        /* Neutralize hover effects on text links inside table rows (if any appear in future) */
        .table tbody a:not(.btn):hover { color: inherit !important; text-decoration:none !important; }
        /* Prevent badge hover color shifts */
        .table tbody .badge:hover { filter:none !important; opacity:1 !important; }
        /* Remove potential transform/scale on any icon within row on hover */
        .table tbody tr:hover i { transform:none !important; }
      </style>
    @endif
  </div>
</div>
@endsection

@push('modals')

<div class="modal fade" id="confirmDeleteServiceModal" tabindex="-1" aria-labelledby="confirmDeleteServiceLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title d-flex align-items-center gap-2" id="confirmDeleteServiceLabel">
          <i class="bi bi-trash-fill"></i>
          Delete Service
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">You're about to delete the service:</p>
        <p class="fw-bold mb-3" id="deleteServiceName"></p>
        <p class="small text-muted mb-0">This action is permanent. Deleted services cannot be restored.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteServiceBtn">
          <i class="bi bi-trash me-1"></i>Delete
        </button>
      </div>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const modalEl = document.getElementById('confirmDeleteServiceModal');
    if(!modalEl) return;
    const nameEl = document.getElementById('deleteServiceName');
  const confirmBtn = document.getElementById('confirmDeleteServiceBtn');
    let targetFormId = null;

    // Bootstrap modal instance
    let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.querySelectorAll('.btn-delete-service').forEach(btn => {
      btn.addEventListener('click', function(){
        const svcName = this.getAttribute('data-service-name') || 'Unknown Service';
        const formId = this.getAttribute('data-delete-form-id');
        targetFormId = formId;
        nameEl.textContent = svcName;
        bsModal.show();
      });
    });

    confirmBtn.addEventListener('click', function(){
      if(!targetFormId) return;
      const form = document.getElementById(targetFormId);
      if(!form) return;
      form.submit();
    });

    modalEl.addEventListener('hidden.bs.modal', function(){
      targetFormId = null;
    });
  });
</script>
@endpush
