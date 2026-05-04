@extends('admin.layouts.app')

@section('title', 'Edit Service')

@section('content')
<style>
  .service-form-page {
    width: 100%;
  }

  .service-form-hero {
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
  }

  .service-form-icon {
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

  .service-form-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 2.8rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .service-form-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 1rem;
  }

  .service-form-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
  }

  .service-form-panel-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.13), transparent 30%),
      linear-gradient(180deg, #ffffff, #f8fafc);
  }

  .service-form-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -0.02em;
  }

  .service-form-panel-title i {
    color: #0d6efd;
  }

  .service-form-panel-subtitle {
    color: #64748b;
    font-size: 0.92rem;
    margin: 6px 0 0;
  }

  .service-form-panel-body {
    padding: 24px;
  }

  .service-summary-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #f8fafc;
    padding: 20px;
    height: 100%;
  }

  .summary-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.10);
    color: #0d6efd;
    font-size: 1.45rem;
    margin-bottom: 14px;
  }

  .form-control {
    border-radius: 18px;
    min-height: 52px;
  }

  textarea.form-control {
    min-height: 140px;
  }

  .form-action-btn {
    border-radius: 16px;
    padding: 12px 18px;
    font-weight: 900;
  }

  @media (max-width: 767.98px) {
    .service-form-hero,
    .service-form-panel {
      border-radius: 24px;
    }

    .service-form-panel-header,
    .service-form-panel-body {
      padding: 18px;
    }
  }
</style>

<div class="service-form-page">

  <div class="service-form-hero">
    <div class="d-flex align-items-center gap-3 gap-md-4">
      <div class="service-form-icon">
        <i class="bi bi-pencil-square"></i>
      </div>

      <div>
        <h1 class="service-form-title">Edit Service</h1>
        <p class="service-form-text">
          Update this medical service information.
        </p>
      </div>
    </div>
  </div>

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

  <div class="row g-4">
    <div class="col-xl-8">
      <div class="service-form-panel">
        <div class="service-form-panel-header">
          <h2 class="service-form-panel-title">
            <i class="bi bi-sliders"></i>
            Service Information
          </h2>
          <p class="service-form-panel-subtitle">
            Modify the service name or description.
          </p>
        </div>

        <div class="service-form-panel-body">
          <form method="POST" action="{{ route('admin.services.update', $service) }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
              <label for="name" class="form-label">
                <i class="bi bi-gear me-1"></i>
                Service Name
              </label>

              <input type="text"
                     id="name"
                     name="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name', $service->name) }}"
                     placeholder="Example: Dental Consultation"
                     required>

              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-4">
              <label for="description" class="form-label">
                <i class="bi bi-card-text me-1"></i>
                Description
                <small class="text-muted">(optional)</small>
              </label>

              <textarea id="description"
                        name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="5"
                        placeholder="Briefly describe what this service covers.">{{ old('description', $service->description) }}</textarea>

              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary form-action-btn">
                <i class="bi bi-save me-2"></i>
                Update Service
              </button>

              <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary form-action-btn">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Services
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="service-summary-card">
        <div class="summary-icon">
          <i class="bi bi-clipboard2-pulse"></i>
        </div>

        <h5 class="fw-bold text-dark mb-2">Current Service</h5>
        <p class="text-muted mb-3">
          You are editing the existing service below.
        </p>

        <div class="p-3 rounded-4 bg-white border">
          <div class="small text-muted fw-bold mb-1">Service Name</div>
          <div class="fw-bold text-dark">{{ $service->name }}</div>
        </div>

        <div class="p-3 rounded-4 bg-white border mt-3">
          <div class="small text-muted fw-bold mb-1">Description</div>
          <div class="text-muted">
            {{ $service->description ?: 'No description provided.' }}
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection