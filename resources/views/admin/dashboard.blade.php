@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">CliniQ Admin Dashboard</h1>
        <div class="d-flex">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addClinicModal">
                <i class="fas fa-plus me-1"></i> Add Clinic
            </button>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-user-plus me-1"></i> Add Admin
            </button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus-circle me-1"></i> Add Service
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
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
            <div class="card border-left-success shadow h-100 py-2">
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
            <div class="card border-left-warning shadow h-100 py-2">
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
            <div class="card border-left-danger shadow h-100 py-2">
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
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Clinic Registrations</h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary filter-btn active" data-status="all">All</button>
                <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-status="Approved">Approved</button>
                <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-status="Pending">Pending</button>
                <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-status="Rejected">Rejected</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="clinicsTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Clinic Name</th>
                            <th>Branch Code</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clinics as $clinic)
                        <tr data-status="{{ $clinic->status }}">
                            <td>{{ $clinic->clinic_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($clinic->logo)
                                    <img src="{{ asset('storage/' . $clinic->logo) }}" class="rounded-circle me-3" width="40" height="40" alt="Clinic Logo">
                                    @else
                                    <div class="avatar me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ substr($clinic->name, 0, 1) }}
                                    </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0">{{ $clinic->name }}</h6>
                                        <small class="text-muted">{{ $clinic->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $clinic->branch_code }}</td>
                            <td>{{ $clinic->type->name ?? 'N/A' }}</td>
                            <td>
                                <small>{{ Str::limit($clinic->address, 30) }}</small>
                                @if($clinic->gps_latitude && $clinic->gps_longitude)
                                <div class="text-primary small">
                                    <i class="fas fa-map-marker-alt"></i> GPS: {{ $clinic->gps_latitude }}, {{ $clinic->gps_longitude }}
                                </div>
                                @endif
                            </td>
                            <td>{{ $clinic->contact_number }}</td>
                            <td>
                                @if($clinic->status == 'Approved')
                                <span class="badge bg-success">{{ $clinic->status }}</span>
                                @elseif($clinic->status == 'Pending')
                                <span class="badge bg-warning text-dark">{{ $clinic->status }}</span>
                                @else
                                <span class="badge bg-danger">{{ $clinic->status }}</span>
                                @endif
                                @if($clinic->status != 'Pending')
                                <div class="small text-muted">
                                    {{ $clinic->status == 'Approved' ? 'Approved on ' . $clinic->approved_at->format('M d, Y') : 'Rejected on ' . $clinic->rejected_at->format('M d, Y') }}
                                </div>
                                @endif
                            </td>
                            <td>{{ $clinic->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex">
                                    @if($clinic->status == 'Pending')
                                    <button class="btn btn-sm btn-success me-1 approve-btn" data-id="{{ $clinic->clinic_id }}" data-bs-toggle="tooltip" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-btn" data-id="{{ $clinic->clinic_id }}" data-bs-toggle="tooltip" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-sm btn-primary me-1 view-btn" data-id="{{ $clinic->clinic_id }}" data-bs-toggle="tooltip" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Approvals</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentApprovals as $approval)
                        <div class="list-group-item list-group-item-action">
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
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Rejections</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentRejections as $rejection)
                        <div class="list-group-item list-group-item-action">
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

<!-- Add Clinic Modal -->
<div class="modal fade" id="addClinicModal" tabindex="-1" aria-labelledby="addClinicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClinicModalLabel">Register New Clinic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clinicRegistrationForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clinicName" class="form-label">Clinic Name</label>
                            <input type="text" class="form-control" id="clinicName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="clinicType" class="form-label">Clinic Type</label>
                            <select class="form-select" id="clinicType" required>
                                <option value="">Select Type</option>
                                @foreach($clinicTypes as $type)
                                <option value="{{ $type->type_id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="branchCode" class="form-label">Branch Code</label>
                            <input type="text" class="form-control" id="branchCode" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contactNumber" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" rows="2" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="logo">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="latitude" class="form-label">GPS Latitude</label>
                            <input type="number" step="0.0000001" class="form-control" id="latitude">
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label">GPS Longitude</label>
                            <input type="number" step="0.0000001" class="form-control" id="longitude">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveClinicBtn">Register Clinic</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Add System Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adminRegistrationForm">
                    <div class="mb-3">
                        <label for="adminName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminName" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="adminPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPasswordConfirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="adminPasswordConfirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAdminBtn">Add Admin</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceRegistrationForm">
                    <div class="mb-3">
                        <label for="serviceName" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="serviceName" required>
                    </div>
                    <div class="mb-3">
                        <label for="serviceDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="serviceDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="serviceCategory" class="form-label">Category</label>
                        <input type="text" class="form-control" id="serviceCategory">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Active Service</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveServiceBtn">Add Service</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectionModalLabel">Reject Clinic Registration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectionForm">
                    <input type="hidden" id="rejectClinicId">
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="rejectionReason" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Filter clinics by status
        $('.filter-btn').click(function() {
            const status = $(this).data('status');
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            if (status === 'all') {
                $('#clinicsTable tbody tr').show();
            } else {
                $('#clinicsTable tbody tr').hide();
                $(`#clinicsTable tbody tr[data-status="${status}"]`).show();
            }
        });

        // Approve clinic
        $('.approve-btn').click(function() {
            const clinicId = $(this).data('id');
            if (confirm('Are you sure you want to approve this clinic?')) {
                // AJAX call to approve clinic
                alert(`Clinic ${clinicId} approved successfully!`);
                // In a real app, you would reload or update the row
            }
        });

        // Reject clinic - open modal
        $('.reject-btn').click(function() {
            const clinicId = $(this).data('id');
            $('#rejectClinicId').val(clinicId);
            $('#rejectionModal').modal('show');
        });

        // Confirm rejection
        $('#confirmRejectBtn').click(function() {
            const clinicId = $('#rejectClinicId').val();
            const reason = $('#rejectionReason').val();
            
            if (!reason) {
                alert('Please provide a rejection reason');
                return;
            }
            
            // AJAX call to reject clinic
            alert(`Clinic ${clinicId} rejected with reason: ${reason}`);
            $('#rejectionModal').modal('hide');
            // In a real app, you would reload or update the row
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endsection

@section('styles')
<style>
    .avatar {
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .filter-btn.active {
        font-weight: bold;
    }
    
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #f8f9fc;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #4e73df;
    }
    
    .badge {
        font-size: 0.75em;
        font-weight: 600;
        padding: 0.35em 0.65em;
    }
    
    #clinicsTable tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
        cursor: pointer;
    }
    
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .modal-footer {
        border-top: none;
    }
</style>
@endsection