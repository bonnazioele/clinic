@extends('layouts.secretary')
@section('title','Manage Doctors')

@section('content')
<div class="container-fluid">
  @include('partials.alerts')

  <!-- Page Header -->
  <div class="mb-3">
    <h3 class="card-title mb-0">
      <i class="bi bi-people me-2"></i>
      Doctors ({{ $doctors->total() }} total)
    </h3>
  </div>

  <!-- Controls Bar -->
  <div class="d-flex flex-column flex-lg-row gap-3 mb-4">
    <!-- Filter Dropdown (Far Left) -->
    <select class="form-select" id="serviceFilter" style="min-width: 180px;">
      <option value="">All Services</option>
      @foreach($availableServices ?? [] as $service)
        <option value="{{ $service->id }}" {{ request('service') == $service->id ? 'selected' : '' }}>
          {{ $service->service_name }}
        </option>
      @endforeach
    </select>
    
    <!-- Search Bar (Middle) -->
    <div class="input-group flex-grow-1" style="max-width: 350px;">
      <span class="input-group-text">
        <i class="bi bi-search"></i>
      </span>
      <input type="text" class="form-control" id="doctorSearch" placeholder="Search doctors..." value="{{ request('search') }}">
    </div>
    
    <!-- Add Doctor Button (Far Right) -->
    <a href="{{ route('secretary.doctors.create') }}" class="btn btn-primary whitespace-nowrap">
      <i class="bi bi-person-plus me-2"></i>
      Add Doctor
    </a>
  </div>

  @if($doctors->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="bi bi-people display-1 text-muted mb-3"></i>
        <h5 class="text-muted">No Doctors Found</h5>
        @if(request('search') || request('service'))
          <p class="text-muted mb-3">No doctors match your current search criteria.</p>
          <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-clockwise me-2"></i>
            Clear Filters
          </a>
        @else
          <p class="text-muted mb-3">Start by adding your first doctor to the clinic.</p>
        @endif
        <a href="{{ route('secretary.doctors.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-2"></i>
          Add First Doctor
        </a>
      </div>
    </div>
  @else
    <!-- Doctors Grid (2 Columns) -->
    <div class="row g-3" id="doctorsGrid">
      @foreach($doctors as $doctor)
        <div class="col-12 col-md-6 doctor-card" 
             data-name="{{ strtolower($doctor->name) }}" 
             data-email="{{ strtolower($doctor->email) }}"
             data-services="{{ $doctor->servicesForClinic(session('current_clinic_id'))->pluck('id')->implode(',') }}">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center">
              <!-- Circular Avatar (Far Left) -->
              <div class="doctor-avatar me-3" style="width: 50px; height: 50px; min-width: 50px;">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                     style="width: 100%; height: 100%; font-size: 18px; font-weight: 600;">
                  {{ substr($doctor->first_name, 0, 1) }}{{ substr($doctor->last_name, 0, 1) }}
                </div>
              </div>
              
              <!-- Doctor Info (Center - Flexible) -->
              <div class="flex-grow-1 min-w-0">
                <h6 class="mb-1 fw-bold text-truncate">{{ $doctor->name }}</h6>
                @if($doctor->phone)
                  <p class="text-muted small mb-0">
                    <i class="bi bi-telephone me-1"></i>
                    {{ $doctor->phone }}
                  </p>
                @else
                  <p class="text-muted small mb-0">No phone</p>
                @endif
              </div>
              
              <!-- Profile Button (Far Right) -->
              <a href="{{ route('secretary.doctors.edit', $doctor) }}" class="btn btn-primary btn-sm ms-3">
                <i class="bi bi-person me-1"></i>
                Profile
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- No Results Message (Hidden by default, shown by JavaScript) -->
    <div id="noResultsMessage" class="card border-0 shadow-sm" style="display: none;">
      <div class="card-body text-center py-5">
        <i class="bi bi-search display-1 text-muted mb-3"></i>
        <h5 class="text-muted">No doctors match your search criteria</h5>
        <p class="text-muted mb-3">Try adjusting your search terms or clearing the filters.</p>
        <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
          <i class="bi bi-arrow-clockwise me-2"></i>
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Pagination -->
    @if($doctors->hasPages())
      <div class="d-flex justify-content-center mt-4">
        {{ $doctors->withQueryString()->links() }}
      </div>
    @endif
  @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('doctorSearch');
    const serviceFilter = document.getElementById('serviceFilter');
    const doctorCards = document.querySelectorAll('.doctor-card');
    const doctorsGrid = document.getElementById('doctorsGrid');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    
    function filterDoctors() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedService = serviceFilter.value;
        let visibleCount = 0;
        
        doctorCards.forEach(card => {
            const name = card.dataset.name;
            const email = card.dataset.email;
            const services = card.dataset.services.split(',');
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesService = !selectedService || services.includes(selectedService);
            
            if (matchesSearch && matchesService) {
                card.style.display = 'block';
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.style.display = 'none';
                card.classList.add('d-none');
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0 && (searchTerm || selectedService)) {
            doctorsGrid.style.display = 'none';
            noResultsMessage.style.display = 'block';
        } else {
            doctorsGrid.style.display = 'flex';
            noResultsMessage.style.display = 'none';
        }
        
        // Update URL without page reload
        const url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        if (selectedService) {
            url.searchParams.set('service', selectedService);
        } else {
            url.searchParams.delete('service');
        }
        
        window.history.replaceState({}, '', url);
    }
    
    function clearFilters() {
        searchInput.value = '';
        serviceFilter.value = '';
        filterDoctors();
    }
    
    // Add event listeners
    searchInput.addEventListener('input', filterDoctors);
    serviceFilter.addEventListener('change', filterDoctors);
    clearFiltersBtn.addEventListener('click', clearFilters);
    
    // Clear search on Escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            clearFilters();
        }
    });
});
</script>
@endpush
@endsection
