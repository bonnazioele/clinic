@extends('layouts.app')

@section('title', 'Add Service')

@section('content')
@php
  $user = auth()->user();

  $secretaryClinicRouteValue = request()->route('clinic');
  $secretaryClinicId = null;

  if (is_object($secretaryClinicRouteValue) && isset($secretaryClinicRouteValue->id)) {
      $secretaryClinicId = $secretaryClinicRouteValue->id;
  }

  if (!$secretaryClinicId && is_numeric($secretaryClinicRouteValue)) {
      $secretaryClinicId = $secretaryClinicRouteValue;
  }

  if (!$secretaryClinicId && isset($clinic) && isset($clinic->id)) {
      $secretaryClinicId = $clinic->id;
  }

  if (!$secretaryClinicId && session('active_clinic_id')) {
      $secretaryClinicId = session('active_clinic_id');
  }

  if (!$secretaryClinicId && isset($user->clinic_id)) {
      $secretaryClinicId = $user->clinic_id;
  }

  if (!$secretaryClinicId && isset($user->clinics) && $user->clinics->count()) {
      $secretaryClinicId = $user->clinics->first()->id;
  }

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) {
          return url($fallback);
      }

      $params = (array) $params;

      if ($secretaryClinicId && !array_key_exists('clinic', $params)) {
          $params = array_merge(['clinic' => $secretaryClinicId], $params);
      }

      try {
          return route($routeName, $params);
      } catch (\Throwable $error) {
          return url($fallback);
      }
  };

  $storeUrl = $secUrl('secretary.services.store');
  $attachUrl = $secUrl('secretary.services.attach');
  $backUrl = $secUrl('secretary.services.index');

  $masterServices = isset($availableServices)
      ? collect($availableServices)->filter(fn ($service) => is_object($service))->values()
      : collect();
@endphp

<style>
  .service-add-page {
    width: min(1180px, 96%);
    margin: 0 auto;
    padding: 0.5rem 0 1.75rem;
  }

  .service-add-hero {
    border-radius: 28px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      radial-gradient(circle at 18% 92%, rgba(125,211,252,.22), transparent 24%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
  }

  .service-add-hero::after {
    content: "";
    position: absolute;
    right: -55px;
    bottom: -65px;
    width: 180px;
    height: 180px;
    border-radius: 55px;
    background: rgba(255,255,255,0.12);
    transform: rotate(12deg);
  }

  .service-add-hero-row {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .service-add-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.9rem;
  }

  .service-add-icon {
    width: 62px;
    height: 62px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 1.7rem;
    flex: 0 0 62px;
  }

  .service-add-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.2rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .service-add-subtitle {
    margin: 0.35rem 0 0;
    font-size: 0.95rem;
    font-weight: 650;
    opacity: 0.94;
  }

  .service-add-hero .btn {
    border-radius: 15px;
    font-weight: 900;
    min-height: 44px;
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .flow-card,
  .masterlist-card,
  .new-service-card {
    border-radius: 26px;
    border: 1px solid rgba(226,232,240,.95);
    background: rgba(255,255,255,.96);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .flow-card {
    margin-bottom: 1rem;
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .flow-step {
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e8eef8;
    padding: 0.85rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
  }

  .flow-step-badge {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    color: #0d6efd;
    background: #eaf3ff;
    font-weight: 900;
    flex: 0 0 34px;
  }

  .flow-step-title {
    margin: 0;
    color: #0f172a;
    font-weight: 900;
    font-size: 0.92rem;
  }

  .flow-step-text {
    margin: 0.18rem 0 0;
    color: #64748b;
    font-weight: 650;
    font-size: 0.78rem;
    line-height: 1.35;
  }

  .section-head {
    padding: 1rem 1.15rem;
    border-bottom: 1px solid #edf2f7;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .section-title {
    margin: 0;
    font-size: 1.04rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .section-title i {
    color: #0d6efd;
  }

  .section-note {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 700;
  }

  .masterlist-body,
  .new-service-body {
    padding: 1.15rem;
  }

  .master-search-wrap {
    position: relative;
    margin-bottom: 1rem;
  }

  .master-search-wrap i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #0d6efd;
    font-size: 1rem;
  }

  .master-search {
    min-height: 48px;
    border-radius: 17px !important;
    padding-left: 2.75rem !important;
    border: 1px solid #dbe3ef !important;
    font-weight: 750;
    box-shadow: none !important;
  }

  .master-search:focus,
  .form-control:focus {
    border-color: rgba(13,110,253,.55) !important;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.1) !important;
  }

  .masterlist-services {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 0.85rem;
    max-height: 430px;
    overflow: auto;
    padding-right: 0.2rem;
  }

  .master-service-item {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #ffffff;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    min-height: 172px;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }

  .master-service-item:hover {
    transform: translateY(-2px);
    border-color: rgba(13,110,253,.28);
    box-shadow: 0 14px 28px rgba(15,23,42,.09);
  }

  .master-service-top {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    margin-bottom: 0.75rem;
  }

  .master-service-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    color: #0d6efd;
    background: #eaf3ff;
    flex: 0 0 42px;
    font-size: 1.05rem;
  }

  .master-service-name {
    color: #0f172a;
    font-size: 0.98rem;
    font-weight: 900;
    line-height: 1.25;
  }

  .master-service-desc {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 650;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .master-service-form {
    margin-top: auto;
    padding-top: 0.9rem;
  }

  .master-service-form .btn {
    width: 100%;
    min-height: 40px;
    border-radius: 14px;
    font-weight: 900;
  }

  .masterlist-empty,
  .masterlist-search-prompt {
    border: 1px dashed #cbd5e1;
    border-radius: 20px;
    padding: 1.25rem;
    background: #f8fafc;
    color: #64748b;
    font-weight: 750;
    text-align: center;
  }

  .masterlist-search-prompt i {
    color: #0d6efd;
    font-size: 1.25rem;
    display: block;
    margin-bottom: 0.35rem;
  }

  .new-service-card {
    margin-top: 1rem;
  }

  .new-service-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 0.45fr);
    gap: 1rem;
    align-items: start;
  }

  .form-label {
    font-size: 0.82rem;
    font-weight: 850;
    color: #334155;
  }

  .form-control {
    border-radius: 15px !important;
    border-color: #dbe3ef !important;
    font-weight: 650;
    box-shadow: none !important;
  }

  .field-help {
    margin-top: 0.4rem;
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 650;
    line-height: 1.4;
  }

  .create-warning {
    border-radius: 20px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    padding: 1rem;
    color: #9a3412;
    font-size: 0.83rem;
    font-weight: 750;
    line-height: 1.45;
  }

  .create-warning i {
    color: #f97316;
  }

  .new-service-actions {
    padding: 1rem 1.15rem;
    border-top: 1px solid #edf2f7;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    flex-wrap: wrap;
  }

  .new-service-actions .btn {
    border-radius: 14px;
    font-weight: 900;
    min-height: 42px;
  }

  @media (max-width: 980px) {
    .flow-card,
    .new-service-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 640px) {
    .service-add-page {
      width: 100%;
    }

    .service-add-hero,
    .flow-card,
    .masterlist-card,
    .new-service-card {
      border-radius: 22px;
    }

    .service-add-title-wrap {
      align-items: flex-start;
    }

    .masterlist-services {
      grid-template-columns: 1fr;
      max-height: none;
    }

    .new-service-actions .btn,
    .service-add-hero .btn {
      width: 100%;
    }
  }
</style>

<div class="service-add-page">
  @include('partials.alerts')

  <section class="service-add-hero">
    <div class="service-add-hero-row">
      <div class="service-add-title-wrap">
        <div class="service-add-icon">
          <i class="bi bi-plus-circle"></i>
        </div>

        <div>
          <h1 class="service-add-title">Add Service</h1>
          <p class="service-add-subtitle">
            Search the masterlist first. Create a new service only when it does not exist yet.
          </p>
        </div>
      </div>

      <a href="{{ $backUrl }}" class="btn btn-light text-primary">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Services
      </a>
    </div>
  </section>

  <section class="flow-card" aria-label="Service adding flow">
    <div class="flow-step">
      <div class="flow-step-badge">1</div>
      <div>
        <p class="flow-step-title">Search masterlist</p>
        <p class="flow-step-text">Check if the service already exists in the system.</p>
      </div>
    </div>

    <div class="flow-step">
      <div class="flow-step-badge">2</div>
      <div>
        <p class="flow-step-title">Attach if found</p>
        <p class="flow-step-text">Reuse the existing service to avoid duplicate records.</p>
      </div>
    </div>

    <div class="flow-step">
      <div class="flow-step-badge">3</div>
      <div>
        <p class="flow-step-title">Create only if missing</p>
        <p class="flow-step-text">Doctors can configure their own service duration later.</p>
      </div>
    </div>
  </section>

  <section class="masterlist-card">
    <div class="section-head">
      <h2 class="section-title">
        <i class="bi bi-search"></i>
        Masterlist Services
      </h2>
      <div class="section-note">Available services not yet attached to {{ $clinic->name ?? 'this clinic' }}</div>
    </div>

    <div class="masterlist-body">
      <div class="master-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text"
               id="masterServiceSearch"
               class="form-control master-search"
               placeholder="Search existing services like Dental, Consultation, X-Ray...">
      </div>

      @if($masterServices->isNotEmpty())
        <div class="masterlist-search-prompt" id="masterServicePrompt">
          <i class="bi bi-search"></i>
          Start typing to search the masterlist. Services will only appear after searching.
        </div>

        <div class="masterlist-services d-none" id="masterServiceList">
          @foreach($masterServices as $masterService)
            <article class="master-service-item"
                     data-service-name="{{ strtolower($masterService->name ?? '') }}"
                     data-service-description="{{ strtolower($masterService->description ?? '') }}">
              <div class="master-service-top">
                <div class="master-service-icon">
                  <i class="bi bi-clipboard2-pulse"></i>
                </div>

                <div>
                  <div class="master-service-name">{{ $masterService->name ?? 'Unnamed Service' }}</div>
                  <div class="master-service-desc">
                    {{ $masterService->description ?: 'No description provided for this service yet.' }}
                  </div>
                </div>
              </div>

              <form method="POST" action="{{ $attachUrl }}" class="master-service-form">
                @csrf
                <input type="hidden" name="service_ids[]" value="{{ $masterService->id }}">

                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-link-45deg me-1"></i>
                  Attach to Clinic
                </button>
              </form>
            </article>
          @endforeach
        </div>

        <div class="masterlist-empty d-none" id="masterServiceNoResult">
          No matching service found in the masterlist. Create it below as a new service.
        </div>
      @else
        <div class="masterlist-empty">
          No available masterlist services found. Create a new service below.
        </div>
      @endif
    </div>
  </section>

  <section class="new-service-card">
    <div class="section-head">
      <h2 class="section-title">
        <i class="bi bi-plus-circle"></i>
        Create New Master Service
      </h2>
      <div class="section-note">Use this only when the service is not in the masterlist</div>
    </div>

    <form method="POST" action="{{ $storeUrl }}">
      @csrf

      <div class="new-service-body">
        <div class="new-service-grid">
          <div>
            <div class="mb-3">
              <label class="form-label">Service Name</label>
              <input type="text"
                     name="name"
                     value="{{ old('name') }}"
                     class="form-control @error('name') is-invalid @enderror"
                     placeholder="e.g. Dental Checkup, Consultation, X-Ray"
                     required>

              <div class="field-help">
                The controller still checks duplicates, so existing names will be reused instead of duplicated.
              </div>

              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-0">
              <label class="form-label">Description</label>
              <textarea name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="4"
                        placeholder="Briefly describe what this service includes...">{{ old('description') }}</textarea>

              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <aside class="create-warning">
            <div class="d-flex gap-2 align-items-start">
              <i class="bi bi-info-circle-fill mt-1"></i>
              <div>
                <strong>Reminder:</strong><br>
                Do not create another service if it already exists in the masterlist. Attach the existing one instead.
              </div>
            </div>
          </aside>
        </div>
      </div>

      <div class="new-service-actions">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
          Cancel
        </a>

        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i>
          Create New Service
        </button>
      </div>
    </form>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('masterServiceSearch');
    const serviceList = document.getElementById('masterServiceList');
    const prompt = document.getElementById('masterServicePrompt');
    const noResult = document.getElementById('masterServiceNoResult');
    const serviceItems = Array.from(document.querySelectorAll('.master-service-item'));

    if (!searchInput || !serviceList || serviceItems.length === 0) {
      return;
    }

    function filterMasterlist() {
      const term = searchInput.value.trim().toLowerCase();
      let visibleCount = 0;

      if (term === '') {
        serviceList.classList.add('d-none');

        serviceItems.forEach(function (item) {
          item.classList.add('d-none');
        });

        if (prompt) {
          prompt.classList.remove('d-none');
        }

        if (noResult) {
          noResult.classList.add('d-none');
        }

        return;
      }

      if (prompt) {
        prompt.classList.add('d-none');
      }

      serviceItems.forEach(function (item) {
        const name = item.dataset.serviceName || '';
        const description = item.dataset.serviceDescription || '';
        const isVisible = name.includes(term) || description.includes(term);

        item.classList.toggle('d-none', !isVisible);

        if (isVisible) {
          visibleCount++;
        }
      });

      serviceList.classList.toggle('d-none', visibleCount === 0);

      if (noResult) {
        noResult.classList.toggle('d-none', visibleCount !== 0);
      }
    }

    filterMasterlist();
    searchInput.addEventListener('input', filterMasterlist);
  });
</script>
@endpush
