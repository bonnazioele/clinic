<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Slip - {{ $visit->patient->full_name }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #f8fafc;
      color: #0f172a;
    }

    .print-shell {
      max-width: 900px;
      margin: 0 auto;
    }

    .print-actions {
      display: flex;
      justify-content: space-between;
      gap: .75rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .print-actions .btn {
      border-radius: 14px;
      font-weight: 800;
    }

    .slip-card {
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      background: #fff;
      overflow: hidden;
      box-shadow: 0 18px 45px rgba(15,23,42,.08);
    }

    .slip-header {
      padding: 1.25rem;
      color: #fff;
      background:
        radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
        linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    }

    .brand-row {
      display: flex;
      align-items: center;
      gap: .85rem;
    }

    .brand-icon {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      background: rgba(255,255,255,.18);
      font-size: 1.45rem;
    }

    .brand-title {
      margin: 0;
      font-weight: 900;
      letter-spacing: -.04em;
    }

    .brand-subtitle {
      margin: .15rem 0 0;
      opacity: .95;
      font-weight: 650;
    }

    .slip-body {
      padding: 1.25rem;
    }

    .slip-number-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
      margin-bottom: 1.25rem;
    }

    .slip-number-card {
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 1rem;
      background: #f8fafc;
    }

    .slip-label {
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .75rem;
      font-weight: 900;
      margin-bottom: .25rem;
    }

    .slip-value {
      color: #0f172a;
      font-weight: 800;
    }

    .slip-big-value {
      color: #0d6efd;
      font-size: 1.5rem;
      line-height: 1.1;
      font-weight: 900;
    }

    .slip-section {
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .slip-section-title {
      margin: 0 0 .85rem;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .78rem;
      font-weight: 900;
    }

    .signature-box {
      border-bottom: 1px solid #6b7280;
      height: 64px;
    }

    @media print {
      .no-print {
        display:none !important;
      }

      body {
        margin: 10mm;
        background: #fff;
      }

      .print-shell {
        max-width: 100%;
      }

      .slip-card {
        box-shadow: none;
        border-radius: 0;
      }
    }

    @media (max-width: 768px) {
      .slip-number-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div class="print-shell py-4">
    <div class="no-print print-actions">
      <a href="{{ route('secretary.walkin.confirmation', $visit->id) }}" class="btn btn-outline-secondary">
        Back
      </a>

      <button class="btn btn-primary" onclick="window.print()">
        Print Slip
      </button>
    </div>

    <div class="slip-card">
      <div class="slip-header">
        <div class="brand-row">
          <div class="brand-icon">
            <span>❤</span>
          </div>
          <div>
            <h3 class="brand-title">CliniQ</h3>
            <p class="brand-subtitle">Walk-In Registration Slip</p>
          </div>
        </div>
      </div>

      <div class="slip-body">
        <div class="slip-number-grid">
          <div class="slip-number-card">
            <div class="slip-label">Visit Number</div>
            <div class="slip-big-value">{{ $visit->visit_number }}</div>
          </div>

          <div class="slip-number-card">
            <div class="slip-label">Patient Number</div>
            <div class="slip-big-value">{{ $visit->patient->patient_number }}</div>
          </div>
        </div>

        <div class="slip-section">
          <h6 class="slip-section-title">Patient Details</h6>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="slip-label">Patient Name</div>
              <div class="slip-value">{{ $visit->patient->full_name }}</div>
            </div>

            <div class="col-md-3">
              <div class="slip-label">Date of Birth</div>
              <div class="slip-value">{{ $visit->patient->date_of_birth->format('F d, Y') }}</div>
            </div>

            <div class="col-md-3">
              <div class="slip-label">Patient Type</div>
              <div class="slip-value">{{ $visit->patient_type }}</div>
            </div>
          </div>
        </div>

        <div class="slip-section">
          <h6 class="slip-section-title">Visit Details</h6>

          <div class="row g-3">
            <div class="col-md-4">
              <div class="slip-label">Contact Number</div>
              <div class="slip-value">{{ $visit->patient->mobile_number }}</div>
            </div>

            <div class="col-md-4">
              <div class="slip-label">Priority Level</div>
              <div class="slip-value">{{ $visit->priority_level }}</div>
            </div>

            <div class="col-md-4">
              <div class="slip-label">Requested Service</div>
              <div class="slip-value">{{ $visit->requested_service }}</div>
            </div>

            @if($visit->assigned_department)
              <div class="col-12">
                <div class="slip-label">Assigned Department</div>
                <div class="slip-value">{{ $visit->assigned_department }}</div>
              </div>
            @endif

            <div class="col-12">
              <div class="slip-label">Reason for Visit / Chief Complaint</div>
              <div class="slip-value">{{ $visit->reason_for_visit }}</div>
            </div>
          </div>
        </div>

        <div class="slip-section">
          <h6 class="slip-section-title">Emergency Contact</h6>

          <div class="row g-3">
            <div class="col-md-4">
              <div class="slip-label">Name</div>
              <div class="slip-value">{{ $visit->patient->emergency_contact_name }}</div>
            </div>

            <div class="col-md-4">
              <div class="slip-label">Relationship</div>
              <div class="slip-value">{{ $visit->patient->emergency_contact_relationship }}</div>
            </div>

            <div class="col-md-4">
              <div class="slip-label">Contact Number</div>
              <div class="slip-value">{{ $visit->patient->emergency_contact_number }}</div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <div class="slip-label">Patient Signature</div>
            <div class="signature-box mb-2">
              @if($visit->patient_signature)
                <img src="{{ $visit->patient_signature }}" alt="Signature" style="max-height:60px;">
              @endif
            </div>
            <div class="text-muted small">
              Date Signed: {{ $visit->date_signed->format('F d, Y') }}
            </div>
          </div>

          <div class="col-md-6">
            <div class="slip-label">Registration Staff</div>
            <div class="slip-value">{{ $visit->registrationStaff->name }}</div>
            <div class="signature-box mt-3"></div>
            <div class="text-muted small">Signature</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>