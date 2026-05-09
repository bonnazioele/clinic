@extends('admin.layouts.app')

@section('title','Reports')

@section('content')
<style>
  .admin-reports-hero {
    border-radius: 30px;
    padding: 2rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    overflow: hidden;
  }

  .admin-reports-icon {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    display: inline-grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 2.2rem;
    margin-bottom: 1rem;
  }

  .admin-reports-title {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 950;
    letter-spacing: -0.055em;
  }

  .admin-reports-copy {
    max-width: 760px;
    margin: 0.85rem auto 1.25rem;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 650;
  }

  .admin-reports-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border-radius: 999px;
    padding: 0.6rem 0.85rem;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-weight: 900;
    margin-bottom: 1.25rem;
  }

  .admin-reports-hero .btn {
    border-radius: 15px;
    font-weight: 900;
  }
</style>

<section class="admin-reports-hero text-center">
  <div class="admin-reports-icon">
    <i class="bi bi-graph-up-arrow"></i>
  </div>

  <h1 class="admin-reports-title">Reports & Analytics</h1>
  <p class="admin-reports-copy">
    System-wide clinic, service, user, and engagement analytics will appear here soon.
  </p>

  <div class="admin-reports-pill">
    <i class="bi bi-hourglass-split"></i>
    Coming Soon
  </div>

  <div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-light text-primary">
      <i class="bi bi-arrow-left me-2"></i>
      Back to Dashboard
    </a>
  </div>
</section>
@endsection
