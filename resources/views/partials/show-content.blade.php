@php
  use Illuminate\Support\Facades\Storage;

  $documentPath = $user->medical_document ?? null;
@endphp

<div class="role-profile-tab-shell patient-tab-shell">
  <div class="role-profile-tab-content patient-tab-content">
    <div class="container-fluid profile-page px-0">
      @include('partials.alerts')

      <div class="profile-shell">
        <div class="profile-hero">
          <div class="profile-hero-row">
            <div class="profile-title-wrap">
              <div class="profile-title-icon">
                <i class="bi {{ $meta['icon'] }}"></i>
              </div>

              <div>
                <h1 class="profile-title">My Profile</h1>
                <p class="profile-subtitle">{{ $meta['hero_show'] }}</p>
              </div>
            </div>

            <a href="{{ route($role . '.profile.edit') }}" class="btn btn-primary profile-edit-btn">
            <i class="bi bi-pencil-square me-1"></i>
            Edit Profile
            </a>
          </div>
        </div>

        <div class="profile-body">
          <div class="profile-grid">
            <aside class="profile-main-card">
              <div class="profile-avatar">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
              </div>

              <h2 class="profile-name">{{ $user->name }}</h2>

              <div class="profile-role">
                <i class="bi {{ $meta['icon'] }}"></i>
                {{ $meta['label'] }}
              </div>

              <div class="profile-mini-details">
                <div class="mini-detail">
                  <i class="bi bi-envelope"></i>
                  <div>
                    <div class="mini-label">Email</div>
                    <div class="mini-value">{{ $user->email }}</div>
                  </div>
                </div>

                <div class="mini-detail">
                  <i class="bi bi-telephone"></i>
                  <div>
                    <div class="mini-label">Phone</div>
                    <div class="mini-value">{{ $user->phone ?? 'Not provided' }}</div>
                  </div>
                </div>
              </div>
            </aside>

            <main>
              <section class="profile-info-card">
                <div class="profile-card-header">
                  <h5 class="profile-card-title">
                    <i class="bi bi-info-circle"></i>
                    Personal Information
                  </h5>

                  <span class="profile-status-pill">
                    <i class="bi bi-check-circle-fill"></i>
                    Active
                  </span>
                </div>

                <div class="profile-info-body">
                  <div class="profile-info-grid">
                    <div class="info-box">
                      <div class="info-label">
                        <i class="bi bi-person"></i>
                        Full Name
                      </div>
                      <div class="info-value">{{ $user->name }}</div>
                    </div>

                    <div class="info-box">
                      <div class="info-label">
                        <i class="bi bi-envelope"></i>
                        Email Address
                      </div>
                      <div class="info-value">{{ $user->email }}</div>
                    </div>

                    <div class="info-box">
                      <div class="info-label">
                        <i class="bi bi-telephone"></i>
                        Phone Number
                      </div>
                      <div class="info-value">{{ $user->phone ?? 'Not provided' }}</div>
                    </div>

                    <div class="info-box">
                      <div class="info-label">
                        <i class="bi bi-shield-check"></i>
                        Account Type
                      </div>
                      <div class="info-value">{{ $meta['label'] }}</div>
                    </div>

                    <div class="info-box full">
                      <div class="info-label">
                        <i class="bi bi-geo-alt"></i>
                        Address
                      </div>
                      <div class="info-value">{{ $user->address ?: 'Not provided' }}</div>
                    </div>
                  </div>
                </div>
              </section>

              <section class="profile-document-card">
                <div class="profile-card-header">
                  <h5 class="profile-card-title">
                    <i class="bi bi-file-earmark-medical"></i>
                    {{ $meta['document_title'] }}
                  </h5>
                </div>

                <div class="document-body">
                  @if($documentPath)
                    <div class="document-box">
                      <div class="document-left">
                        <div class="document-icon">
                          <i class="bi bi-file-earmark-text"></i>
                        </div>

                        <div>
                          <div class="document-title">Uploaded Document</div>
                          <div class="document-sub">
                            You can view or download your uploaded file.
                          </div>
                        </div>
                      </div>

                      <a href="{{ Storage::url($documentPath) }}"
                         target="_blank"
                         class="btn btn-outline-primary document-btn">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        View / Download
                      </a>
                    </div>
                  @else
                    <div class="no-document">
                      <i class="bi bi-info-circle me-1"></i>
                      {{ $meta['document_empty'] }}
                    </div>
                  @endif
                </div>
              </section>

              <section class="profile-note-card">
                <h6 class="note-title">
                  <i class="bi bi-lightbulb"></i>
                  Profile Reminder
                </h6>

                <ul class="note-list">
                  @foreach($meta['reminders'] as $reminder)
                    <li>{{ $reminder }}</li>
                  @endforeach
                </ul>
              </section>
            </main>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>