@extends('layouts.app')

@section('title', 'Application Submitted')

@section('content')
<style>
  .thanks-page {
    min-height: calc(100vh - 40px);
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
  }

  .thanks-card {
    width: min(760px, 100%);
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    padding: 2rem;
    text-align: center;
  }

  .thanks-icon {
    width: 86px;
    height: 86px;
    border-radius: 28px;
    display: inline-grid;
    place-items: center;
    color: #047857;
    background: rgba(16, 185, 129, 0.14);
    font-size: 2.6rem;
    margin-bottom: 1rem;
  }

  .thanks-title {
    color: #0f172a;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .thanks-copy {
    max-width: 560px;
    margin: 0.65rem auto 1.35rem;
    color: #64748b;
    font-weight: 650;
  }

  .thanks-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .thanks-actions .btn {
    border-radius: 15px;
    font-weight: 900;
    padding: 0.7rem 1rem;
  }
</style>

<div class="thanks-page">
  <section class="thanks-card">
    <div class="thanks-icon">
      <i class="bi bi-check2-circle"></i>
    </div>

    <h1 class="thanks-title h2">Application Submitted</h1>
    <p class="thanks-copy">
      We received your clinic registration. Our admins will review it shortly, and you will get an email once it is approved.
    </p>

    <div class="thanks-actions">
      <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right me-1"></i>
        Go to Login
      </a>

      <a href="{{ route('welcome') }}" class="btn btn-outline-secondary">
        Back to Home
      </a>
    </div>
  </section>
</div>
@endsection
