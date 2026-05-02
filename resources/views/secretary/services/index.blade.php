@extends('layouts.app')
@section('title','Clinic Services')
@section('content')
<div class="container py-4">
  @php
    $hasAvailableServices = $clinic && isset($availableServices) && $availableServices->isNotEmpty();
  @endphp
  @include('partials.alerts')
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold text-primary mb-0 d-flex align-items-center">
        <i class="bi bi-gear-wide-connected medical-icon me-2"></i>Clinic Services
      </h2>
      @if($hasAvailableServices)
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#attachServicesModal">
          <i class="bi bi-plus-circle me-2"></i>Add Service
        </button>
      @endif
    </div>
  </div>

  @if(!$clinic)
    <div class="alert alert-warning">You are not assigned to any clinics.</div>
  @else
    <div class="row g-4">
      <div class="col-12">
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
<<<<<<< Updated upstream
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
=======
                    <form method="POST" action="{{ route('secretary.services.detach',[$clinic,$s]) }}"
                          class="d-inline"
                          data-confirm="Detach this service from the clinic? Patients with upcoming appointments keep their bookings."
                          data-confirm-title="Detach Service"
                          data-confirm-btn="Detach">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-warning"><i class="bi bi-link-45deg me-1"></i>Detach</button>
                    </form>
>>>>>>> Stashed changes
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>

    @if($hasAvailableServices)
    <div class="modal fade" id="attachServicesModal" tabindex="-1" aria-labelledby="attachServicesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <form method="POST" action="{{ route('secretary.services.attach', $clinic) }}" id="attachServicesForm">
            @csrf
            <div class="modal-header border-bottom">
              <div>
                <h5 class="modal-title fw-bold mb-1" id="attachServicesModalLabel">
                  <i class="bi bi-plus-circle-fill text-success me-2"></i>Add Services
                </h5>
                <p class="text-muted small mb-0">Search the master list, select one or more, set durations, then Save.</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <label for="serviceSearchInput" class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Search Services
                  </label>
                  <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="serviceSearchInput" placeholder="Type service name to search..." autocomplete="off">
                  </div>

                  <div id="serviceSearchStatus" class="small text-muted d-none"></div>
                  <div id="serviceSearchResults" class="border rounded" style="max-height: 350px; overflow-y: auto;">
                    <div class="text-muted small p-3 text-center">Start typing to search for services...</div>
                  </div>
                  <div class="d-flex justify-content-center mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="serviceSearchLoadMore">
                      <i class="bi bi-arrow-down-circle me-1"></i>Load more
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">
                      <i class="bi bi-check2-square me-1"></i>Selected Services
                      <span class="badge bg-primary ms-1" id="selectedServicesCount">0</span>
                    </label>
                  </div>
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <label class="form-label small mb-0 text-muted">Apply to all:</label>
                    <input type="number" id="bulkDurationInput" class="form-control form-control-sm" style="width: 80px;" value="30" min="5" max="480" placeholder="min">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="applyBulkDuration">
                      <i class="bi bi-arrow-repeat"></i> Apply
                    </button>
                  </div>
                  <div id="selectedServicesList" class="border rounded bg-light" style="min-height: 100px; max-height: 350px; overflow-y: auto;">
                    <div class="text-muted small p-3 text-center" id="noServicesPlaceholder">
                      <i class="bi bi-inbox me-1"></i>No services selected. Click on services from the left to add them.
                    </div>
                  </div>
                  @error('service_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>

                <input type="hidden" name="duration_minutes" id="defaultDurationMinutes" value="30">
              </div>
            </div>
            <div class="modal-footer border-top">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-1"></i>Cancel
              </button>
              <button type="submit" class="btn btn-success" id="submitAttachBtn" disabled>
                <i class="bi bi-plus-circle me-2"></i>Save Selected Services
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
  @endif
</div>
@endsection

@push('scripts')
<script>
  (function() {
    var modal = document.getElementById('attachServicesModal');
    if (!modal) return;

    var input = document.getElementById('serviceSearchInput');
    var results = document.getElementById('serviceSearchResults');
    var status = document.getElementById('serviceSearchStatus');
    var loadMoreBtn = document.getElementById('serviceSearchLoadMore');
    var selectedList = document.getElementById('selectedServicesList');
    var selectedCount = document.getElementById('selectedServicesCount');
    var submitBtn = document.getElementById('submitAttachBtn');
    var bulkDurationInput = document.getElementById('bulkDurationInput');
    var applyBulkDurationBtn = document.getElementById('applyBulkDuration');
    var defaultDurationInput = document.getElementById('defaultDurationMinutes');

    var nextPageUrl = null;
    var debounceTimer = null;
    var selectedServices = new Map();

    function setStatus(text) {
      if (!status) return;
      if (!text) {
        status.classList.add('d-none');
        status.textContent = '';
        return;
      }
      status.textContent = text;
      status.classList.remove('d-none');
    }

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function isServiceSelected(id) { return selectedServices.has(Number(id)); }

    function updateSelectedCount() {
      var count = selectedServices.size;
      if (selectedCount) selectedCount.textContent = count;
      if (submitBtn) submitBtn.disabled = count === 0;
    }

    function renderSelectedServices() {
      if (!selectedList) return;
      selectedList.innerHTML = '';

      if (selectedServices.size === 0) {
        selectedList.innerHTML = '<div class="text-muted small p-3 text-center" id="noServicesPlaceholder"><i class="bi bi-inbox me-1"></i>No services selected. Click on services to add them.</div>';
        updateSelectedCount();
        return;
      }

      selectedServices.forEach(function(svc, id) {
        var row = document.createElement('div');
        row.className = 'selected-service-row d-flex align-items-center gap-2 p-2 border-bottom bg-white';
        row.setAttribute('data-service-id', id);

        row.innerHTML =
          '<div class="flex-grow-1">' +
            '<span class="fw-semibold">' + escapeHtml(svc.name) + '</span>' +
            (svc.description ? '<div class="small text-muted text-truncate" style="max-width: 300px;">' + escapeHtml(svc.description) + '</div>' : '') +
          '</div>' +
          '<div class="d-flex align-items-center gap-2">' +
            '<div class="input-group input-group-sm" style="width: 100px;">' +
              '<input type="number" class="form-control form-control-sm duration-input" name="duration_minutes_by_service[' + id + ']" value="' + (svc.duration || 30) + '" min="5" max="480" placeholder="min">' +
              '<span class="input-group-text">min</span>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-service-btn" title="Remove"><i class="bi bi-trash"></i></button>' +
          '</div>' +
          '<input type="hidden" name="service_ids[]" value="' + id + '">';

        selectedList.appendChild(row);
      });

      selectedList.querySelectorAll('.remove-service-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var row = btn.closest('.selected-service-row');
          var serviceId = Number(row.getAttribute('data-service-id'));
          selectedServices.delete(serviceId);
          renderSelectedServices();
          updateSearchResultsState();
        });
      });

      selectedList.querySelectorAll('.duration-input').forEach(function(inp) {
        inp.addEventListener('change', function() {
          var row = inp.closest('.selected-service-row');
          var serviceId = Number(row.getAttribute('data-service-id'));
          var svc = selectedServices.get(serviceId);
          if (svc) { svc.duration = parseInt(inp.value, 10) || 30; selectedServices.set(serviceId, svc); }
        });
      });

      updateSelectedCount();
    }

    function addService(svc) {
      var id = Number(svc.id);
      if (selectedServices.has(id)) return;
      selectedServices.set(id, {
        id: id,
        name: svc.name || '',
        description: svc.description || '',
        duration: parseInt(bulkDurationInput.value, 10) || 30
      });
      renderSelectedServices();
      updateSearchResultsState();
    }

    function updateSearchResultsState() {
      if (!results) return;
      results.querySelectorAll('.search-result-item').forEach(function(item) {
        var serviceId = Number(item.getAttribute('data-service-id'));
        if (isServiceSelected(serviceId)) {
          item.classList.add('bg-success', 'bg-opacity-10');
          item.querySelector('.add-service-btn').classList.add('d-none');
          item.querySelector('.added-badge').classList.remove('d-none');
        } else {
          item.classList.remove('bg-success', 'bg-opacity-10');
          item.querySelector('.add-service-btn').classList.remove('d-none');
          item.querySelector('.added-badge').classList.add('d-none');
        }
      });
    }

    function renderSearchResults(items, append) {
      if (!results) return;
      if (!append) results.innerHTML = '';

      if (!items || !items.length) {
        if (!append) results.innerHTML = '<div class="text-muted small p-3 text-center"><i class="bi bi-search me-1"></i>No services found.</div>';
        return;
      }

      items.forEach(function(svc) {
        var id = Number(svc.id);
        var isSelected = isServiceSelected(id);
        var description = (svc.description || '').toString();

        var item = document.createElement('div');
        item.className = 'search-result-item d-flex align-items-center gap-2 p-2 border-bottom cursor-pointer' + (isSelected ? ' bg-success bg-opacity-10' : '');
        item.setAttribute('data-service-id', id);

        item.innerHTML =
          '<div class="flex-grow-1">' +
            '<div class="fw-semibold">' + escapeHtml(String(svc.name || '')) + '</div>' +
            (description ? '<div class="small text-muted text-truncate" style="max-width: 350px;">' + escapeHtml(description) + '</div>' : '') +
          '</div>' +
          '<button type="button" class="btn btn-sm btn-outline-success add-service-btn' + (isSelected ? ' d-none' : '') + '"><i class="bi bi-plus-lg"></i></button>' +
          '<span class="badge bg-success added-badge' + (isSelected ? '' : ' d-none') + '"><i class="bi bi-check-lg me-1"></i>Added</span>';

        item.querySelector('.add-service-btn').addEventListener('click', function(e) {
          e.stopPropagation();
          addService({ id: id, name: svc.name, description: description });
        });

        item.addEventListener('click', function(e) {
          if (e.target.closest('.add-service-btn') || e.target.closest('.added-badge')) return;
          if (!isServiceSelected(id)) addService({ id: id, name: svc.name, description: description });
        });

        results.appendChild(item);
      });
    }

    function updateLoadMoreVisibility() {
      if (!loadMoreBtn) return;
      if (nextPageUrl) loadMoreBtn.classList.remove('d-none');
      else loadMoreBtn.classList.add('d-none');
    }

    function fetchResults(url, append) {
      setStatus('Searching...');
      return fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function(resp) { return resp.json(); })
        .then(function(payload) {
          renderSearchResults(payload.data || [], append);
          nextPageUrl = payload.next_page_url || null;
          updateLoadMoreVisibility();
          setStatus('');
        })
        .catch(function() {
          setStatus('Search failed. Please try again.');
          nextPageUrl = null;
          updateLoadMoreVisibility();
        });
    }

    function doSearch(append) {
      var term = (input && input.value ? input.value : '').trim();
      var url = '{{ route('secretary.services.search') }}' + '?q=' + encodeURIComponent(term);
      return fetchResults(url, !!append);
    }

    if (input) {
      input.addEventListener('input', function() {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
          nextPageUrl = null;
          updateLoadMoreVisibility();
          doSearch(false);
        }, 300);
      });
    }

    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', function() {
        if (!nextPageUrl) return;
        fetchResults(nextPageUrl, true);
      });
    }

    if (applyBulkDurationBtn) {
      applyBulkDurationBtn.addEventListener('click', function() {
        var duration = parseInt(bulkDurationInput.value, 10) || 30;
        if (duration < 5) duration = 5;
        if (duration > 480) duration = 480;

        selectedServices.forEach(function(svc, id) { svc.duration = duration; selectedServices.set(id, svc); });
        if (defaultDurationInput) defaultDurationInput.value = duration;
        renderSelectedServices();
      });
    }

    modal.addEventListener('shown.bs.modal', function() {
      if (input) { input.value = ''; input.focus(); }
      selectedServices.clear();
      renderSelectedServices();
      nextPageUrl = null;
      updateLoadMoreVisibility();
      doSearch(false);
    });

    modal.addEventListener('hidden.bs.modal', function() {
      selectedServices.clear();
      if (results) results.innerHTML = '<div class="text-muted small p-3 text-center">Start typing to search for services...</div>';
      renderSelectedServices();
    });
  })();
</script>
@endpush
