@extends('admin.layouts.app')
@section('title', 'Clinic Application Details')

@push('styles')
<style>
.application-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.status-badge.pending {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
}

.info-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.action-buttons .btn {
    min-width: 120px;
}

.action-buttons .btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.decline-reason-section {
    display: none;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.alert.alert-success {
    background-color: #d1fae5;
    border-color: #a7f3d0;
    color: #065f46;
}

.alert.alert-danger {
    background-color: #fee2e2;
    border-color: #fecaca;
    color: #991b1b;
}

/* Professional Modal Styling */
.modal-content {
    border-radius: 12px;
    backdrop-filter: blur(8px);
}

.modal-backdrop {
    backdrop-filter: blur(2px);
}

#approveModal .modal-content {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

#approveModal .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

#approveModal .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

#approveModal .alert {
    border-radius: 8px;
    background-color: #f8f9fa;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Clinic Application</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-primary mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Clinic Application Details
            </h2>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Application Details -->
        <div class="col-lg-8">
            <div class="application-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0">{{ $clinic->name }}</h4>
                    <span class="badge status-badge 
                        @if($clinic->isApprovedLike()) 
                            bg-success
                        @elseif(strtolower($clinic->status) === 'rejected') 
                            bg-danger
                        @else 
                            pending
                        @endif px-3 py-2">
                        @if($clinic->isApprovedLike())
                            <i class="bi bi-check-circle me-1"></i>{{ ucfirst($clinic->status) }}
                        @elseif(strtolower($clinic->status) === 'rejected')
                            <i class="bi bi-x-circle me-1"></i>{{ ucfirst($clinic->status) }}
                        @else
                            <i class="bi bi-clock me-1"></i>{{ ucfirst($clinic->status) }}
                        @endif
                    </span>
                </div>

                <!-- Basic Information -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="info-card p-3">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-building me-1"></i>Clinic Information
                            </h6>
                            <div class="mb-2">
                                <strong>Name:</strong> {{ $clinic->name }}
                            </div>
                            @if($clinic->branch_code)
                            <div class="mb-2">
                                <strong>Branch Code:</strong> {{ $clinic->branch_code }}
                            </div>
                            @endif
                            <div class="mb-2">
                                <strong>Email:</strong> 
                                <a href="mailto:{{ $clinic->email }}" class="text-decoration-none">{{ $clinic->email }}</a>
                            </div>
                            @if($clinic->description)
                            <div class="mb-2">
                                <strong>Description:</strong>
                                <p class="mb-0 text-muted">{{ $clinic->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card p-3">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-person me-1"></i>Contact Person
                            </h6>
                            @if($clinic->contact_first_name || $clinic->contact_last_name)
                            <div class="mb-2">
                                <strong>Name:</strong> {{ trim(($clinic->contact_first_name ?? '') . ' ' . ($clinic->contact_last_name ?? '')) }}
                            </div>
                            @endif
                            @if($clinic->contact_person_email)
                            <div class="mb-2">
                                <strong>Email:</strong> 
                                <a href="mailto:{{ $clinic->contact_person_email }}" class="text-decoration-none">{{ $clinic->contact_person_email }}</a>
                            </div>
                            @endif
                            @if($clinic->contact_number)
                            <div class="mb-2">
                                <strong>Phone:</strong> 
                                <a href="tel:{{ $clinic->contact_number }}" class="text-decoration-none">{{ $clinic->contact_number }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="info-card p-3 mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-geo-alt me-1"></i>Location Details
                    </h6>
                    <div class="mb-2">
                        <strong>Address:</strong> {{ $clinic->address }}
                    </div>
                    @if($clinic->gps_latitude && $clinic->gps_longitude)
                    <div class="mb-2">
                        <strong>GPS Coordinates:</strong> 
                        {{ $clinic->gps_latitude }}, {{ $clinic->gps_longitude }}
                        <a href="https://maps.google.com?q={{ $clinic->gps_latitude }},{{ $clinic->gps_longitude }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-map me-1"></i>View on Map
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Services -->
                <div class="info-card p-3 mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-gear me-1"></i>Requested Services
                    </h6>
                    @if($clinic->services && $clinic->services->count() > 0)
                        <ul class="mb-0 ps-3">
                            @foreach($clinic->services as $service)
                            <li class="mb-2">
                                <span class="fw-medium">{{ $service->name }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>No services requested with this application
                        </div>
                    @endif
                </div>

                <!-- Application Metadata -->
                <div class="info-card p-3">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-info-circle me-1"></i>Application Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Submitted:</strong> {{ $clinic->created_at->format('M d, Y') }}
                            </div>
                            <div class="mb-2">
                                <strong>Time:</strong> {{ $clinic->created_at->format('h:i A') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Last Updated:</strong> {{ $clinic->updated_at->diffForHumans() }}
                            </div>
                            <div class="mb-2">
                                <strong>Application ID:</strong> #{{ $clinic->id }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4">
            <div class="application-card p-4">
                <h5 class="mb-3">
                    <i class="bi bi-gear me-2"></i>Application Actions
                </h5>

                @if($clinic->isApprovedLike())
                    <!-- Already Approved State -->
                    <div class="alert alert-success d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>
                            <strong>Already Approved</strong>
                            <div class="small">This clinic application has been approved and is active.</div>
                        </div>
                    </div>
                    
                @elseif(strtolower($clinic->status) === 'rejected')
                    <!-- Already Declined State -->
                    <div class="alert alert-danger d-flex align-items-center mb-3">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        <div>
                            <strong>Application Declined</strong>
                            <div class="small">This clinic application has been declined.</div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-danger w-100 action-buttons mb-3" disabled>
                        <i class="bi bi-x-circle me-2"></i>Application Declined
                    </button>
                @else
                    <!-- Pending Actions -->
                    <!-- Approve Button -->
                    <button type="button" class="btn btn-success w-100 action-buttons mb-3" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-circle me-2"></i>Approve Application
                    </button>

                    <!-- Decline Button -->
                    <button type="button" class="btn btn-danger w-100 action-buttons mb-3" id="decline-btn">
                        <i class="bi bi-x-circle me-2"></i>Decline Application
                    </button>

                    <!-- Decline Form (Hidden by default) -->
                    <div class="decline-reason-section" id="decline-section">
                        <form action="{{ route('admin.clinics.decline', $clinic) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="decline_reason" class="form-label fw-semibold">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Reason for Decline
                                </label>
                                <textarea class="form-control @error('decline_reason') is-invalid @enderror" id="decline_reason" name="decline_reason" rows="4" 
                                        placeholder="Please provide a clear reason for declining this application..." required>{{ old('decline_reason') }}</textarea>
                                @error('decline_reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This reason will be sent to the applicant via email.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger flex-fill">
                                    <i class="bi bi-send me-1"></i>Send Decline Notice
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="cancel-decline">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <hr class="my-4">

                <!-- Additional Information -->
                <div class="text-muted">
                    <h6 class="mb-2">
                        <i class="bi bi-info-circle me-1"></i>Review Guidelines
                    </h6>
                    <ul class="small mb-0 ps-3">
                        <li>Verify all contact information is accurate</li>
                        <li>Check if the clinic location and address are valid</li>
                        <li>Review any additional documentation if provided</li>
                    </ul>
                </div>
            </div>

            <!-- Logo Preview -->
            <div class="application-card p-4 mt-4">
                <h6 class="mb-3">
                    <i class="bi bi-image me-2"></i>Clinic Logo
                </h6>
                @if($clinic->logo)
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $clinic->logo) }}" 
                             alt="Clinic Logo" 
                             class="img-fluid rounded"
                             style="max-height: 200px; max-width: 100%;">
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                        <p class="mb-0">No logo provided with this application</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Application Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-semibold text-dark" id="approveModalLabel">
                    <i class="bi bi-check-circle text-success me-2"></i>Approve Application
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="text-center mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-check-circle text-success fs-2"></i>
                    </div>
                    <p class="text-muted fs-6 mb-0">You are about to approve the clinic application for</p>
                    <h6 class="fw-bold text-dark mt-1 mb-0">{{ $clinic->name }}</h6>
                </div>

                <div class="alert alert-light border-start border-4 border-success">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle text-muted me-2 mt-1"></i>
                        <div>
                            <small class="text-muted">
                                An approval email with login credentials will be sent to 
                                <strong>{{ $clinic->contact_person_email ?: $clinic->email }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form action="{{ route('admin.clinics.approve', $clinic) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success px-4" id="confirmApproveBtn" data-loading-text="Approving...">
                            <i class="bi bi-check-lg me-1"></i>Approve Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Decline functionality - only for pending applications
    @if(!$clinic->isApprovedLike() && strtolower($clinic->status) !== 'rejected')
    const declineBtn = document.getElementById('decline-btn');
    const declineSection = document.getElementById('decline-section');
    const cancelDecline = document.getElementById('cancel-decline');

    if (declineBtn && declineSection && cancelDecline) {
        declineBtn.addEventListener('click', function() {
            declineSection.style.display = 'block';
            declineBtn.style.display = 'none';
        });

        cancelDecline.addEventListener('click', function() {
            declineSection.style.display = 'none';
            declineBtn.style.display = 'block';
            document.getElementById('decline_reason').value = '';
        });

        // If validation failed previously, keep the decline form open so the user sees the error
        @if($errors->has('decline_reason') || old('decline_reason'))
            declineSection.style.display = 'block';
            declineBtn.style.display = 'none';
        @endif
    }
    @endif

    // Global form loading system handles the approve button automatically
});
</script>
@endpush