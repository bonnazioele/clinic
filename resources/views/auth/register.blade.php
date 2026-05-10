@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<style>
  .auth-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
  }

  .auth-shell {
    width: min(1180px, 100%);
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(420px, 560px);
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
    min-height: 660px;
    padding: 2rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 86% 22%, rgba(255, 255, 255, 0.20), transparent 18%),
      radial-gradient(circle at 10% 88%, rgba(34, 197, 94, 0.26), transparent 26%),
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
    font-size: clamp(2.2rem, 4.8vw, 4.3rem);
    line-height: 0.96;
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
    grid-template-columns: 1fr;
    gap: 0.75rem;
    margin-top: 1.5rem;
  }

  .auth-mini-card {
    border-radius: 18px;
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(14px);
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .auth-mini-card i {
    font-size: 1.25rem;
  }

  .auth-mini-card strong {
    display: block;
    font-size: 0.9rem;
    font-weight: 900;
  }

  .auth-mini-card span {
    display: block;
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.78rem;
    font-weight: 650;
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

  .auth-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
  }

  .auth-span-2 {
    grid-column: span 2;
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

  .auth-form textarea.form-control {
    min-height: 82px;
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

    .auth-form-grid {
      grid-template-columns: 1fr;
    }

    .auth-span-2 {
      grid-column: span 1;
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
        <h1 class="auth-hero-title">Start your care journey with CliniQ.</h1>
        <p class="auth-hero-copy">
          Create a patient account to book appointments, track queue status, and keep your clinic visits organized.
        </p>
      </div>

      <div class="auth-mini-grid">
        <div class="auth-mini-card">
          <i class="bi bi-calendar-plus"></i>
          <div>
            <strong>Book appointments</strong>
            <span>Choose available doctor schedules and services.</span>
          </div>
        </div>

        <div class="auth-mini-card">
          <i class="bi bi-activity"></i>
          <div>
            <strong>Track your queue</strong>
            <span>See your visit status after booking.</span>
          </div>
        </div>

        <div class="auth-mini-card">
          <i class="bi bi-file-earmark-medical"></i>
          <div>
            <strong>Manage visit details</strong>
            <span>Keep contact and appointment information ready.</span>
          </div>
        </div>
      </div>
    </section>

    <section class="auth-card">
      @include('partials.alerts', ['toastOffsetTop' => '6rem', 'toastOffsetRight' => '1.25rem'])

      <div class="auth-card-head">
        <span class="auth-title-icon">
          <i class="bi bi-person-plus"></i>
        </span>

        <div>
          <h2 class="auth-card-title">Create Account</h2>
          <p class="auth-card-subtitle">Fill in your patient profile details.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="auth-form-grid">
          <div>
            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
            <input id="first_name"
                   type="text"
                   class="form-control @error('first_name') is-invalid @enderror"
                   name="first_name"
                   value="{{ old('first_name') }}"
                   required
                   autofocus
                   autocomplete="given-name">
            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div>
            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
            <input id="last_name"
                   type="text"
                   class="form-control @error('last_name') is-invalid @enderror"
                   name="last_name"
                   value="{{ old('last_name') }}"
                   required
                   autocomplete="family-name">
            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="auth-span-2">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input id="email"
                   type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="email">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div>
            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input id="phone"
                   type="tel"
                   class="form-control @error('phone') is-invalid @enderror"
                   name="phone"
                   value="{{ old('phone') }}"
                   required
                   placeholder="09123456789"
                   autocomplete="tel">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div>
            <label for="birthdate" class="form-label">Birth Date <span class="text-danger">*</span></label>
            <input id="birthdate"
                   type="date"
                   class="form-control @error('birthdate') is-invalid @enderror"
                   name="birthdate"
                   value="{{ old('birthdate') }}"
                   required>
            @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="auth-span-2">
            <label for="address" class="form-label">Address <span class="text-muted">(optional)</span></label>
            <textarea id="address"
                      name="address"
                      class="form-control @error('address') is-invalid @enderror"
                      rows="2"
                      placeholder="House number, street, city">{{ old('address') }}</textarea>
            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div>
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input id="password"
                     type="password"
                     class="form-control @error('password') is-invalid @enderror"
                     name="password"
                     required
                     autocomplete="new-password">
              <button type="button"
                      class="btn btn-outline-secondary password-toggle"
                      data-target="#password"
                      aria-label="Show password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div>
            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input id="password_confirmation"
                     type="password"
                     class="form-control"
                     name="password_confirmation"
                     required
                     autocomplete="new-password">
              <button type="button"
                      class="btn btn-outline-secondary password-toggle"
                      data-target="#password_confirmation"
                      aria-label="Show password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary auth-submit w-100 mt-4" data-loading-text="Creating account...">
          <i class="bi bi-person-check me-2"></i>
          Create Account
        </button>

        <div class="auth-link-box mt-3">
          <span class="text-muted small">Already registered?</span>
          <a href="{{ route('login') }}" class="text-primary ms-1">Sign in</a>
        </div>
      </form>
    </section>
  </div>
</div>
@endsection
