<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Slip - {{ $visit->patient->full_name }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; }
    .signature-box { border-bottom: 1px solid #6b7280; height: 64px; }
    @media print {
      .no-print { display:none; }
      body { margin: 12mm; }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="no-print mb-3 d-flex justify-content-between">
      <a href="{{ route('secretary.walkin.confirmation', $visit->id) }}" class="btn btn-outline-secondary">Back</a>
      <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-center mb-4">
          <h3 class="mb-0">CliniQ</h3>
          <p class="text-muted">Walk-In Registration Slip</p>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-muted small">Visit Number</div>
              <div class="fs-4 fw-bold text-primary">{{ $visit->visit_number }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-muted small">Patient Number</div>
              <div class="fs-4 fw-bold text-success">{{ $visit->patient->patient_number }}</div>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="fw-semibold">Patient Name</div>
            <div>{{ $visit->patient->full_name }}</div>
          </div>
          <div class="col-md-3">
            <div class="fw-semibold">Date of Birth</div>
            <div>{{ $visit->patient->date_of_birth->format('F d, Y') }}</div>
          </div>
          <div class="col-md-3">
            <div class="fw-semibold">Patient Type</div>
            <div>{{ $visit->patient_type }}</div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="fw-semibold">Contact Number</div>
            <div>{{ $visit->patient->mobile_number }}</div>
          </div>
          <div class="col-md-4">
            <div class="fw-semibold">Priority Level</div>
            <div>{{ $visit->priority_level }}</div>
          </div>
          <div class="col-md-4">
            <div class="fw-semibold">Requested Service</div>
            <div>{{ $visit->requested_service }}</div>
          </div>
        </div>

        @if($visit->assigned_department)
        <div class="mb-3">
          <div class="fw-semibold">Assigned Department</div>
          <div>{{ $visit->assigned_department }}</div>
        </div>
        @endif

        <div class="mb-4">
          <div class="fw-semibold">Reason for Visit / Chief Complaint</div>
          <div>{{ $visit->reason_for_visit }}</div>
        </div>

        <div class="border rounded p-3 mb-4">
          <div class="text-muted text-uppercase small mb-2">Emergency Contact</div>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="fw-semibold">Name</div>
              <div>{{ $visit->patient->emergency_contact_name }}</div>
            </div>
            <div class="col-md-4">
              <div class="fw-semibold">Relationship</div>
              <div>{{ $visit->patient->emergency_contact_relationship }}</div>
            </div>
            <div class="col-md-4">
              <div class="fw-semibold">Contact Number</div>
              <div>{{ $visit->patient->emergency_contact_number }}</div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <div class="fw-semibold">Patient Signature</div>
            <div class="signature-box mb-2">
              @if($visit->patient_signature)
                <img src="{{ $visit->patient_signature }}" alt="Signature" style="max-height:60px;">
              @endif
            </div>
            <div class="text-muted small">Date Signed: {{ $visit->date_signed->format('F d, Y') }}</div>
          </div>
          <div class="col-md-6">
            <div class="fw-semibold">Registration Staff</div>
            <div>{{ $visit->registrationStaff->name }}</div>
            <div class="signature-box mt-3"></div>
            <div class="text-muted small">Signature</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
