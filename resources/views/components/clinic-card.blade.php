<div class="card h-100 shadow-sm border-0 rounded-4 clinic-card"
     data-clinic-id="{{ $clinic->id }}"
     title="Click to view {{ $clinic->name }} details">
  <div class="card-body d-flex flex-column p-4">

    {{-- Clinic Header --}}
    <div class="d-flex align-items-start justify-content-between mb-3">
      <div class="flex-grow-1">
        <h5 class="fw-bold text-dark mb-1 clinic-name">{{ $clinic->name }}</h5>
        <div class="d-flex align-items-center text-muted small mb-2">
          <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
          <span>{{ Str::limit($clinic->address, 40) }}</span>
        </div>
      </div>

      {{-- Status Badge --}}
      @if($clinic->opens_at && $clinic->closes_at)
        <span class="badge {{ $clinic->is_open ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2">
          <i class="bi {{ $clinic->is_open ? 'bi-clock' : 'bi-clock-fill' }} me-1"></i>
          {{ $clinic->is_open ? 'Open Now' : 'Closed' }}
        </span>
      @endif
    </div>

    {{-- Distance (optional) --}}
    @if(property_exists($clinic, 'distance'))
      <div class="d-flex align-items-center text-info mb-3">
        <i class="bi bi-signpost-2 me-2"></i>
        <span class="fw-medium">{{ number_format($clinic->distance, 1) }} km away</span>
      </div>
    @endif

    {{-- Rating --}}
    @if($clinic->average_rating)
      <div class="mb-3">
        <div class="d-flex align-items-center mb-1">
          <div class="me-2">
            @for ($i = 1; $i <= 5; $i++)
              @if ($i <= floor($clinic->average_rating))
                <i class="bi bi-star-fill text-warning fs-6"></i>
              @elseif ($i - $clinic->average_rating < 1)
                <i class="bi bi-star-half text-warning fs-6"></i>
              @else
                <i class="bi bi-star text-warning fs-6"></i>
              @endif
            @endfor
          </div>
          <span class="fw-semibold text-dark">{{ number_format($clinic->average_rating, 1) }}</span>
          <span class="text-muted ms-1">/ 5.0</span>
        </div>
      </div>
    @endif

    {{-- Services Offered --}}
    <div class="mb-4">
      <h6 class="text-muted small text-uppercase fw-semibold mb-2">Services</h6>
      @if($clinic->services->count() > 0)
        <div class="d-flex flex-wrap gap-1">
          @foreach($clinic->services->take(3) as $service)
            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
              {{ $service->service_name }}
            </span>
          @endforeach
          @if($clinic->services->count() > 3)
            <span class="badge bg-secondary text-white rounded-pill px-2 py-1 small">
              +{{ $clinic->services->count() - 3 }} more
            </span>
          @endif
        </div>
      @else
        <span class="text-muted small">No services listed</span>
      @endif
    </div>

    {{-- Action Buttons --}}
    <div class="mt-auto d-flex gap-2">
      <a href="{{ route('clinics.show', $clinic) }}"
         class="btn btn-outline-secondary btn-sm flex-grow-1 rounded-pill"
         title="View clinic details">
        <i class="bi bi-eye me-2"></i> View Details
      </a>
      <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}"
         class="btn btn-primary btn-sm flex-grow-1 rounded-pill"
         title="Book an appointment">
        <i class="bi bi-calendar-event me-2"></i> Book Now
      </a>
    </div>

  </div>
</div>
