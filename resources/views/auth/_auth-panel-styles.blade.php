<style>
  .auth-panel-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
  }

  .auth-panel {
    width: min(560px, 100%);
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .auth-panel-hero {
    padding: 1.35rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 88% 24%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
  }

  .auth-panel-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .auth-panel-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.5rem;
    flex: 0 0 54px;
  }

  .auth-panel-title {
    margin: 0;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .auth-panel-subtitle {
    margin: 0.3rem 0 0;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 650;
  }

  .auth-panel-body {
    padding: 1.35rem;
  }

  .auth-panel-body .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 850;
  }

  .auth-panel-body .form-control {
    min-height: 48px;
    border-radius: 15px !important;
    border-color: #dbe3ef;
    background: #f8fafc;
    font-weight: 650;
    box-shadow: none;
  }

  .auth-panel-body .form-control:focus {
    border-color: rgba(13, 110, 253, 0.55);
    background: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.10);
  }

  .auth-panel-body .input-group .btn {
    border-radius: 0 15px 15px 0;
    border-color: #dbe3ef;
    background: #ffffff;
  }

  .auth-panel-body .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .auth-panel-submit {
    min-height: 48px;
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.20);
  }

  .auth-panel-note {
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.95rem;
    color: #64748b;
    font-weight: 650;
  }
</style>
