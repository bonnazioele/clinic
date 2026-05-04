@push('styles')
<style>
  .role-profile-tab-shell,
  .role-profile-tab-content,
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .profile-page,
  .profile-edit-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .profile-shell,
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

  .profile-hero,
  .profile-edit-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .profile-hero-row,
  .profile-edit-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .profile-title-wrap,
  .profile-edit-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .profile-title-icon,
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

  .profile-title,
  .profile-edit-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .profile-subtitle,
  .profile-edit-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .profile-edit-btn,
  .profile-back-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .profile-body,
  .profile-edit-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: minmax(280px, 0.85fr) minmax(0, 1.65fr);
    gap: 1rem;
    align-items: start;
  }

  .profile-edit-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.85fr);
    gap: 1rem;
    align-items: start;
  }

  .profile-main-card,
  .profile-info-card,
  .profile-document-card,
  .profile-note-card,
  .profile-form-card,
  .profile-preview-card,
  .profile-help-card {
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
    overflow: hidden;
  }

  .profile-main-card,
  .profile-form-card {
    position: relative;
  }

  .profile-main-card {
    padding: 1.15rem;
    text-align: center;
  }

  .profile-main-card::before,
  .profile-form-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .profile-avatar,
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

  .profile-name,
  .preview-name {
    margin: 0;
    color: #0f172a;
    font-size: 1.2rem;
    font-weight: 900;
    letter-spacing: -0.035em;
  }

  .profile-role,
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

  .profile-mini-details,
  .preview-list {
    margin-top: 1rem;
    display: grid;
    gap: 0.6rem;
    text-align: left;
  }

  .mini-detail,
  .preview-item {
    padding: 0.75rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .mini-detail {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
  }

  .mini-detail i {
    color: #0d6efd;
    font-size: 1rem;
    margin-top: 0.05rem;
  }

  .mini-label,
  .preview-label {
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.12rem;
  }

  .mini-value,
  .preview-value {
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

  .profile-status-pill,
  .profile-edit-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.4rem 0.65rem;
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .profile-status-pill {
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
  }

  .profile-edit-pill {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
  }

  .profile-info-body,
  .profile-form-body,
  .document-body {
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

  .profile-document-card,
  .profile-note-card {
    margin-top: 1rem;
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

  .profile-note-card,
  .profile-help-card {
    padding: 1rem;
    background: linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.95));
  }

  .note-title,
  .help-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 800;
  }

  .note-title i,
  .help-title i {
    color: #0d6efd;
  }

  .note-list,
  .help-list {
    margin: 0;
    padding-left: 1.05rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.55;
  }

  .form-section {
    margin-bottom: 1.05rem;
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
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.055), rgba(255, 255, 255, 0.94));
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

  @media (max-width: 1100px) {
    .profile-grid,
    .profile-edit-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .profile-page,
    .profile-edit-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .profile-hero,
    .profile-body,
    .profile-edit-hero,
    .profile-edit-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .profile-hero-row,
    .profile-edit-hero-row,
    .profile-card-header,
    .document-box,
    .current-document {
      flex-direction: column;
      align-items: stretch;
    }

    .profile-title,
    .profile-edit-title {
      font-size: 1.35rem;
    }

    .profile-subtitle,
    .profile-edit-subtitle {
      font-size: 0.82rem;
    }

    .profile-edit-btn,
    .profile-back-btn,
    .document-btn,
    .current-document-link,
    .profile-actions .btn {
      width: 100%;
    }

    .profile-info-grid,
    .field-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush