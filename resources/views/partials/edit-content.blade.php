@php
  use Illuminate\Support\Facades\Storage;

  $documentPath = $user->medical_document ?? null;
@endphp

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
                <p class="profile-edit-subtitle">{{ $meta['hero_edit'] }}</p>
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
                  {{ $meta['label'] }}
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

                        <div class="field-help">This name will appear on your account.</div>

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

                        <div class="field-help">This email is used for account-related updates.</div>

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

                        <div class="field-help">Keep this updated for clinic communication.</div>

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

                        <div class="field-help">Your account type cannot be changed here.</div>
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

                    <div class="field-help">This helps keep your profile complete.</div>

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
                        Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG. Maximum size: 5MB.
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

                    <a href="{{ route($role . '.profile.show') }}" class="btn btn-outline-secondary">
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