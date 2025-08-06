@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mt-4 mb-0">Admin Dashboard</h1>
        <div>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-1"></i> Quick Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.clinics.create') }}">
                        <i class="fas fa-hospital me-2"></i> Add Clinic
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.services.create') }}">
                        <i class="fas fa-stethoscope me-2"></i> Add Service
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.clinic-types.create') }}">
                        <i class="fas fa-tags me-2"></i> Add Clinic Type
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-user-shield me-2"></i> Add System Admin
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clinics</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClinics }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hospital fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Approved Clinics</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedClinics }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approvals</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingClinics }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Rejected Clinics</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rejectedClinics }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinic Registrations Section -->
    <div class="card -sm mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 font-weight-bold text-primary">
                    Clinic Registrations
                </h5>
                <small class="text-muted">Showing {{ $clinics->firstItem() ?? 0 }} to {{ $clinics->lastItem() ?? 0 }} of {{ $clinics->total() ?? 0 }} clinics</small>
            </div>
            <!-- Search and Filter Controls -->
            <div class="d-flex align-items-center gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="searchClinics" 
                           placeholder="Search clinic name or email">
                </div>
                <select class="form-select" id="filterByType" style="width: 150px;">
                    <option value="">All Types</option>
                    @foreach($clinicTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="clinicsTable">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Clinic Details</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clinics as $clinic)
                        <tr id="clinic-row-{{ $clinic->id }}">
                            <td>
                                @if($clinic->logo)
                                <img src="{{ asset('storage/' . $clinic->logo) }}" 
                                     alt="Logo" 
                                     class="img-thumbnail" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $clinic->name }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    @if($clinic->user)
                                        <div><i class="fas fa-user text-muted me-1"></i>{{ $clinic->user->name }}</div>
                                        <div><i class="fas fa-envelope text-muted me-1"></i>{{ $clinic->user->email }}</div>
                                    @else
                                    @if($clinic->email)
                                        <div><i class="fas fa-envelope text-muted me-1"></i>{{ $clinic->email }}</div>
                                    @endif
                                    @endif
                                    @if($clinic->contact_number)
                                        <div><i class="fas fa-phone text-muted me-1"></i>{{ $clinic->contact_number }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($clinic->type)
                                    <span class="badge bg-info">{{ $clinic->type->type_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>
                            <td class="small">
                                <div>{{ $clinic->created_at->format('M d, Y') }}</div>
                                <div class="text-muted">{{ $clinic->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success approve-btn" data-id="{{ $clinic->id }}" data-name="{{ $clinic->name }}">
                                    <i class="fas fa-check me-1"></i> Approve
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No pending clinics found</h5>
                                <p class="text-muted">There are currently no pending clinic registrations to display.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clinics->hasPages())
            <div class="mt-3">
                {{ $clinics->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Approvals</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentApprovals as $approval)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $approval->name }}</h6>
                                <small class="text-muted">{{ $approval->approved_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">Branch: {{ $approval->branch_code }}</p>
                            <small class="text-muted">Approved by: {{ $approval->approver->name ?? 'System' }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Rejections</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentRejections as $rejection)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $rejection->name }}</h6>
                                <small class="text-muted">{{ $rejection->rejected_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">Reason: {{ Str::limit($rejection->rejection_reason, 50) }}</p>
                            <small class="text-muted">Rejected by: {{ $rejection->rejector->name ?? 'System' }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">Approve Clinic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve <strong id="approveClinicName"></strong>?</p>
                <div id="approveError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

@section('styles')
<style>
    .avatar {
        font-weight: bold;
        text-transform: uppercase;
    }
    
    /* Card styling */
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        border: none;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    /* Table styling */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 700;
        font-size: 0.875rem;
        color: #2c3e50;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem 0.75rem;
    }
    
    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }
    
    /* Striped rows */
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* Hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.08) !important;
        transition: background-color 0.2s ease;
    }
    
    /* Badge styling */
    .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.4rem 0.8rem;
        border-radius: 0.375rem;
    }
    
    /* Action buttons styling */
    .btn-sm {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
        min-width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-success:hover {
        background-color: #28a745;
        border-color: #28a745;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    .btn-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 86, 179, 0.3);
    }
    
    /* Search and filter styling */
    .input-group .form-control {
        border-radius: 0.375rem;
    }
    
    .input-group .input-group-text {
        border-radius: 0.375rem 0 0 0.375rem;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .form-select {
        border-radius: 0.375rem;
        border-color: #dee2e6;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .card-footer {
        background-color: #f8f9fc;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 0 0 0.5rem 0.5rem;
    }
    
    /* Modal styling */
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .modal-footer {
        border-top: none;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1rem;
    }
    
    /* Empty state styling */
    .table tbody tr td .text-muted i {
        color: #6c757d !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body .row {
            flex-direction: column;
        }
        
        .card-body .row .col-md-3,
        .card-body .row .col-md-2,
        .card-body .row .col-md-5 {
            margin-bottom: 0.5rem;
        }
        
        .table th, .table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    }
    
    /* Custom checkbox and radio styling for accessibility */
    .btn-check:focus + .btn {
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
    
    /* Loading states */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Tooltip improvements */
    .tooltip {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection