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
          'hero' => 'View your personal information, contact details, and uploaded medical document.',
          'document_title' => 'Medical Document',
          'document_empty' => 'No medical document has been uploaded yet.',
          'reminders' => [
              'Keep your phone number updated so clinics can contact you when needed.',
              'Make sure your address is correct for clinic records.',
              'Upload or update your medical document if the clinic requires it.',
          ],
      ],
      'secretary' => [
          'label' => 'Secretary Account',
          'layout' => 'layouts.secretary-dashboard',
          'icon' => 'bi-clipboard2-pulse',
          'hero' => 'View your secretary profile, contact details, and clinic staff account information.',
          'document_title' => 'Profile Document',
          'document_empty' => 'No profile document has been uploaded yet.',
          'reminders' => [
              'Keep your phone number updated for clinic coordination.',
              'Review your account details regularly.',
              'Contact the clinic owner or admin if your assigned clinic details are incorrect.',
          ],
      ],
      'doctor' => [
          'label' => 'Doctor Account',
          'layout' => 'layouts.doctor-dashboard',
          'icon' => 'bi-heart-pulse',
          'hero' => 'View your doctor profile, contact details, and professional account information.',
          'document_title' => 'Professional Document',
          'document_empty' => 'No professional document has been uploaded yet.',
          'reminders' => [
              'Keep your contact details updated for patient and clinic notifications.',
              'Check that your clinic and service assignments are correct.',
              'Contact the clinic owner or admin if your profile details need correction.',
          ],
      ],
      'owner' => [
          'label' => 'Owner Account',
          'layout' => 'layouts.owner-dashboard',
          'icon' => 'bi-building-check',
          'hero' => 'View your clinic owner profile, contact details, and account information.',
          'document_title' => 'Profile Document',
          'document_empty' => 'No profile document has been uploaded yet.',
          'reminders' => [
              'Keep your contact information updated for clinic management notifications.',
              'Review your owner account details regularly.',
              'Contact the system admin if your clinic ownership details are incorrect.',
          ],
      ],
      'admin' => [
          'label' => 'Admin Account',
          'layout' => 'layouts.admin-dashboard',
          'icon' => 'bi-shield-lock',
          'hero' => 'View your administrator profile, contact details, and system account information.',
          'document_title' => 'Profile Document',
          'document_empty' => 'No profile document has been uploaded yet.',
          'reminders' => [
              'Keep your admin contact information updated.',
              'Review your account details regularly.',
              'Use admin privileges carefully when managing users and clinics.',
          ],
      ],
  ];

  $meta = $roleMeta[$roleSlug] ?? [
      'label' => Str::title(str_replace('-', ' ', $roleSlug)) . ' Account',
      'layout' => 'layouts.app',
      'icon' => 'bi-person-badge',
      'hero' => 'View your profile, contact details, and account information.',
      'document_title' => 'Profile Document',
      'document_empty' => 'No profile document has been uploaded yet.',
      'reminders' => [
          'Keep your contact details updated.',
          'Review your account information regularly.',
          'Contact the administrator if your details are incorrect.',
      ],
  ];

  $profileLayout = View::exists($meta['layout']) ? $meta['layout'] : 'layouts.app';

  $documentPath = data_get($user, 'medical_document');
  $address = data_get($user, 'address');
@endphp

@extends($profileLayout)

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
  .role-profile-tab-shell,
  .role-profile-tab-content,
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .profile-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .profile-shell {
    width: 100%;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.95);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
  }

  .profile-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .profile-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .profile-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .profile-title-icon {
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

  .profile-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .profile-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .profile-edit-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .profile-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: minmax(280px, 0.85fr) minmax(0, 1.65fr);
    gap: 1rem;
    align-items: start;
  }

  .profile-main-card,
  .profile-info-card,
  .profile-document-card,
  .profile-note-card {
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
    overflow: hidden;
  }

  .profile-main-card {
    position: relative;
    padding: 1.15rem;
    text-align: center;
  }

  .profile-main-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .profile-avatar {
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

  .profile-name {
    margin: 0;
    color: #0f172a;
    font-size: 1.2rem;
    font-weight: 900;
    letter-spacing: -0.035em;
  }

  .profile-role {
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

  .profile-mini-details {
    margin-top: 1rem;
    display: grid;
    gap: 0.6rem;
    text-align: left;
  }

  .mini-detail {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .mini-detail i {
    color: #0d6efd;
    font-size: 1rem;
    margin-top: 0.05rem;
  }

  .mini-label {
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.12rem;
  }

  .mini-value {
    color: #0f172a;
    font-size: 0.84rem;
    font-weight: 800;
    line-height: 1.3;
    word-break: break-word;
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

  .profile-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.4rem 0.65rem;
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .profile-info-body {
    padding: 1.15rem;
  }

  .profile-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.8rem;
  }

  .info-box {
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .info-box.full {
    grid-column: 1 / -1;
  }

  .info-label {
    display: flex;
    align-items: center;
    gap: 0.38rem;
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.2rem;
  }

  .info-label i {
    color: #0d6efd;
    font-size: 0.9rem;
  }

  .info-value {
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 800;
    line-height: 1.35;
    word-break: break-word;
  }

  .profile-document-card {
    margin-top: 1rem;
  }

  .document-body {
    padding: 1.15rem;
  }

  .document-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px solid #edf2f7;
    background: linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.95));
  }

  .document-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }

  .document-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 15px;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.3rem;
  }

  .document-title {
    color: #0f172a;
    font-size: 0.94rem;
    font-weight: 900;
    margin-bottom: 0.12rem;
  }

  .document-sub {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .document-btn {
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 800;
    padding: 0.5rem 0.8rem;
    white-space: nowrap;
  }

  .no-document {
    padding: 0.9rem;
    border-radius: 17px;
    border: 1px dashed rgba(13, 110, 253, 0.32);
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 600;
  }

  .profile-note-card {
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.95));
  }

  .note-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 800;
  }

  .note-title i {
    color: #0d6efd;
  }

  .note-list {
    margin: 0;
    padding-left: 1.05rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.55;
  }

  @media (max-width: 1100px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .profile-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .profile-hero,
    .profile-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .profile-hero-row,
    .profile-card-header,
    .document-box {
      flex-direction: column;
      align-items: stretch;
    }

    .profile-title {
      font-size: 1.35rem;
    }

    .profile-subtitle {
      font-size: 0.82rem;
    }

    .profile-edit-btn,
    .document-btn {
      width: 100%;
    }

    .profile-info-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
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
                <p class="profile-subtitle">{{ $meta['hero'] }}</p>
              </div>
            </div>

            <a href="{{ route('patient.profile.edit') }}" class="btn btn-primary profile-edit-btn">
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
                      <div class="info-value">{{ $address ?: 'Not provided' }}</div>
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
@endsection