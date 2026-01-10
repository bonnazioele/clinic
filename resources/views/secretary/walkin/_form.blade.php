@push('styles')
<style>
  .walkin-section-title {
    font-size: 1rem;
    font-weight: 600;
    border-left: 4px solid #0d6efd;
    padding-left: 0.75rem;
    margin-bottom: 1rem;
    color: #0f172a;
  }

  .signature-pad {
    border: 2px dashed #94a3b8;
    border-radius: 0.5rem;
    width: 100%;
    height: 220px;
  }

</style>
@endpush

@php
  $clinicServices = isset($clinicServices) ? collect($clinicServices) : collect();
  $hasClinicServices = $clinicServices->isNotEmpty();
  $activeClinicName = $activeClinic->name ?? null;
@endphp

<div class="card medical-card shadow-sm mt-4" id="walkInRegistrationModule">
  <div class="card-header bg-primary text-white d-flex align-items-center">
    <i class="bi bi-clipboard-plus me-2"></i>
    <h5 class="mb-0">Walk-In Patient Registration</h5>
  </div>
  <div class="card-body">
    @if(! $hasClinicServices)
      <div class="alert alert-warning border border-warning-subtle">
        <div class="fw-semibold mb-1">No services configured for this clinic yet.</div>
        <div class="small text-muted">Add at least one service under Secretary &raquo; Services so you can tag walk-ins correctly.</div>
        <a class="btn btn-sm btn-outline-warning mt-2" href="{{ route('secretary.services.index') }}">Manage Clinic Services</a>
      </div>
    @endif

    <form id="walkInRegistrationForm">
      @csrf
      <input type="hidden" id="walkin_patient_number" value="">
      <input type="hidden" id="walkin_visit_number" value="">

      <div class="walkin-section-title">Patient Personal Information</div>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Last Name<span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="last_name" id="walkin_last_name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">First Name<span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="first_name" id="walkin_first_name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Middle Name</label>
          <input type="text" class="form-control" name="middle_name" id="walkin_middle_name">
        </div>
        <div class="col-md-3">
          <label class="form-label">Sex<span class="text-danger">*</span></label>
          <select class="form-select" name="sex" id="walkin_sex" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Date of Birth<span class="text-danger">*</span></label>
          <input type="date" class="form-control" name="date_of_birth" id="walkin_date_of_birth" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Age</label>
          <input type="text" class="form-control" id="walkin_age" readonly>
        </div>
      </div>

      <div class="walkin-section-title">Contact Information</div>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Mobile Number<span class="text-danger">*</span></label>
          <input type="tel" class="form-control" name="mobile_number" id="walkin_mobile_number" placeholder="09xxxxxxxxx" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" name="email_address" id="walkin_email_address" placeholder="Optional">
        </div>
        <div class="col-12">
          <label class="form-label">Complete Address<span class="text-danger">*</span></label>
          <textarea class="form-control" name="complete_address" id="walkin_complete_address" rows="2" required></textarea>
        </div>
      </div>

      <div class="walkin-section-title">Visit Information</div>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <label class="form-label">Visit Type<span class="text-danger">*</span></label>
          <select class="form-select" name="visit_type" id="walkin_visit_type" required>
            <option value="Walk-In" selected>Walk-In</option>
            <option value="Follow-Up">Follow-Up</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Requested Service<span class="text-danger">*</span></label>
          <select class="form-select" name="requested_service" id="walkin_requested_service" {{ $hasClinicServices ? '' : 'disabled' }} required>
            <option value="">{{ $hasClinicServices ? 'Select a service' : 'No services available' }}</option>
            @foreach($clinicServices as $service)
              <option value="{{ $service->name }}"
                      data-description="{{ e($service->description ?? '') }}"
                      data-duration="{{ $service->pivot->duration_minutes ?? '' }}"
                      data-department="{{ $service->name }}">
                {{ $service->name }}
              </option>
            @endforeach
          </select>
          @if(! $hasClinicServices)
            <div class="form-text text-danger">Add services under Secretary &raquo; Services to continue.</div>
          @endif
        </div>
        <div class="col-md-3">
          <label class="form-label">Patient Type<span class="text-danger">*</span></label>
          <select class="form-select" name="patient_type" id="walkin_patient_type" required>
            <option value="New" selected>New</option>
            <option value="Returning">Returning</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Priority Level<span class="text-danger">*</span></label>
          <select class="form-select" name="priority_level" id="walkin_priority_level" required>
            <option value="Normal" selected>Normal</option>
            <option value="Urgent">Urgent</option>
            <option value="Emergency">Emergency</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Reason for Visit / Chief Complaint<span class="text-danger">*</span></label>
          <textarea class="form-control" name="reason_for_visit" id="walkin_reason_for_visit" rows="3" required></textarea>
        </div>
      </div>

      <div class="walkin-section-title">Emergency Contact</div>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Contact Name<span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="emergency_contact_name" id="walkin_emergency_contact_name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Relationship<span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="emergency_contact_relationship" id="walkin_emergency_contact_relationship" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Contact Number<span class="text-danger">*</span></label>
          <input type="tel" class="form-control" name="emergency_contact_number" id="walkin_emergency_contact_number" required>
        </div>
      </div>

      <div class="walkin-section-title">Consent & Verification</div>
      <div class="mb-3">
        <div class="alert alert-primary" role="alert">
          By signing below, the patient acknowledges the Data Privacy Act of 2012 (RA 10173) and authorizes CliniQ to collect and process personal and medical information for healthcare delivery.
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="consent_to_data_collection" id="walkin_consent" value="1" required>
          <label class="form-check-label" for="walkin_consent">
            Consent to Data Collection and Medical Processing
          </label>
        </div>
        <div class="mb-3">
          <label class="form-label">Patient Signature</label>
          <canvas id="walkin_signature" class="signature-pad"></canvas>
          <div class="text-end mt-2">
            <button type="button" class="btn btn-outline-danger btn-sm" id="walkin_clear_signature">
              Clear Signature
            </button>
          </div>
          <input type="hidden" name="patient_signature" id="walkin_patient_signature">
        </div>
        <div class="col-md-3">
          <label class="form-label">Date Signed<span class="text-danger">*</span></label>
          <input type="date" class="form-control" name="date_signed" id="walkin_date_signed" value="{{ now()->toDateString() }}" required>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-success" id="walkin_submit_btn">
          <i class="bi bi-check2-circle me-2"></i>Register Walk-In Patient
        </button>
        <button type="button" class="btn btn-outline-secondary" id="walkin_reset_form">
          <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Form
        </button>
        <a href="{{ route('secretary.queue.overview') }}" class="btn btn-outline-dark">
          <i class="bi bi-arrow-left me-2"></i>Back to Queue
        </a>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="walkinSuccessModal" tabindex="-1" aria-labelledby="walkinSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="walkinSuccessModalLabel"><i class="bi bi-check-circle me-2"></i>Registration Successful</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-2">Patient</p>
        <h5 class="fw-semibold mb-4" id="walkinSuccessPatientName">&nbsp;</h5>

        <div class="border rounded-3 p-3 mb-3">
          <div class="text-muted small">Patient ID</div>
          <div class="fs-5 fw-semibold" id="walkinSuccessPatientNumber">&nbsp;</div>
        </div>

        <div class="border rounded-3 p-3 mb-3">
          <div class="text-muted small">Queue Number</div>
          <div class="fs-5 fw-semibold" id="walkinSuccessQueueNumber">&nbsp;</div>
        </div>

        <div class="border rounded-3 p-3 mb-3">
          <div class="text-muted small">Visit ID</div>
          <div class="fs-5 fw-semibold" id="walkinSuccessVisitNumber">&nbsp;</div>
        </div>

        <div class="alert alert-light border border-success-subtle mb-0" role="alert">
          <i class="bi bi-info-circle me-2 text-success"></i>The patient has been queued. Continue to the confirmation screen to print a slip or review details.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Register Another Patient</button>
        <button type="button" class="btn btn-outline-primary" id="walkinSuccessViewButton">
          <i class="bi bi-file-earmark-text me-1"></i>View Confirmation
        </button>
        <button type="button" class="btn btn-success" id="walkinSuccessQueueButton">
          <i class="bi bi-people me-1"></i>Go to Queue
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
  (function(){
    const form = document.getElementById('walkInRegistrationForm');
    if(!form){
      return;
    }

    const signatureCanvas = document.getElementById('walkin_signature');
    const signaturePad = new SignaturePad(signatureCanvas, {
      backgroundColor: '#ffffff',
      penColor: '#111827'
    });

    const ageInput = document.getElementById('walkin_age');
    const dobInput = document.getElementById('walkin_date_of_birth');
    const submitBtn = document.getElementById('walkin_submit_btn');
    const patientNumberField = document.getElementById('walkin_patient_number');
    const visitNumberField = document.getElementById('walkin_visit_number');
    const patientSignatureField = document.getElementById('walkin_patient_signature');
    const clearSignatureBtn = document.getElementById('walkin_clear_signature');
    const resetButton = document.getElementById('walkin_reset_form');
    const successModalEl = document.getElementById('walkinSuccessModal');
    const successPatientName = document.getElementById('walkinSuccessPatientName');
    const successPatientNumber = document.getElementById('walkinSuccessPatientNumber');
    const successQueueNumber = document.getElementById('walkinSuccessQueueNumber');
    const successVisitNumber = document.getElementById('walkinSuccessVisitNumber');
    const successViewBtn = document.getElementById('walkinSuccessViewButton');
    const successQueueBtn = document.getElementById('walkinSuccessQueueButton');
    const successModal = successModalEl && typeof bootstrap !== 'undefined'
      ? new bootstrap.Modal(successModalEl)
      : null;

    if (clearSignatureBtn) {
      clearSignatureBtn.addEventListener('click', () => signaturePad.clear());
    }

    function calculateAge(){
      if(!dobInput.value){
        ageInput.value = '';
        return;
      }
      const birth = new Date(dobInput.value);
      const today = new Date();
      let age = today.getFullYear() - birth.getFullYear();
      const m = today.getMonth() - birth.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      ageInput.value = age >= 0 ? age : '';
    }
    dobInput.addEventListener('change', calculateAge);


    form.addEventListener('submit', function(event){
      event.preventDefault();
      if(signaturePad.isEmpty()){
        patientSignatureField.value = '';
      } else {
        patientSignatureField.value = signaturePad.toDataURL();
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

      const formData = new FormData(form);

      fetch('{{ route('secretary.walkin.store') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(async response => {
        const data = await response.json();
        if(!response.ok){
          throw data;
        }
        patientNumberField.value = data.data.patient_number;
        visitNumberField.value = data.data.visit_number;

        const queueUrl = data.data.queue_url || null;
        const confirmationUrl = data.data.confirmation_url || null;

        if (successModal) {
          successPatientName.textContent = data.data.patient_name;
          successPatientNumber.textContent = data.data.patient_number;
          successVisitNumber.textContent = data.data.visit_number;
          if (successQueueNumber) {
            successQueueNumber.textContent = data.data.queue_number || '—';
          }
          if (successViewBtn) {
            successViewBtn.onclick = () => {
              if (confirmationUrl) {
                window.location.href = confirmationUrl;
              }
            };
          }
          if (successQueueBtn) {
            successQueueBtn.onclick = () => {
              if (queueUrl) {
                window.location.href = queueUrl;
              }
            };
          }
          successModal.show();
        } else if (queueUrl) {
          window.location.href = queueUrl;
        } else if (confirmationUrl) {
          window.location.href = confirmationUrl;
        }
      })
      .catch(error => {
        if(error && error.errors){
          const messages = Object.values(error.errors).flat().join('\n');
          alert('Validation failed:\n' + messages);
        } else {
          alert('Registration failed. Please try again.');
        }
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Register Walk-In Patient';
      });
    });

    if (resetButton) {
      resetButton.addEventListener('click', () => {
        form.reset();
        signaturePad.clear();
        patientNumberField.value = '';
        visitNumberField.value = '';
        ageInput.value = '';
        if (patientSignatureField) {
          patientSignatureField.value = '';
        }
      });
    }
  })();
</script>
@endpush
