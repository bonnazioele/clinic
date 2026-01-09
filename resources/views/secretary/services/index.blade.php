@extends('layouts.app')
@section('title','Clinic Services')
@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold text-primary mb-0 d-flex align-items-center">
        <i class="bi bi-gear-wide-connected medical-icon me-2"></i>Clinic Services
      </h2>
      <div class="d-flex gap-2">
        <a href="{{ route('secretary.services.create',['clinic_id'=>$clinic?->id]) }}" class="btn btn-success">
          <i class="bi bi-plus-circle me-2"></i>New Service
        </a>
      </div>
    </div>
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-sm-4 col-md-3">
        <label for="clinic_filter" class="form-label fw-semibold">Clinic</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-hospital"></i></span>
          <select id="clinic_filter" name="clinic_id" class="form-select" onchange="this.form.submit()">
            @foreach($assignedClinics as $c)
              <option value="{{ $c->id }}" @if($clinic && $clinic->id===$c->id) selected @endif>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-sm-4 col-md-3 d-flex align-items-end">
        @if($clinic)
          <span class="badge bg-info text-dark"><i class="bi bi-list-check me-1"></i>{{ $services->count() }} services</span>
        @endif
      </div>
    </form>
  </div>

  @if(!$clinic)
    <div class="alert alert-warning">You are not assigned to any clinics.</div>
  @else
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="medical-card p-4 h-100">
          <h5 class="fw-semibold mb-3 text-primary d-flex align-items-center">
            <i class="bi bi-link-45deg medical-icon me-2"></i>
            Attached Services <span class="badge bg-secondary ms-2">{{ $services->count() }}</span>
          </h5>
          @if($services->isEmpty())
            <div class="text-muted">No services attached to this clinic yet.</div>
          @else
            <table class="table align-middle">
              <thead>
                <tr>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Description</th>
                  <th class="px-4 py-3">Duration</th>
                  <th class="px-4 py-3 text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($services as $s)
                <tr>
                  <td class="fw-semibold px-4 py-3">{{ $s->name }}</td>
                  <td class="small text-muted px-4 py-3" style="max-width:240px">{{ Str::limit($s->description,80) }}</td>
                  <td class="px-4 py-3"><span class="badge bg-secondary">{{ $s->pivot->duration_minutes }}m</span></td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <form method="POST" action="{{ route('secretary.services.detach',[$clinic,$s]) }}"
                            data-confirm="Detach this service from the clinic? Patients with upcoming appointments keep their bookings."
                            data-confirm-title="Detach Service"
                            data-confirm-btn="Detach">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-warning"><i class="bi bi-link-45deg me-1"></i>Detach</button>
                      </form>
                      <form method="POST" action="{{ route('secretary.services.destroy',$s) }}"
                            data-confirm="Delete this service from the master list? This cannot be undone."
                            data-confirm-title="Delete Service"
                            data-confirm-btn="Delete">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
      <div class="col-lg-5">
        <div class="medical-card p-4 h-100">
          <h5 class="fw-semibold mb-3 text-primary d-flex align-items-center">
            <i class="bi bi-plus-circle medical-icon me-2"></i>Attach from Master List
          </h5>
          @if($availableServices->isEmpty())
            <div class="text-muted">All services already attached.</div>
          @else
            <form method="POST" action="{{ route('secretary.services.attach',$clinic) }}">
              @csrf
              <div class="mb-3">
                <label for="serviceSelect" class="form-label fw-semibold d-inline-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-list-check"></i>
                  <span>Select Services</span>
                </label>

                <div id="serviceSelectSkeleton" class="form-control select-placeholder-skeleton">Choose services...</div>
    <select id="serviceSelect"
      name="service_ids[]"
      class="form-select enhance-hidden @error('service_ids') is-invalid @enderror"
      multiple required>
                  @foreach($availableServices as $svc)
                    <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                  @endforeach
                </select>
                @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label for="duration_minutes" class="form-label fw-semibold d-inline-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-stopwatch"></i>
                  <span>Default Duration (minutes)</span>
                </label>
                <input id="duration_minutes" type="number" name="duration_minutes" value="30" min="5" max="480" class="form-control @error('duration_minutes') is-invalid @enderror">
                @error('duration_minutes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-success"><i class="bi bi-plus-circle me-2"></i>Attach Selected</button>
              </div>
            </form>
          @endif
          <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Removal blocked if service already used in appointments.</p>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://code.jquery.com">
<link rel="dns-prefetch" href="https://code.jquery.com">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  /* Custom style for Select2 to blend with Bootstrap */
  .select2-container .select2-selection--multiple {
    min-height: 48px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 4px 8px;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #0d6efd;
    border: none;
    color: #fff;
    padding: 2px 8px;
    margin-top: 4px;
  }
  /* Prevent flash of native select before Select2 initializes */
  .enhance-hidden { visibility: hidden; }
  /* Placeholder skeleton that mimics a form-control */
  .select-placeholder-skeleton {
    display: flex;
    align-items: center;
    min-height: 48px;
    color: #6c757d; /* text-muted */
    pointer-events: none;
  }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  (function() {
    function enhanceServiceSelect() {
      var sel = document.getElementById('serviceSelect');
      if (!sel) return;
      var skeleton = document.getElementById('serviceSelectSkeleton');

      // If Select2 is available, initialize once; otherwise, show native select
      if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        var $sel = window.jQuery(sel);
        if (!$sel.hasClass('select2-hidden-accessible')) {
          $sel.select2({
            placeholder: 'Choose services...',
            allowClear: true,
            width: '100%'
          });
        }
        $sel.removeClass('enhance-hidden');
        if (skeleton) skeleton.classList.add('d-none');
      } else {
        // Fallback: ensure the control remains visible even if Select2 fails to load
        sel.classList.remove('enhance-hidden');
        if (skeleton) skeleton.classList.add('d-none');
      }
    }

    // Run on multiple lifecycle events to handle refresh, back/forward cache, or PJAX/Turbo
    document.addEventListener('DOMContentLoaded', enhanceServiceSelect);
    window.addEventListener('load', enhanceServiceSelect);
    window.addEventListener('pageshow', enhanceServiceSelect);
    document.addEventListener('turbo:load', enhanceServiceSelect);
    document.addEventListener('turbolinks:load', enhanceServiceSelect);
    document.addEventListener('livewire:load', enhanceServiceSelect);
    // Safety fallback: retry shortly after first pass
    setTimeout(enhanceServiceSelect, 800);
  })();
</script>
@endpush
