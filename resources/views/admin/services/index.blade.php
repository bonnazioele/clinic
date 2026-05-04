@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<style>
  .admin-services-page {
    width: 100%;
  }

  .services-hero {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 32px;
    padding: 28px;
    margin-bottom: 24px;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.15), transparent 32%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 30%),
      rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
  }

  .services-hero-icon {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #14b8ff);
    color: #ffffff;
    font-size: 2rem;
    box-shadow: 0 18px 34px rgba(13, 110, 253, 0.28);
    flex-shrink: 0;
  }

  .services-hero-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 2.8rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .services-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 1rem;
  }

  .services-stat-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #ffffff;
    padding: 18px 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
  }

  .services-stat-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 900;
    margin-bottom: 4px;
  }

  .services-stat-value {
    color: #0f172a;
    font-size: 2rem;
    line-height: 1;
    font-weight: 900;
    margin: 0;
  }

  .services-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
    margin-bottom: 24px;
  }

  .services-panel-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.13), transparent 30%),
      linear-gradient(180deg, #ffffff, #f8fafc);
  }

  .services-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -0.02em;
  }

  .services-panel-title i {
    color: #0d6efd;
  }

  .services-panel-subtitle {
    color: #64748b;
    font-size: 0.92rem;
    margin: 6px 0 0;
  }

  .services-panel-body {
    padding: 24px;
  }

  .filter-control {
    border-radius: 18px;
    min-height: 50px;
  }

  .service-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    background: #ffffff;
    padding: 18px;
    transition: 0.2s ease;
    height: 100%;
  }

  .service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.09);
  }

  .service-icon {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.10);
    color: #0d6efd;
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  .service-name {
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 900;
    margin: 0;
  }

  .service-meta {
    color: #64748b;
    font-size: 0.88rem;
    margin: 6px 0 0;
  }

  .service-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 7px 11px;
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
    font-size: 0.78rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .service-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    flex-wrap: wrap;
  }

  .service-action-btn {
    width: 42px;
    height: 42px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .empty-state {
    border: 1px dashed rgba(148, 163, 184, 0.55);
    border-radius: 24px;
    background: #f8fafc;
    padding: 42px 20px;
    text-align: center;
  }

  .empty-icon {
    width: 76px;
    height: 76px;
    border-radius: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.10);
    color: #0d6efd;
    font-size: 2rem;
    margin-bottom: 14px;
  }

  .delete-modal {
    border: 0;
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
  }

  .delete-modal .modal-header {
    border-bottom: 0;
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #ffffff;
  }

  .delete-modal .btn-close {
    filter: invert(1);
  }

  .pagination-wrap {
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    padding: 16px 24px;
    background: #f8fafc;
  }

  @media (max-width: 767.98px) {
    .services-hero,
    .services-panel {
      border-radius: 24px;
    }

    .services-panel-body,
    .services-panel-header {
      padding: 18px;
    }

    .service-actions {
      justify-content: flex-start;
    }
  }
</style>

<div class="admin-services-page">

  <div class="services-hero">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="d-flex align-items-center gap-3 gap-md-4">
          <div class="services-hero-icon">
            <i class="bi bi-clipboard2-pulse"></i>
          </div>

          <div>
            <h1 class="services-hero-title">Services Management</h1>
            <p class="services-hero-text">
              Manage medical services that clinics can offer to patients.
            </p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="services-stat-card">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <div>
              <div class="services-stat-label">Total Services</div>
              <h2 class="services-stat-value">{{ $services->total() }}</h2>
            </div>

            <span class="service-icon" style="background:rgba(16,185,129,0.14);color:#047857;">
              <i class="bi bi-list-check"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if(session('success') || session('status'))
    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
      <i class="bi bi-check2-circle me-2"></i>
      {{ session('success') ?? session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
      <div class="fw-bold mb-1">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Please fix the highlighted errors.
      </div>

      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="services-panel">
    <div class="services-panel-header">
      <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div>
          <h2 class="services-panel-title">
            <i class="bi bi-funnel"></i>
            Filter Services
          </h2>
          <p class="services-panel-subtitle">
            Search services by name or sort the service list.
          </p>
        </div>

        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>
          Add Service
        </a>
      </div>
    </div>

    <div class="services-panel-body">
      <form method="GET" action="{{ route('admin.services.index') }}" class="row g-3 align-items-end">
        <div class="col-md-6 col-lg-5">
          <label class="form-label">
            <i class="bi bi-search me-1"></i>
            Search
          </label>

          <input type="text"
                 name="q"
                 value="{{ request('q') }}"
                 class="form-control filter-control"
                 placeholder="Search service name...">
        </div>

        <div class="col-md-4 col-lg-3">
          <label class="form-label">
            <i class="bi bi-sort-alpha-down me-1"></i>
            Sort
          </label>

          <select name="sort" class="form-select filter-control">
            <option value="" @selected(request('sort') === null || request('sort') === '')>
              Newest First
            </option>
            <option value="az" @selected(request('sort') === 'az')>
              Name A → Z
            </option>
            <option value="za" @selected(request('sort') === 'za')>
              Name Z → A
            </option>
          </select>
        </div>

        <div class="col-md-2">
          <button class="btn btn-primary w-100 filter-control">
            <i class="bi bi-funnel me-1"></i>
            Apply
          </button>
        </div>

        <div class="col-md-2">
          @if(request()->hasAny(['q', 'sort']) && (request('q') || request('sort')))
            <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary w-100 filter-control">
              <i class="bi bi-x-circle me-1"></i>
              Clear
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <div class="services-panel">
    <div class="services-panel-header">
      <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div>
          <h2 class="services-panel-title">
            <i class="bi bi-gear-wide-connected"></i>
            Available Services
          </h2>
          <p class="services-panel-subtitle">
            {{ $services->total() }} service{{ $services->total() === 1 ? '' : 's' }} found.
          </p>
        </div>

        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
          {{ $services->total() }} total
        </span>
      </div>
    </div>

    <div class="services-panel-body">
      @if($services->count())
        <div class="row g-3">
          @foreach($services as $service)
            <div class="col-12">
              <div class="service-card">
                <div class="row align-items-center g-3">
                  <div class="col-lg-6">
                    <div class="d-flex align-items-center gap-3">
                      <span class="service-icon">
                        <i class="bi bi-clipboard2-pulse"></i>
                      </span>

                      <div class="min-w-0">
                        <h3 class="service-name text-truncate">
                          {{ $service->name }}
                        </h3>

                        <p class="service-meta">
                          {{ $service->description ?: 'No description provided.' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-3">
                    <span class="service-badge">
                      <i class="bi bi-hospital"></i>
                      {{ $service->clinics_count }} clinic{{ $service->clinics_count == 1 ? '' : 's' }} using this
                    </span>
                  </div>

                  <div class="col-sm-6 col-lg-3">
                    <div class="service-actions">
                      <a href="{{ route('admin.services.edit', $service) }}"
                         class="btn btn-outline-primary service-action-btn"
                         title="Edit Service">
                        <i class="bi bi-pencil-square"></i>
                      </a>

                      <form method="POST"
                            action="{{ route('admin.services.destroy', $service) }}"
                            class="d-inline service-delete-form"
                            id="delete-service-{{ $service->id }}">
                        @csrf
                        @method('DELETE')

                        <button type="button"
                                class="btn btn-outline-danger service-action-btn btn-delete-service"
                                data-service-name="{{ $service->name }}"
                                data-delete-form-id="delete-service-{{ $service->id }}"
                                title="Delete Service">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <div class="empty-icon">
            <i class="bi bi-gear-x"></i>
          </div>

          <h5 class="fw-bold text-dark mb-2">No services found</h5>
          <p class="text-muted mb-4">
            Get started by adding your first medical service.
          </p>

          <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            Add First Service
          </a>
        </div>
      @endif
    </div>

    @if($services->hasPages())
      <div class="pagination-wrap">
        <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-3 small">
          <div class="text-muted order-2 order-md-1">
            Showing
            <strong>{{ $services->firstItem() }}</strong>–<strong>{{ $services->lastItem() }}</strong>
            of <strong>{{ $services->total() }}</strong>
          </div>

          <div class="order-1 order-md-2">
            {{ $services->appends(request()->only(['q', 'sort']))->links('vendor.pagination.compact') }}
          </div>
        </div>
      </div>
    @endif
  </div>

</div>

<div class="modal fade" id="confirmDeleteServiceModal" tabindex="-1" aria-labelledby="confirmDeleteServiceLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content delete-modal">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="confirmDeleteServiceLabel">
          <i class="bi bi-trash-fill"></i>
          Delete Service
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <p class="mb-2">You're about to delete this service:</p>
        <p class="fw-bold fs-5 mb-3" id="deleteServiceName"></p>
        <p class="small text-muted mb-0">
          This action is permanent. Deleted services cannot be restored.
        </p>
      </div>

      <div class="modal-footer border-0 pt-0 px-4 pb-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>

        <button type="button" class="btn btn-danger" id="confirmDeleteServiceBtn">
          <i class="bi bi-trash me-1"></i>
          Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('confirmDeleteServiceModal');
  if (!modalEl) return;

  const nameEl = document.getElementById('deleteServiceName');
  const confirmBtn = document.getElementById('confirmDeleteServiceBtn');

  let targetFormId = null;
  let bsModal = null;

  if (window.bootstrap && bootstrap.Modal) {
    bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
  }

  document.querySelectorAll('.btn-delete-service').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const svcName = this.getAttribute('data-service-name') || 'Unknown Service';
      const formId = this.getAttribute('data-delete-form-id');

      targetFormId = formId;
      nameEl.textContent = svcName;

      if (bsModal) {
        bsModal.show();
      } else if (confirm('Delete ' + svcName + '?')) {
        const form = document.getElementById(formId);
        if (form) form.submit();
      }
    });
  });

  confirmBtn.addEventListener('click', function () {
    if (!targetFormId) return;

    const form = document.getElementById(targetFormId);
    if (!form) return;

    form.submit();
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    targetFormId = null;
  });
});
</script>
@endsection