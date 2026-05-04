@php
  use Illuminate\Support\Facades\View;
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Str;

  $authUser = auth()->user();
  $user = $user ?? $authUser;

  $roleValue = data_get($user, 'role.name')
      ?? data_get($user, 'role')
      ?? data_get($user, 'user_role')
      ?? data_get($user, 'account_type')
      ?? data_get($authUser, 'role.name')
      ?? data_get($authUser, 'role')
      ?? 'patient';

  if (is_object($roleValue)) {
      $roleValue = data_get($roleValue, 'name') ?? data_get($roleValue, 'title') ?? 'patient';
  }

  $roleSlug = Str::slug(strtolower((string) $roleValue), '-');

  $roleAliases = [
      'clinic-secretary' => 'secretary',
      'staff' => 'secretary',
      'clinic-owner' => 'owner',
      'super-admin' => 'admin',
      'administrator' => 'admin',
  ];

  $roleSlug = $roleAliases[$roleSlug] ?? $roleSlug;

  $roleMeta = [
      'patient' => [
          'label' => 'Patient Account',
          'layout' => 'layouts.patient-dashboard',
          'icon' => 'bi-person-heart',
          'hero' => 'Update your personal information, contact details, address, and medical document.',
          'name_help' => 'This name will appear on your patient account.',
          'email_help' => 'Clinics may use this for account-related updates.',
          'phone_help' => 'Keep this updated so clinics can contact you.',
          'address_help' => 'This helps clinics keep accurate patient records.',
          'document_title' => 'Medical Document',
          'document_help' => 'Upload a new file only if you want to replace your current medical document.',
          'reminders' => [
              'Make sure your contact details are accurate before saving.',
              'Only upload a document if you want to replace the current one.',
              'After saving, review your profile to confirm the changes.',
          ],
      ],
      'secretary' => [
          'label' => 'Secretary Account',
          'layout' => 'layouts.secretary-dashboard',
          'icon' => 'bi-clipboard2-pulse',
          'hero' => 'Update your secretary profile, contact details, and staff account information.',
          'name_help' => 'This name will appear on your secretary account.',
          'email_help' => 'Clinics may use this for staff-related updates.',
          'phone_help' => 'Keep this updated for clinic coordination.',
          'address_help' => 'This helps keep your staff profile complete.',
          'document_title' => 'Profile Document',
          'document_help' => 'Upload a new file only if you want to replace your current profile document.',
          'reminders' => [
              'Make sure your contact details are accurate before saving.',
              'Contact the clinic owner or admin if your assigned clinic is incorrect.',
              'After saving, review your profile to confirm the changes.',
          ],
      ],
      'doctor' => [
          'label' => 'Doctor Account',
          'layout' => 'layouts.doctor-dashboard',
          'icon' => 'bi-heart-pulse',
          'hero' => 'Update your doctor profile, contact details, and professional account information.',
          'name_help' => 'This name will appear on your doctor account.',
          'email_help' => 'Clinics may use this for doctor-related updates.',
          'phone_help' => 'Keep this updated for clinic and patient coordination.',
          'address_help' => 'This helps keep your professional profile complete.',
          'document_title' => 'Professional Document',
          'document_help' => 'Upload a new file only if you want to replace your current professional document.',
          'reminders' => [
              'Make sure your contact details are accurate before saving.',
              'Contact the clinic owner or admin if your clinic/service assignment is incorrect.',
              'After saving, review your profile to confirm the changes.',
          ],
      ],
      'owner' => [
          'label' => 'Owner Account',
          'layout' => 'layouts.owner-dashboard',
          'icon' => 'bi-building-check',
          'hero' => 'Update your clinic owner profile, contact details, and owner account information.',
          'name_help' => 'This name will appear on your owner account.',
          'email_help' => 'The system may use this for clinic ownership updates.',
          'phone_help' => 'Keep this updated for clinic management communication.',
          'address_help' => 'This helps keep your owner profile complete.',
          'document_title' => 'Profile Document',
          'document_help' => 'Upload a new file only if you want to replace your current profile document.',
          'reminders' => [
              'Make sure your contact details are accurate before saving.',
              'Contact the system admin if your clinic ownership details are incorrect.',
              'After saving, review your profile to confirm the changes.',
          ],
      ],
      'admin' => [
          'label' => 'Admin Account',
          'layout' => 'layouts.admin-dashboard',
          'icon' => 'bi-shield-lock',
          'hero' => 'Update your administrator profile, contact details, and system account information.',
          'name_help' => 'This name will appear on your admin account.',
          'email_help' => 'The system may use this for admin-related updates.',
          'phone_help' => 'Keep this updated for system communication.',
          'address_help' => 'This helps keep your admin profile complete.',
          'document_title' => 'Profile Document',
          'document_help' => 'Upload a new file only if you want to replace your current profile document.',
          'reminders' => [
              'Make sure your contact details are accurate before saving.',
              'Use admin account details carefully.',
              'After saving, review your profile to confirm the changes.',
          ],
      ],
  ];

  $meta = $roleMeta[$roleSlug] ?? [
      'label' => Str::title(str_replace('-', ' ', $roleSlug)) . ' Account',
      'layout' => 'layouts.app',
      'icon' => 'bi-person-badge',
      'hero' => 'Update your profile, contact details, and account information.',
      'name_help' => 'This name will appear on your account.',
      'email_help' => 'The system may use this for account-related updates.',
      'phone_help' => 'Keep this updated for communication.',
      'address_help' => 'This helps keep your profile complete.',
      'document_title' => 'Profile Document',
      'document_help' => 'Upload a new file only if you want to replace your current profile document.',
      'reminders' => [
          'Make sure your contact details are accurate before saving.',
          'Only upload a document if you want to replace the current one.',
          'After saving, review your profile to confirm the changes.',
      ],
  ];

  $profileLayout = View::exists($meta['layout']) ? $meta['layout'] : 'layouts.app';

  $documentPath = data_get($user, 'medical_document');
@endphp

@extends($profileLayout)

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@push('styles')
<style>
  .role-profile-tab-shell,
  .role-profile-tab-content,
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .profile-edit-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .profile-edit-shell {
    width: 100%;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.95);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
  }

  .profile-edit-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .profile-edit-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .profile-edit-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .profile-edit-title-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    color: #ffffff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
    font-size: 1.4rem;
  }

  .profile-edit-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .profile-edit-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .profile-back-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .profile-edit-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .profile-edit-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.85fr);
    gap: 1rem;
    align-items: start;
  }

  .profile-form-card,
  .profile-preview-card,
  .profile-help-card {
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
    overflow: hidden;
  }

  .profile-form-card {
    position: relative;
  }

  .profile-form-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .profile-card-header {
    padding: 1rem 1.15rem 0.85rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .profile-card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #0f172a;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .profile-card-title i {
    color: #0d6efd;
  }

  .profile-edit-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.4rem 0.65rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .profile-form-body {
    padding: 1.15rem;
  }

  .form-section {
    margin-bottom: 1.05rem;
  }

  .form-section:last-child {
    margin-bottom: 0;
  }

  .section-label {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.75rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 800;
  }

  .section-label i {
    color: #0d6efd;
  }

  .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 0.4rem;
  }

  .form-control {
    border-radius: 14px;
    border-color: #dbe3ef;
    padding: 0.68rem 0.8rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: none;
  }

  textarea.form-control {
    min-height: 108px;
    resize: vertical;
  }

  .form-control:focus {
    border-color: rgba(13, 110, 253, 0.55);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
  }

  .field-help {
    margin-top: 0.38rem;
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
  }

  .field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
  }

  .document-upload-box {
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px dashed rgba(13, 110, 253, 0.32);
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.055), rgba(255, 255, 255, 0.94));
  }

  .document-upload-head {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    margin-bottom: 0.8rem;
  }

  .document-upload-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.2rem;
  }

  .document-upload-title {
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 900;
    margin-bottom: 0.12rem;
  }

  .document-upload-text {
    color: #64748b;
    font-size: 0.77rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .current-document {
    margin-top: 0.75rem;
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.7rem;
  }

  .current-document-text {
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
  }

  .current-document-link {
    border-radius: 11px;
    font-size: 0.78rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .profile-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    padding-top: 0.2rem;
  }

  .profile-actions .btn {
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 800;
    padding: 0.58rem 0.9rem;
  }

  .side-stack {
    display: grid;
    gap: 1rem;
  }

  .profile-preview-card {
    padding: 1rem;
    text-align: center;
  }

  .profile-avatar-preview {
    width: 86px;
    height: 86px;
    margin: 0 auto 0.85rem;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.18), transparent 38%),
      linear-gradient(135deg, #e8f2ff, #f8fbff);
    color: #0d6efd;
    border: 1px solid rgba(191, 219, 254, 0.9);
    font-size: 2.2rem;
    font-weight: 900;
  }

  .preview-name {
    margin: 0;
    color: #0f172a;
    font-size: 1.12rem;
    font-weight: 900;
    letter-spacing: -0.03em;
  }

  .preview-role {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.5rem;
    border-radius: 999px;
    padding: 0.4rem 0.7rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.75rem;
    font-weight: 800;
  }

  .preview-list {
    margin-top: 1rem;
    display: grid;
    gap: 0.6rem;
    text-align: left;
  }

  .preview-item {
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .preview-label {
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.12rem;
  }

  .preview-value {
    color: #0f172a;
    font-size: 0.84rem;
    font-weight: 800;
    line-height: 1.3;
    word-break: break-word;
  }

  .profile-help-card {
    padding: 1rem;
    background:
      linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.95));
  }

  .help-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 800;
  }

  .help-title i {
    color: #0d6efd;
  }

  .help-list {
    margin: 0;
    padding-left: 1.05rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.55;
  }

  @media (max-width: 1100px) {
    .profile-edit-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .profile-edit-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .profile-edit-hero,
    .profile-edit-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .profile-edit-hero-row,
    .profile-card-header,
    .current-document {
      flex-direction: column;
      align-items: stretch;
    }

    .profile-edit-title {
      font-size: 1.35rem;
    }

    .profile-edit-subtitle {
      font-size: 0.82rem;
    }

    .profile-back-btn,
    .profile-actions .btn,
    .current-document-link {
      width: 100%;
    }

    .profile-actions {
      flex-direction: column;
    }

    .field-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="role-profile-tab-shell patient-tab-shell">
  <div class="role-profile-tab-content patient-tab-content">
    <div class="container-fluid profile-edit-page px-0">
      @include('partials.alerts')

      <div class="profile-edit-shell">
        <div class="profile-edit-hero">
          <div class="profile-edit-hero-row">
            <div class="profile-edit-title-wrap">
              <div class="profile-edit-title-icon">
                <i class="bi {{ $meta['icon'] }}"></i>
              </div>

              <div>
                <h1 class="profile-edit-title">Edit Profile</h1>
                <p class="profile-edit-subtitle">{{ $meta['hero'] }}</p>
              </div>
            </div>

            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary profile-back-btn">
              <i class="bi bi-arrow-left me-1"></i>
              Back to Profile
            </a>
          </div>
        </div>

        <div class="profile-edit-body">
          <div class="profile-edit-grid">
            <main class="profile-form-card">
              <div class="profile-card-header">
                <h5 class="profile-card-title">
                  <i class="bi bi-person-lines-fill"></i>
                  Profile Information
                </h5>

                <span class="profile-edit-pill">
                  <i class="bi bi-info-circle"></i>
                  Editable
                </span>
              </div>

                <form method="POST" action="{{ route($role . '.profile.update') }}" enctype="multipart/form-data">                @csrf
                @method('PUT')

                <div class="profile-form-body">
                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-person"></i>
                      Basic Details
                    </div>

                    <div class="field-grid">
                      <div>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}"
                               required>

                        <div class="field-help">{{ $meta['name_help'] }}</div>

                        @error('name')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div>
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}"
                               required>

                        <div class="field-help">{{ $meta['email_help'] }}</div>

                        @error('email')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-telephone"></i>
                      Contact Details
                    </div>

                    <div class="field-grid">
                      <div>
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="e.g. 0917 123 4567">

                        <div class="field-help">{{ $meta['phone_help'] }}</div>

                        @error('phone')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div>
                        <label class="form-label">Account Type</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $meta['label'] }}"
                               readonly>

                        <div class="field-help">
                          Your account type cannot be changed here.
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-geo-alt"></i>
                      Address
                    </div>

                    <label for="address" class="form-label">Address</label>
                    <textarea id="address"
                              name="address"
                              class="form-control @error('address') is-invalid @enderror"
                              placeholder="Enter your complete address">{{ old('address', $user->address) }}</textarea>

                    <div class="field-help">{{ $meta['address_help'] }}</div>

                    @error('address')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-section">
                    <div class="section-label">
                      <i class="bi bi-file-earmark-medical"></i>
                      {{ $meta['document_title'] }}
                    </div>

                    <div class="document-upload-box">
                      <div class="document-upload-head">
                        <div class="document-upload-icon">
                          <i class="bi bi-cloud-arrow-up"></i>
                        </div>

                        <div>
                          <div class="document-upload-title">Upload Document</div>
                          <div class="document-upload-text">
                            {{ $meta['document_help'] }}
                          </div>
                        </div>
                      </div>

                      <input type="file"
                             name="medical_document"
                             class="form-control @error('medical_document') is-invalid @enderror">

                      <div class="field-help">
                        Optional. Accepted file type depends on your current backend validation.
                      </div>

                      @error('medical_document')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror

                      @if($documentPath)
                        <div class="current-document">
                          <div class="current-document-text">
                            <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                            You currently have a document uploaded.
                          </div>

                          <a href="{{ Storage::url($documentPath) }}"
                             target="_blank"
                             class="btn btn-outline-primary btn-sm current-document-link">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            View Current File
                          </a>
                        </div>
                      @endif
                    </div>
                  </div>

                  <div class="profile-actions">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-save me-2"></i>
                      Save Profile
                    </button>

                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                      <i class="bi bi-x-circle me-2"></i>
                      Cancel
                    </a>
                  </div>
                </div>
              </form>
            </main>

            <aside class="side-stack">
              <section class="profile-preview-card">
                <div class="profile-avatar-preview">
                  {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>

                <h2 class="preview-name">{{ $user->name }}</h2>

                <div class="preview-role">
                  <i class="bi {{ $meta['icon'] }}"></i>
                  {{ $meta['label'] }}
                </div>

                <div class="preview-list">
                  <div class="preview-item">
                    <div class="preview-label">Email</div>
                    <div class="preview-value">{{ $user->email }}</div>
                  </div>

                  <div class="preview-item">
                    <div class="preview-label">Phone</div>
                    <div class="preview-value">{{ $user->phone ?? 'Not provided' }}</div>
                  </div>

                  <div class="preview-item">
                    <div class="preview-label">Address</div>
                    <div class="preview-value">{{ $user->address ?? 'Not provided' }}</div>
                  </div>

                  <div class="preview-item">
                    <div class="preview-label">{{ $meta['document_title'] }}</div>
                    <div class="preview-value">
                      {{ $documentPath ? 'Uploaded' : 'Not uploaded' }}
                    </div>
                  </div>
                </div>
              </section>

              <section class="profile-help-card">
                <h6 class="help-title">
                  <i class="bi bi-lightbulb"></i>
                  Profile Reminder
                </h6>

                <ul class="help-list">
                  @foreach($meta['reminders'] as $reminder)
                    <li>{{ $reminder }}</li>
                  @endforeach
                </ul>
              </section>
            </aside>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection