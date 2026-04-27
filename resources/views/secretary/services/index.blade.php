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
    </div>
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
                  <th class="px-4 py-3">Doctor Count</th>
                  <th class="px-4 py-3">Doctor In Queue</th>
                  <th class="px-4 py-3 text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($services as $s)
                @php
                  $activeQueueCount = (int) ($activeQueueCounts[$s->id] ?? 0);
                  $activeDoctorCount = (int) ($activeDoctorCounts[$s->id] ?? 0);
                  $doctorInQueueCount = (int) ($doctorInQueueCounts[$s->id] ?? 0);
                  $requiresDetachWarning = $activeQueueCount > 0 || $activeDoctorCount > 0;
                  $queueLabel = $activeQueueCount === 1 ? 'queue entry' : 'queue entries';
                  $doctorLabel = $activeDoctorCount === 1 ? 'doctor link' : 'doctor links';
                  $warningSegments = [];
                  if ($activeQueueCount > 0) {
                    $warningSegments[] = $activeQueueCount.' active '.$queueLabel;
                  }
                  if ($activeDoctorCount > 0) {
                    $warningSegments[] = $activeDoctorCount.' active '.$doctorLabel;
                  }
                  $warningSummary = implode(' and ', $warningSegments);
                @endphp
                <tr>
                  <td>{{ $s->name }}</td>
                  <td>{{ Str::limit($s->description,80) }}</td>
                  <td>{{ $s->pivot->duration_minutes }}m</td>
                  <td>{{ $activeDoctorCount }}</td>
                  <td>{{ $doctorInQueueCount }}</td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-1">
                      <form method="POST" action="{{ route('secretary.services.detach',[$clinic,$s]) }}"
                            data-confirm="{{ $requiresDetachWarning ? 'This service has active appointments and assigned doctors. Proceeding will: Cancel all appointments and notify affected patients. Unlink doctors and clear their related schedules. Close active queues and waitlists. Do you want to proceed?' : 'Do you want to proceed?' }}"
                            data-confirm-title="{{ $requiresDetachWarning ? 'Warning: Active Records Detected' : 'Confirm Detach' }}"
                            @if($requiresDetachWarning)
                            data-confirm-html="<p class='mb-2'>This service has <strong>{{ $warningSummary }}</strong> in this clinic.</p><p class='mb-2'>Proceeding will:</p><ul class='mb-3 ps-3'><li>Cancel all appointments and notify affected patients.</li><li>Unlink doctors and clear their related schedules.</li><li>Close active queues and waitlists.</li></ul><p class='mb-0'>Do you want to proceed?</p>"
                            @else
                            data-confirm-html="<p class='mb-0'>Do you want to proceed?</p>"
                            @endif
                            data-confirm-btn="Proceed">
                        @csrf @method('DELETE')
                        @if($requiresDetachWarning)
                          <input type="hidden" name="proceed_detach" value="1">
                        @endif
                        <button class="btn btn-sm btn-outline-warning"><i class="bi bi-link-45deg me-1"></i>Detach</button>
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
