@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<style>
  .auth-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 1.5rem 1rem;
  }

  .auth-shell {
    width: min(1180px, 100%);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(420px, 500px);
    gap: 2rem;
    align-items: center;
    min-height: 650px;
    padding: 1rem 1.25rem 1rem 3.5rem;
  }

  .auth-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }

  .auth-hero {
    min-height: 320px;
    padding: 0;
    color: #0f172a;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: left;
  }

  .auth-title-icon {
    width: auto;
    height: auto;
    border-radius: 0;
    display: inline-flex;
    place-items: initial;
    background: transparent;
    color: #0d6efd;
    font-size: 0.95rem;
    flex: 0 0 auto;
  }

  .auth-hero-title {
    margin: 0;
    max-width: 620px;
    color: #0d6efd;
    font-size: clamp(2rem, 3.8vw, 3.1rem);
    line-height: 1.08;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .auth-hero-copy {
    max-width: 460px;
    margin: 1rem 0 0;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 600;
  }

  .auth-card {
    background: #ffffff;
    padding: 1.4rem;
  }

  .auth-card-head {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    margin-bottom: 1.15rem;
  }

  .auth-card-title {
    margin: 0;
    color: #0d6efd;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 0;
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
    gap: 0.75rem;
  }

  .auth-span-2 {
    grid-column: span 2;
  }

  .auth-form .form-label {
    color: #334155;
    font-size: 0.78rem;
    font-weight: 800;
  }

  .auth-form .form-control {
    min-height: 40px;
    border-radius: 4px !important;
    border-color: #e2e8f0;
    background: #ffffff;
    font-weight: 600;
    box-shadow: none;
  }

  .auth-form textarea.form-control {
    min-height: 70px;
  }

  .auth-form .form-control:focus {
    border-color: rgba(13, 110, 253, 0.55);
    background: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.10);
  }

  .auth-form .input-group .btn {
    border-radius: 0 4px 4px 0;
    border-color: #e2e8f0;
    background: #ffffff;
  }

  .auth-submit {
    min-height: 40px;
    border-radius: 999px;
    font-weight: 900;
    box-shadow: none;
  }

  .auth-link-box {
    border-radius: 0;
    background: transparent;
    border: 0;
    padding: 0.75rem 0 0;
    text-align: center;
  }

  .auth-link-box a {
    font-weight: 900;
  }

  @media (max-width: 991.98px) {
    .auth-shell {
      grid-template-columns: 1fr;
      padding: 2rem;
    }

    .auth-hero {
      min-height: auto;
      text-align: center;
    }

    .auth-hero-title,
    .auth-hero-copy {
      margin-left: auto;
      margin-right: auto;
    }
  }

  @media (max-width: 575.98px) {
    .auth-page {
      padding: 1rem 0;
    }

    .auth-shell {
      padding: 1.25rem;
    }

    .auth-form-grid {
      grid-template-columns: 1fr;
    }

    .auth-span-2 {
      grid-column: span 1;
    }

    .auth-hero-title {
      font-size: clamp(1.85rem, 12vw, 2.5rem);
      line-height: 1.06;
    }
  }
</style>

<div class="auth-page">
  <div class="auth-shell">
    <section class="auth-hero">
      <div>
        <h1 class="auth-hero-title">Join CliniQ Today!</h1>
        <p class="auth-hero-copy">
          Create your account and start managing your healthcare journey.
        </p>
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
