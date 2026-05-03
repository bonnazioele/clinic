@extends('layouts.app')

@section('content')
<style>
  .clinic-edit-shell {
    max-width: 1180px;
    margin: 0 auto;
  }

  .clinic-hero-card {
    position: relative;
    overflow: hidden;
    border: 0;
    border-radius: 28px;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.18), transparent 36%),
      linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eef6ff 100%);
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
  }

  .clinic-hero-card::before {
    content: "";
    position: absolute;
    inset: auto -90px -120px auto;
    width: 260px;
    height: 260px;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.1);
  }

  .clinic-hero-content {
    position: relative;
    z-index: 1;
  }

  .clinic-icon-wrap {
    width: 58px;
    height: 58px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #4dabf7);
    color: #fff;
    box-shadow: 0 14px 30px rgba(13, 110, 253, 0.28);
    flex-shrink: 0;
  }

  .clinic-title {
    color: #0f172a;
    letter-spacing: -0.03em;
  }

  .clinic-subtitle {
    color: #64748b;
  }

  .clinic-back-btn {
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: rgba(255, 255, 255, 0.84);
    color: #334155;
    border-radius: 16px;
    padding: 0.72rem 1rem;
    font-weight: 700;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    transition: all 0.2s ease;
  }

  .clinic-back-btn:hover {
    transform: translateY(-1px);
    background: #fff;
    color: #0d6efd;
    border-color: rgba(13, 110, 253, 0.28);
  }

  .clinic-form-card {
    border: 0;
    border-radius: 28px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
  }

  .clinic-form-header {
    padding: 1.35rem 1.5rem;
    border-bottom: 1px solid #eef2f7;
    background: linear-gradient(180deg, #ffffff, #fbfdff);
  }

  .clinic-form-body {
    padding: 1.5rem;
  }

  .section-card {
    border: 1px solid #eef2f7;
    border-radius: 24px;
    background: #ffffff;
    padding: 1.25rem;
    height: 100%;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.04);
  }

  .section-title {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1rem;
    font-weight: 800;
    color: #0f172a;
  }

  .section-title-icon {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: #0d6efd;
  }

  .form-label {
    color: #334155;
    margin-bottom: 0.45rem;
  }

  .clinic-input,
  .clinic-select,
  .clinic-textarea {
    border-radius: 16px;
    border: 1px solid #dbe3ef;
    background-color: #f8fafc;
    padding: 0.78rem 0.95rem;
    color: #0f172a;
    transition: all 0.2s ease;
  }

  .clinic-input:focus,
  .clinic-select:focus,
  .clinic-textarea:focus {
    border-color: rgba(13, 110, 253, 0.55);
    background-color: #fff;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.11);
  }

  .queue-info-box {
    border-radius: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 1rem;
    color: #475569;
  }

  .queue-mode-item {
    display: flex;
    gap: 0.75rem;
  }

  .queue-mode-number {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    background: #0d6efd;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 0.1rem;
  }

  .upload-box {
    border: 1.5px dashed #cbd5e1;
    background: #f8fafc;
    border-radius: 22px;
    padding: 1rem;
    transition: all 0.2s ease;
  }

  .upload-box:hover {
    border-color: rgba(13, 110, 253, 0.55);
    background: #f4f9ff;
  }

  .preview-image-wrap {
    margin-top: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.65rem;
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid #eef2f7;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
  }

  .preview-image {
    width: 76px;
    height: 58px;
    object-fit: cover;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
  }

  .preview-label {
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 700;
  }

  .clinic-form-footer {
    padding: 1.2rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #eef2f7;
  }

  .save-btn {
    border: 0;
    border-radius: 16px;
    padding: 0.78rem 1.2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #0d6efd, #2563eb);
    box-shadow: 0 14px 28px rgba(13, 110, 253, 0.24);
    transition: all 0.2s ease;
  }

  .save-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 18px 34px rgba(13, 110, 253, 0.3);
  }

  .soft-note {
    color: #64748b;
    font-size: 0.92rem;
  }

  @media (max-width: 767.98px) {
    .clinic-hero-card,
    .clinic-form-card {
      border-radius: 22px;
    }

    .clinic-form-body {
      padding: 1rem;
    }

    .section-card {
      padding: 1rem;
      border-radius: 20px;
    }

    .clinic-hero-actions {
      width: 100%;
      margin-top: 1rem;
    }

    .clinic-back-btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="container py-4">
  <div class="clinic-edit-shell">
    @include('partials.alerts')

    <div class="clinic-hero-card p-4 mb-4">
      <div class="clinic-hero-content d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="clinic-icon-wrap">
            <i class="bi bi-building fs-4"></i>
          </div>

          <div>
            <h2 class="clinic-title fw-bold mb-1">Edit Clinic Profile</h2>
            <p class="clinic-subtitle mb-0">
              Manage clinic details, queue strategy, logo, and cover image.
            </p>
            <div class="mt-2 small text-primary fw-semibold">
              <i class="bi bi-hospital me-1"></i>{{ $clinic->name }}
            </div>
          </div>
        </div>

        <div class="clinic-hero-actions">
          <a href="{{ route('secretary.appointments.index') }}" class="btn clinic-back-btn d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i>Back
          </a>
        </div>
      </div>
    </div>

    <form method="POST" action="{{ route('secretary.clinic.update', $clinic) }}" enctype="multipart/form-data" class="clinic-form-card">
      @csrf
      @method('PUT')

      <div class="clinic-form-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h5 class="fw-bold mb-1 text-dark">Clinic Information</h5>
            <p class="soft-note mb-0">Keep this profile updated so patients see the correct clinic details.</p>
          </div>
          <span class="badge rounded-pill text-bg-primary px-3 py-2">
            <i class="bi bi-pencil-square me-1"></i>Edit Mode
          </span>
        </div>
      </div>

      <div class="clinic-form-body">
        <div class="row g-4">
          <div class="col-lg-7">
            <div class="section-card">
              <div class="section-title">
                <span class="section-title-icon">
                  <i class="bi bi-info-circle"></i>
                </span>
                Basic Details
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Clinic Name</label>
                  <input
                    type="text"
                    name="name"
                    class="form-control clinic-input"
                    value="{{ old('name', $clinic->name) }}"
                    placeholder="Enter clinic name"
                    required
                  >
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Address</label>
                  <input
                    type="text"
                    name="address"
                    class="form-control clinic-input"
                    value="{{ old('address', $clinic->address) }}"
                    placeholder="Enter clinic address"
                    required
                  >
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Description</label>
                  <textarea
                    name="description"
                    rows="5"
                    class="form-control clinic-textarea"
                    placeholder="Describe your clinic..."
                  >{{ old('description', $clinic->description) }}</textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="section-card">
              <div class="section-title">
                <span class="section-title-icon">
                  <i class="bi bi-diagram-3"></i>
                </span>
                Queue Strategy
              </div>

              <label class="form-label fw-semibold">
                Queue Mode <span class="text-danger">*</span>
              </label>

              <select name="queue_mode" class="form-select clinic-select" required>
                <option value="fcfs" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'fcfs' ? 'selected' : '' }}>
                  First-Come, First-Served (FCFS)
                </option>
                <option value="priority" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'priority' ? 'selected' : '' }}>
                  Priority-Aware Queue
                </option>
              </select>

              <div class="queue-info-box mt-3">
                <div class="queue-mode-item mb-3">
                  <span class="queue-mode-number">1</span>
                  <div>
                    <strong class="text-dark">First-Come, First-Served</strong>
                    <div class="small mt-1">
                      Patients are served strictly by arrival order. Priority flags are ignored.
                    </div>
                  </div>
                </div>

                <div class="queue-mode-item">
                  <span class="queue-mode-number">2</span>
                  <div>
                    <strong class="text-dark">Priority-Aware Queue</strong>
                    <div class="small mt-1">
                      Senior, PWD, pregnant, and emergency patients can be moved earlier in the queue.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="section-card">
              <div class="section-title">
                <span class="section-title-icon">
                  <i class="bi bi-image"></i>
                </span>
                Clinic Logo
              </div>

              <div class="upload-box">
                <label class="form-label fw-semibold">Upload Logo</label>
                <input type="file" name="logo" class="form-control clinic-input" accept="image/*">
                <div class="small text-muted mt-2">
                  Recommended: square image for cleaner display.
                </div>

                @if($clinic->logo)
                  <div class="preview-image-wrap">
                    <img src="{{ asset('storage/'.$clinic->logo) }}" alt="Logo" class="preview-image">
                    <div>
                      <div class="preview-label">Current Logo</div>
                      <div class="small text-muted">Uploading a new image will replace this.</div>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="section-card">
              <div class="section-title">
                <span class="section-title-icon">
                  <i class="bi bi-card-image"></i>
                </span>
                Cover Image
              </div>

              <div class="upload-box">
                <label class="form-label fw-semibold">Upload Cover Image</label>
                <input type="file" name="cover_image" class="form-control clinic-input" accept="image/*">
                <div class="small text-muted mt-2">
                  Recommended: wide image for the clinic banner.
                </div>

                @if($clinic->cover_image)
                  <div class="preview-image-wrap">
                    <img src="{{ asset('storage/'.$clinic->cover_image) }}" alt="Cover" class="preview-image">
                    <div>
                      <div class="preview-label">Current Cover</div>
                      <div class="small text-muted">Uploading a new image will replace this.</div>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="clinic-form-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="soft-note">
          <i class="bi bi-shield-check me-1 text-primary"></i>
          Changes will update this clinic profile immediately.
        </div>

        <button class="btn btn-primary save-btn">
          <i class="bi bi-save me-2"></i>Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
@endsection