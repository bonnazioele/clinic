@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<style>
  .auth-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
  }

  .auth-shell {
    width: min(1120px, 100%);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(360px, 440px);
    gap: 1rem;
    align-items: stretch;
  }

  .auth-hero,
  .auth-card {
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 28px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .auth-hero {
    min-height: 560px;
    padding: 2rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 88% 22%, rgba(255, 255, 255, 0.20), transparent 18%),
      radial-gradient(circle at 10% 88%, rgba(14, 165, 233, 0.34), transparent 26%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .auth-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 950;
    font-size: 1.05rem;
  }

  .auth-brand-icon,
  .auth-title-icon {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.45rem;
    flex: 0 0 52px;
  }

  .auth-hero-title {
    margin: 0;
    max-width: 680px;
    font-size: clamp(2.35rem, 5vw, 4.6rem);
    line-height: 0.94;
    font-weight: 950;
    letter-spacing: -0.055em;
  }

  .auth-hero-copy {
    max-width: 560px;
    margin: 1rem 0 0;
    color: rgba(255, 255, 255, 0.86);
    font-weight: 650;
  }

  .auth-mini-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    margin-top: 1.5rem;
  }

  .auth-mini-card {
    border-radius: 18px;
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(14px);
  }

  .auth-mini-card i {
    font-size: 1.25rem;
  }

  .auth-mini-card strong {
    display: block;
    margin-top: 0.45rem;
    font-size: 0.86rem;
    font-weight: 900;
  }

  .auth-card {
    background: rgba(255, 255, 255, 0.96);
    padding: 1.5rem;
  }

  .auth-card-head {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    margin-bottom: 1.35rem;
  }

  .auth-title-icon {
    background: #eff6ff;
    color: #0d6efd;
  }

  .auth-card-title {
    margin: 0;
    color: #0f172a;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .auth-card-subtitle {
    margin: 0.2rem 0 0;
    color: #64748b;
    font-size: 0.92rem;
    font-weight: 650;
  }

  .auth-form .form-label {
    color: #334155;
    font-size: 0.82rem;
    font-weight: 850;
  }

  .auth-form .form-control {
    min-height: 48px;
    border-radius: 15px !important;
    border-color: #dbe3ef;
    background: #f8fafc;
    font-weight: 650;
    box-shadow: none;
  }

  .auth-form .form-control:focus {
    border-color: rgba(13, 110, 253, 0.55);
    background: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.10);
  }

  .auth-form .input-group .btn {
    border-radius: 0 15px 15px 0;
    border-color: #dbe3ef;
    background: #ffffff;
  }

  .auth-submit {
    min-height: 48px;
    border-radius: 15px;
    font-weight: 950;
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.20);
  }

  .auth-link-box {
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.9rem;
    text-align: center;
  }

  .auth-link-box a {
    font-weight: 900;
  }

  @media (max-width: 991.98px) {
    .auth-shell {
      grid-template-columns: 1fr;
    }

    .auth-hero {
      min-height: auto;
    }
  }

  @media (max-width: 575.98px) {
    .auth-page {
      padding: 1rem 0;
    }

    .auth-hero,
    .auth-card {
      border-radius: 22px;
    }

    .auth-mini-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="auth-page">
  <div class="auth-shell">
    <section class="auth-hero">
      <div class="auth-brand">
        <span class="auth-brand-icon">
          <i class="bi bi-heart-pulse"></i>
        </span>
        <span>CliniQ</span>
      </div>

      <div>
        <h1 class="auth-hero-title">Welcome back to your care workspace.</h1>
        <p class="auth-hero-copy">
          Sign in to manage appointments, queues, schedules, and clinic activity from one calm dashboard.
        </p>
      </div>

      <div class="auth-mini-grid">
        <div class="auth-mini-card">
          <i class="bi bi-calendar2-check"></i>
          <strong>Appointments</strong>
        </div>

        <div class="auth-mini-card">
          <i class="bi bi-list-ol"></i>
          <strong>Queue tracking</strong>
        </div>

        <div class="auth-mini-card">
          <i class="bi bi-shield-check"></i>
          <strong>Secure access</strong>
        </div>
      </div>
    </section>

    <section class="auth-card">
      @include('partials.alerts')

      <div class="auth-card-head">
        <span class="auth-title-icon">
          <i class="bi bi-shield-lock"></i>
        </span>

        <div>
          <h2 class="auth-card-title">Sign In</h2>
          <p class="auth-card-subtitle">Use your clinic account credentials.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
          <input id="email"
                 type="email"
                 class="form-control @error('email') is-invalid @enderror"
                 name="email"
                 value="{{ old('email') }}"
                 required
                 autofocus
                 autocomplete="email">
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
          <div class="input-group">
            <input id="password"
                   type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password"
                   required
                   autocomplete="current-password">
            <button type="button"
                    class="btn btn-outline-secondary password-toggle"
                    data-target="#password"
                    aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label small text-muted fw-semibold" for="remember">
              Keep me logged in
            </label>
          </div>

          @if (Route::has('password.request'))
            <a class="small text-primary fw-bold" href="{{ route('password.request') }}">
              Forgot password?
            </a>
          @endif
        </div>

        <button type="submit" class="btn btn-primary auth-submit w-100" data-loading-text="Signing in...">
          <i class="bi bi-box-arrow-in-right me-2"></i>
          Sign In
        </button>

        <div class="auth-link-box mt-3">
          <span class="text-muted small">New to CliniQ?</span>
          <a href="{{ route('register') }}" class="text-primary ms-1">Create an account</a>
        </div>
      </form>
    </section>
  </div>
</div>
@endsection
