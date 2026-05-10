@extends('layouts.app')

@section('title', 'Sign In')

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
    grid-template-columns: minmax(0, 1fr) minmax(380px, 560px);
    gap: 2rem;
    align-items: center;
    min-height: 450px;
    padding: 3rem 3.5rem;
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
    font-size: clamp(2rem, 4vw, 3.1rem);
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
    padding: 1.6rem;
  }

  .auth-card-head {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    margin-bottom: 1.25rem;
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

  .auth-form .form-label {
    color: #334155;
    font-size: 0.78rem;
    font-weight: 800;
  }

  .auth-form .form-control {
    min-height: 42px;
    border-radius: 4px !important;
    border-color: #e2e8f0;
    background: #ffffff;
    font-weight: 600;
    box-shadow: none;
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
    min-height: 42px;
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

    .auth-hero-title {
      font-size: clamp(1.85rem, 12vw, 2.4rem);
      line-height: 1.05;
    }
  }
</style>

<div class="auth-page">
  <div class="auth-shell">
    <section class="auth-hero">
      <div>
        <h1 class="auth-hero-title">Let's sign in to CliniQ!</h1>
        <p class="auth-hero-copy">
          Unlock healthcare and do more in just a minute.
        </p>
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
          Sign in
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
