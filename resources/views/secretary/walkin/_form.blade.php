@push('styles')
<style>
  .walkin-page-shell {
    width: 96%;
    max-width: none;
    margin: 0 auto;
  }

  .walkin-hero {
    border-radius: 26px;
    padding: 1.4rem;
    color: #fff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
    overflow: hidden;
  }

  .walkin-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .walkin-hero-title-wrap {
    display: flex;
    gap: .9rem;
    align-items: flex-start;
  }

  .walkin-hero-icon {
    width: 60px;
    height: 60px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.6rem;
    flex: 0 0 60px;
  }

  .walkin-hero-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.5vw, 2.15rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .walkin-hero-subtitle {
    margin: .35rem 0 0;
    opacity: .94;
    font-weight: 650;
  }

  .walkin-hero-badges {
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
    margin-top: .85rem;
  }

  .walkin-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border-radius: 999px;
    background: rgba(255,255,255,.17);
    color: #fff;
    font-size: .83rem;
    font-weight: 800;
  }

  .walkin-hero-actions {
    display: flex;
    gap: .65rem;
    flex-wrap: wrap;
  }

  .walkin-hero-actions .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .walkin-card {
    border: 1px solid rgba(226,232,240,.96);
    border-radius: 26px;
    background: rgba(255,255,255,.96);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .walkin-card-body {
    padding: 1.15rem;
  }

  .walkin-section {
    border: 1px solid #edf2f7;
    border-radius: 22px;
    padding: 1rem;
    background:
      radial-gradient(circle at 100% 0%, rgba(13,110,253,.05), transparent 18%),
      #ffffff;
    margin-bottom: 1rem;
  }

  .walkin-section-head {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1rem;
  }

  .walkin-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 15px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.15rem;
    flex: 0 0 44px;
  }

  .walkin-section-title {
    margin: 0;
    color: #0f172a;
    font-weight: 900;
    font-size: 1rem;
  }

  .walkin-section-subtitle {
    margin: .1rem 0 0;
    color: #64748b;
    font-size: .86rem;
    font-weight: 650;
  }

  .walkin-card .form-label {
    color: #334155;
    font-weight: 800;
    font-size: .88rem;
  }

  .walkin-card .form-control,
  .walkin-card .form-select {
    border-radius: 14px;
    border-color: #dbe3ef;
    min-height: 46px;
    font-weight: 650;
  }

  .walkin-card .form-control:focus,
  .walkin-card .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 .22rem rgba(13,110,253,.12);
  }

  .walkin-alert {
    border-radius: 20px;
    border: 1px solid #fde68a;
    background: #fffbeb;
    padding: 1rem;
    margin-bottom: 1rem;
  }

  .guest-flow-card {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1e3a8a;
    border-radius: 18px;
    padding: 1rem;
    font-weight: 650;
    margin-bottom: 1rem;
  }

  .walkin-actions {
    position: sticky;
    bottom: 1rem;
    z-index: 20;
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(12px);
    border-radius: 22px;
    padding: .8rem;
    box-shadow: 0 18px 45px rgba(15,23,42,.10);
  }

  .walkin-actions .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .walkin-modal-card {
    border-radius: 24px;
    overflow: hidden;
  }

  .walkin-success-tile {
    border: 1px solid #dcfce7;
    background: #f0fdf4;
    border-radius: 18px;
    padding: .9rem;
  }

  .walkin-success-tile .small {
    color: #64748b;
    font-weight: 800;
  }

  .walkin-success-tile .value {
    color: #166534;
    font-size: 1.2rem;
    font-weight: 900;
  }

  @media (max-width: 768px) {
    .walkin-page-shell {
      width: 94%;
    }

    .walkin-card-body {
      padding: .8rem;
    }

    .walkin-actions {
      position: static;
    }

    .walkin-actions .btn,
    .walkin-hero-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush

@php
  $clinicServices = isset($clinicServices) ? collect($clinicServices) : collect();
  $clinicDoctors = isset($clinicDoctors) ? collect($clinicDoctors) : collect();
  $hasClinicServices = $clinicServices->isNotEmpty();
  $hasClinicDoctors = $clinicDoctors->isNotEmpty();
  $activeClinicName = $activeClinic->name ?? null;
@endphp

<div id="walkInRegistrationModule">
  <div class="walkin-hero">
    <div class="walkin-hero-row">
      <div>
        <div class="walkin-hero-title-wrap">
          <div class="walkin-hero-icon">
            <i class="bi bi-person-plus"></i>
          </div>

          <div>
            <h1 class="walkin-hero-title">Guest Walk-In Queue</h1>
            <p class="walkin-hero-subtitle">
              Add a walk-in patient to the queue using basic details only. Assign the patient to the correct doctor.
            </p>

            <div class="walkin-hero-badges">
              <span class="walkin-hero-badge">
                <i class="bi bi-building"></i>
                {{ $activeClinicName ?? 'Active Clinic' }}
              </span>

              <span class="walkin-hero-badge">
                <i class="bi bi-calendar2-check"></i>
                {{ now()->format('M d, Y') }}
              </span>

              <span class="walkin-hero-badge">
                <i class="bi bi-person-badge"></i>
                Guest Profile
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="walkin-hero-actions">
        <a href="{{ route('secretary.walkin.directory') }}" class="btn btn-light text-primary">
          <i class="bi bi-person-lines-fill me-1"></i>Patient Directory
        </a>

        <a href="{{ route('secretary.queue.overview') }}" class="btn btn-outline-light">
          <i class="bi bi-people me-1"></i>Back to Queue
        </a>
      </div>
    </div>
  </div>

  <div class="walkin-card">
    <div class="walkin-card-body">
      @if(! $hasClinicServices)
        <div class="walkin-alert">
          <div class="d-flex gap-3 align-items-start">
            <div class="fs-3 text-warning">
              <i class="bi bi-exclamation-triangle-fill"></i>
            </div>

            <div>
              <div class="fw-bold mb-1">No services configured for this clinic yet.</div>
              <div class="small text-muted">
                Add at least one service under Secretary &raquo; Services so you can tag walk-ins correctly.
              </div>

              <a class="btn btn-sm btn-outline-warning mt-2" href="{{ route('secretary.services.index') }}">
                Manage Clinic Services
              </a>
            </div>
          </div>
        </div>
      @endif

      @if(! $hasClinicDoctors)
        <div class="walkin-alert">
          <div class="d-flex gap-3 align-items-start">
            <div class="fs-3 text-warning">
              <i class="bi bi-person-exclamation"></i>
            </div>

            <div>
              <div class="fw-bold mb-1">No doctors are assigned to this clinic yet.</div>
              <div class="small text-muted">
                Assign a doctor and doctor services first so walk-in patients can appear on the doctor side.
              </div>
            </div>
          </div>
        </div>
      @endif

      <div class="guest-flow-card">
        <i class="bi bi-info-circle me-2"></i>
        This will create a <strong>guest patient profile</strong>, assign the patient to a doctor, and add the patient to the queue.
        It will <strong>not</strong> create a login account yet.
      </div>

      <form id="walkInRegistrationForm">
        @csrf

        <input type="hidden" id="walkin_patient_number" value="">
        <input type="hidden" id="walkin_visit_number" value="">
        <input type="hidden" name="visit_type" value="Walk-In">
        <input type="hidden" name="patient_type" value="New">

        <div class="walkin-section">
          <div class="walkin-section-head">
            <div class="walkin-section-icon">
              <i class="bi bi-person-vcard"></i>
            </div>

            <div>
              <h2 class="walkin-section-title">Basic Patient Details</h2>
              <p class="walkin-section-subtitle">
                Only the details needed for queue registration are required.
              </p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name" id="walkin_last_name" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="first_name" id="walkin_first_name" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="middle_name" id="walkin_middle_name">
            </div>

            <div class="col-md-6">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email_address" id="walkin_email_address" placeholder="patient@email.com" required>
              <div class="form-text">
                This email will be used later for the account completion link.
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mobile Number</label>
              <input type="tel" class="form-control" name="mobile_number" id="walkin_mobile_number" placeholder="Optional">
            </div>
          </div>
        </div>

        <div class="walkin-section">
          <div class="walkin-section-head">
            <div class="walkin-section-icon">
              <i class="bi bi-hospital"></i>
            </div>

            <div>
              <h2 class="walkin-section-title">Queue Assignment</h2>
              <p class="walkin-section-subtitle">
                Select the service and doctor so the patient appears on the correct doctor queue.
              </p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-lg-3 col-md-6">
              <label class="form-label">Requested Service <span class="text-danger">*</span></label>
              <select class="form-select" name="requested_service" id="walkin_requested_service" {{ $hasClinicServices ? '' : 'disabled' }} required>
                <option value="">{{ $hasClinicServices ? 'Select a service' : 'No services available' }}</option>

                @foreach($clinicServices as $service)
                  <option value="{{ $service->name }}">
                    {{ $service->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Assign Doctor <span class="text-danger">*</span></label>
              <select class="form-select" name="doctor_id" id="walkin_doctor_id" {{ $hasClinicDoctors ? '' : 'disabled' }} required>
                <option value="">Select service first</option>

                @foreach($clinicDoctors as $doctor)
                  @php
                    $doctorServices = $doctor->services->pluck('name')->values()->toArray();
                  @endphp

                  <option value="{{ $doctor->id }}"
                          data-services='@json($doctorServices)'>
                    Dr. {{ $doctor->name }}
                  </option>
                @endforeach
              </select>

              <div class="form-text" id="doctorHelpText">
                Doctors will be filtered based on the selected service.
              </div>
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Reason for Visit</label>
              <input type="text" class="form-control" name="reason_for_visit" id="walkin_reason_for_visit" placeholder="Optional">
            </div>
          </div>
        </div>

        <div class="walkin-actions">
          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-success" id="walkin_submit_btn" {{ ($hasClinicServices && $hasClinicDoctors) ? '' : 'disabled' }}>
              <i class="bi bi-check2-circle me-2"></i>Add Guest to Queue
            </button>

            <button type="button" class="btn btn-outline-secondary" id="walkin_reset_form">
              <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Form
            </button>
          </div>

          <a href="{{ route('secretary.queue.overview') }}" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-2"></i>Back to Queue
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="walkinSuccessModal" tabindex="-1" aria-labelledby="walkinSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg walkin-modal-card">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold" id="walkinSuccessModalLabel">
          <i class="bi bi-check-circle me-2"></i>Guest Added to Queue
        </h5>

        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="text-muted mb-2 fw-semibold">Patient</p>
        <h5 class="fw-bold mb-4" id="walkinSuccessPatientName">&nbsp;</h5>

        <div class="walkin-success-tile mb-3">
          <div class="small">Patient ID</div>
          <div class="value" id="walkinSuccessPatientNumber">&nbsp;</div>
        </div>

        <div class="walkin-success-tile mb-3">
          <div class="small">Queue Number</div>
          <div class="value" id="walkinSuccessQueueNumber">&nbsp;</div>
        </div>

        <div class="walkin-success-tile mb-3">
          <div class="small">Assigned Doctor</div>
          <div class="value" id="walkinSuccessDoctorName">&nbsp;</div>
        </div>

        <div class="walkin-success-tile mb-3">
          <div class="small">Visit ID</div>
          <div class="value" id="walkinSuccessVisitNumber">&nbsp;</div>
        </div>

        <div class="alert alert-light border border-success-subtle mb-0 rounded-4" role="alert">
          <i class="bi bi-info-circle me-2 text-success"></i>
          The patient is now queued as a guest profile and will appear on the assigned doctor’s queue.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">
          Register Another Guest
        </button>

        <button type="button" class="btn btn-outline-primary rounded-pill fw-bold" id="walkinSuccessViewButton">
          <i class="bi bi-file-earmark-text me-1"></i>View Confirmation
        </button>

        <button type="button" class="btn btn-success rounded-pill fw-bold" id="walkinSuccessQueueButton">
          <i class="bi bi-people me-1"></i>Go to Queue
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    const form = document.getElementById('walkInRegistrationForm');

    if (!form) {
      return;
    }

    const serviceSelect = document.getElementById('walkin_requested_service');
    const doctorSelect = document.getElementById('walkin_doctor_id');
    const doctorHelpText = document.getElementById('doctorHelpText');

    const allDoctorOptions = doctorSelect
      ? Array.from(doctorSelect.querySelectorAll('option[value]')).map(option => option.cloneNode(true))
      : [];

    function filterDoctorsByService() {
      if (!serviceSelect || !doctorSelect) {
        return;
      }

      const selectedService = serviceSelect.value;

      doctorSelect.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = selectedService ? 'Select doctor' : 'Select service first';
      doctorSelect.appendChild(placeholder);

      if (!selectedService) {
        doctorSelect.value = '';

        if (doctorHelpText) {
          doctorHelpText.textContent = 'Doctors will be filtered based on the selected service.';
        }

        return;
      }

      let matchedCount = 0;

      allDoctorOptions.forEach(option => {
        if (!option.value) {
          return;
        }

        let services = [];

        try {
          services = JSON.parse(option.getAttribute('data-services') || '[]');
        } catch (error) {
          services = [];
        }

        if (services.includes(selectedService)) {
          doctorSelect.appendChild(option.cloneNode(true));
          matchedCount++;
        }
      });

      if (doctorHelpText) {
        doctorHelpText.textContent = matchedCount > 0
          ? `${matchedCount} doctor(s) available for ${selectedService}.`
          : `No doctor handles ${selectedService} in this clinic.`;
      }
    }

    if (serviceSelect) {
      serviceSelect.addEventListener('change', filterDoctorsByService);
      filterDoctorsByService();
    }

    const submitBtn = document.getElementById('walkin_submit_btn');
    const resetButton = document.getElementById('walkin_reset_form');
    const patientNumberField = document.getElementById('walkin_patient_number');
    const visitNumberField = document.getElementById('walkin_visit_number');

    const successModalEl = document.getElementById('walkinSuccessModal');
    const successPatientName = document.getElementById('walkinSuccessPatientName');
    const successPatientNumber = document.getElementById('walkinSuccessPatientNumber');
    const successQueueNumber = document.getElementById('walkinSuccessQueueNumber');
    const successDoctorName = document.getElementById('walkinSuccessDoctorName');
    const successVisitNumber = document.getElementById('walkinSuccessVisitNumber');
    const successViewBtn = document.getElementById('walkinSuccessViewButton');
    const successQueueBtn = document.getElementById('walkinSuccessQueueButton');

    const successModal = successModalEl && typeof bootstrap !== 'undefined'
      ? new bootstrap.Modal(successModalEl)
      : null;

    const showRegistrationError = (message) => {
      const fallbackMessage = message || 'Registration failed. Please try again.';

      if (typeof window.showGlobalModal === 'function') {
        window.showGlobalModal({
          title: 'Walk-In Registration',
          message: fallbackMessage,
          type: 'warning',
          buttonText: 'OK'
        });
        return;
      }

      alert(fallbackMessage);
    };

    form.addEventListener('submit', function (event) {
      event.preventDefault();

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

        if (!response.ok) {
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

          if (successDoctorName) {
            successDoctorName.textContent = data.data.doctor_name || '—';
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
        if (error && error.errors) {
          const messages = Object.values(error.errors).flat().join(' ');
          showRegistrationError('Validation failed: ' + messages);
        } else if (error && error.message) {
          showRegistrationError(error.message);
        } else {
          showRegistrationError('Registration failed. Please try again.');
        }
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Add Guest to Queue';
      });
    });

    if (resetButton) {
      resetButton.addEventListener('click', () => {
        form.reset();
        patientNumberField.value = '';
        visitNumberField.value = '';
        filterDoctorsByService();
      });
    }
  })();
</script>
@endpush
