@extends('layouts.app')

@section('title', 'Owner Dashboard')

@section('content')
<style>
  .owner-page {
    width: 96%;
    max-width: 1380px;
    margin: 0 auto;
    padding: 1rem 0 2rem;
  }

  .owner-hero {
    border-radius: 30px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
  }

  .owner-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .owner-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .owner-title-icon,
  .owner-stat-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .owner-title {
    margin: 0;
    font-size: clamp(1.7rem, 3vw, 2.4rem);
    font-weight: 950;
    letter-spacing: -0.045em;
  }

  .owner-subtitle {
    margin: 0.35rem 0 0;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 650;
  }

  .owner-clinic-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border-radius: 999px;
    padding: 0.6rem 0.85rem;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-weight: 900;
  }

  .owner-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .owner-stat-card,
  .owner-panel {
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
  }

  .owner-stat-card {
    padding: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .owner-stat-icon {
    width: 52px;
    height: 52px;
    color: #0d6efd;
    background: #eff6ff;
  }

  .owner-stat-value {
    color: #0f172a;
    font-size: 2rem;
    font-weight: 950;
    line-height: 1;
    letter-spacing: -0.05em;
  }

  .owner-stat-label {
    margin-top: 0.35rem;
    color: #64748b;
    font-weight: 850;
  }

  .owner-panel {
    padding: 1.15rem;
  }

  .owner-panel-title {
    margin: 0 0 0.75rem;
    color: #0f172a;
    font-weight: 950;
  }

  .owner-action {
    border-radius: 16px;
    padding: 0.8rem 1rem;
    font-weight: 900;
  }

  @media (max-width: 1100px) {
    .owner-stats {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 640px) {
    .owner-page {
      width: 100%;
    }

    .owner-stats {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="owner-page">
  <section class="owner-hero">
    <div class="owner-hero-row">
      <div class="owner-title-wrap">
        <span class="owner-title-icon">
          <i class="bi bi-building-check"></i>
        </span>

        <div>
          <h1 class="owner-title">Owner Dashboard</h1>
          <p class="owner-subtitle">Monitor staff, services, and today's activity for your clinic.</p>
        </div>
      </div>

      <span class="owner-clinic-pill">
        <i class="bi bi-hospital"></i>
        {{ $clinic->name }}
      </span>
    </div>
  </section>

  <section class="owner-stats">
    <article class="owner-stat-card">
      <span class="owner-stat-icon"><i class="bi bi-person-badge"></i></span>
      <div>
        <div class="owner-stat-value">{{ number_format($metrics['doctors']) }}</div>
        <div class="owner-stat-label">Doctors</div>
      </div>
    </article>

    <article class="owner-stat-card">
      <span class="owner-stat-icon"><i class="bi bi-person-workspace"></i></span>
      <div>
        <div class="owner-stat-value">{{ number_format($metrics['secretaries']) }}</div>
        <div class="owner-stat-label">Secretaries</div>
      </div>
    </article>

    <article class="owner-stat-card">
      <span class="owner-stat-icon"><i class="bi bi-clipboard2-pulse"></i></span>
      <div>
        <div class="owner-stat-value">{{ number_format($metrics['services']) }}</div>
        <div class="owner-stat-label">Services</div>
      </div>
    </article>

    <article class="owner-stat-card">
      <span class="owner-stat-icon"><i class="bi bi-calendar2-check"></i></span>
      <div>
        <div class="owner-stat-value">{{ number_format($metrics['appointments_today']) }}</div>
        <div class="owner-stat-label">Appointments Today</div>
      </div>
    </article>
  </section>

  <section class="owner-panel">
    <h2 class="owner-panel-title h5">Quick Actions</h2>
    <a href="{{ route('owner.staff.index') }}" class="btn btn-outline-primary owner-action">
      <i class="bi bi-people me-1"></i>
      Manage Staff
    </a>
  </section>
</div>
@endsection
