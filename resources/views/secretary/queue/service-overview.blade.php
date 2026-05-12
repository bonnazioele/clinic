@extends('layouts.app')

@section('title', $serviceName . ' Queue')

@section('content')
<style>
  .service-overview-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .service-summary-shell {
    margin-bottom: 1.25rem;
  }

  .service-overview-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
  }

  .service-overview-hero__row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .service-overview-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    min-width: min(100%, 340px);
  }

  .service-overview-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .service-overview-hero h1 {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -0.045em;
  }

  .service-overview-hero p {
    max-width: 560px;
    margin: 0.3rem 0 0;
    font-size: 0.95rem;
    font-weight: 650;
    opacity: 0.94;
  }

  .service-overview-back {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1rem;
    color: #ffffff;
    text-decoration: none;
    font-weight: 900;
    font-size: 0.86rem;
  }

  .service-overview-back:hover {
    color: #ffffff;
    text-decoration: underline;
  }

  .service-stats-grid {
    display: flex;
    justify-content: flex-end;
    align-items: stretch;
    gap: 0.85rem;
    flex: 0 0 auto;
    margin-left: auto;
  }

  .service-stat-card {
    min-width: 168px;
    min-height: 92px;
    border-radius: 16px;
    padding: 0.95rem 1rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
  }

  .service-stat-card::after {
    content: "";
    position: absolute;
    right: -20px;
    bottom: -28px;
    width: 92px;
    height: 92px;
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.14);
    transform: rotate(4deg);
  }

  .service-stat-card--yellow {
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
  }

  .service-stat-card--blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .service-stat-card--green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .service-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.8rem;
  }

  .service-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 15px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.3rem;
    flex: 0 0 46px;
  }

  .service-stat-value {
    font-size: 1.75rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.055em;
    margin-bottom: 0.3rem;
  }

  .service-stat-title {
    font-size: 0.84rem;
    font-weight: 900;
  }

  .service-stat-sub {
    display: none;
  }

  .service-workspace-card {
    border-radius: 24px;
    border: 1px solid rgba(191, 219, 254, 0.95);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 24px 60px rgba(37, 99, 235, 0.13);
    padding: 1.6rem;
    position: relative;
    overflow: hidden;
  }

  .service-workspace-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 5px;
    background: linear-gradient(90deg, #0d6efd, #2bbf6a, #ffc107);
  }

  .service-workspace-card > * {
    position: relative;
    z-index: 1;
  }

  .service-workspace-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1.25rem;
  }

  .service-workspace-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.5rem, 2.2vw, 2rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .service-workspace-title i {
    color: #0d6efd;
  }

  .service-workspace-subtitle {
    margin-top: 0.25rem;
    color: #64748b;
    font-size: 0.98rem;
    font-weight: 600;
  }

  .service-workspace-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.45rem 0.75rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.78rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .doctor-service-card {
    min-height: 245px;
    height: 100%;
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.95);
    padding: 1.25rem 1.3rem;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
  }

  .doctor-service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
  }

  .doctor-service-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.85rem;
    margin-bottom: 1rem;
  }

  .doctor-service-card__profile {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 0;
  }

  .doctor-service-card__avatar {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    flex: 0 0 52px;
    object-fit: cover;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.45rem;
  }

  .doctor-service-card__name {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.2;
  }

  .doctor-service-card__meta {
    margin: 0.2rem 0 0;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .doctor-service-card__status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.38rem 0.62rem;
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
    font-size: 0.72rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .doctor-service-card__status--inactive {
    background: #f8fafc;
    color: #64748b;
    border-color: #e2e8f0;
  }

  .doctor-service-card__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
    margin-top: auto;
  }

  .doctor-service-card__stat {
    border-radius: 16px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    padding: 0.8rem;
  }

  .doctor-service-card__stat-label {
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .doctor-service-card__stat-value {
    margin-top: 0.22rem;
    color: #0f172a;
    font-size: 1.45rem;
    font-weight: 900;
  }

  .doctor-service-card__stat-value--primary {
    color: #0d6efd;
  }

  .doctor-service-card__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    width: 100%;
    border-radius: 12px;
    padding: 0.62rem 0.9rem;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 900;
  }

  .doctor-service-card__btn:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #0b5ed7, #0d6efd);
  }

  .doctor-service-card__btn--disabled {
    background: #f1f5f9;
    color: #94a3b8;
    border: 1px solid #e2e8f0;
    pointer-events: none;
  }

  .service-empty-panel {
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
    padding: 1.6rem 1rem;
    text-align: center;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
  }

  .service-empty-panel__icon {
    width: 54px;
    height: 54px;
    margin: 0 auto 0.75rem;
    display: grid;
    place-items: center;
    border-radius: 17px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 1.5rem;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.11);
  }

  @media (max-width: 992px) {
    .service-stats-grid {
      width: 100%;
      justify-content: flex-start;
      flex-wrap: wrap;
      margin-left: 0;
    }
  }

  @media (max-width: 768px) {
    .service-overview-page {
      width: 100%;
    }

    .service-overview-hero,
    .service-workspace-card {
      border-radius: 22px;
    }

    .service-workspace-header {
      flex-direction: column;
      align-items: stretch;
    }
  }

  @media (max-width: 520px) {
    .service-stats-grid {
      display: grid;
      grid-template-columns: 1fr;
    }

    .doctor-service-card__header {
      flex-direction: column;
    }
  }
</style>

<div class="service-overview-page">
  <section class="service-summary-shell">
    <div class="service-overview-hero">
      <div class="service-overview-hero__row">
        <div class="service-overview-title-wrap">
          <div class="service-overview-title-icon">
            <i class="bi bi-clipboard2-pulse"></i>
          </div>

          <div>
            <h1>{{ $serviceName }}</h1>
            <p>{{ $serviceDescription ?: 'Monitor doctors, patients waiting, and active queue movement for this service.' }}</p>
          </div>
        </div>

        <div class="service-stats-grid">
          <div class="service-stat-card service-stat-card--yellow">
            <div class="service-stat-content">
              <div class="service-stat-icon">
                <i class="bi bi-hourglass-split"></i>
              </div>
              <div>
                <div class="service-stat-value">{{ number_format($waitingCount ?? 0) }}</div>
                <div class="service-stat-title">Waiting</div>
                <p class="service-stat-sub">Patients queued today</p>
              </div>
            </div>
          </div>

          <div class="service-stat-card service-stat-card--blue">
            <div class="service-stat-content">
              <div class="service-stat-icon">
                <i class="bi bi-person-check"></i>
              </div>
              <div>
                <div class="service-stat-value">{{ number_format($nowServing ?? 0) }}</div>
                <div class="service-stat-title">Now Serving</div>
                <p class="service-stat-sub">Active patient encounters</p>
              </div>
            </div>
          </div>

          <div class="service-stat-card service-stat-card--green">
            <div class="service-stat-content">
              <div class="service-stat-icon">
                <i class="bi bi-person-badge"></i>
              </div>
              <div>
                <div class="service-stat-value">{{ number_format($activeDoctors ?? 0) }}</div>
                <div class="service-stat-title">Doctors</div>
                <p class="service-stat-sub">Assigned active doctors</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="service-workspace-card">
    <div class="service-workspace-header">
      <div>
        <h2 class="service-workspace-title">
          <i class="bi bi-people-fill"></i>
          Doctor Lanes
        </h2>
        <div class="service-workspace-subtitle">
          Open a doctor lane to manage the active queue for this service.
        </div>
      </div>

      <span class="service-workspace-pill">
        <i class="bi bi-check2-circle"></i>
        {{ number_format($completedToday ?? 0) }} completed today
      </span>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
      @forelse ($doctors as $doctor)
        @php
          $doctorStatus = $doctor->status ?? $doctor['status'] ?? '';
          $isActive = $doctorStatus === 'active';
          $doctorId = $doctor->doctor_id ?? $doctor['doctor_id'];
          $doctorName = $doctor->name ?? $doctor['name'];
          $avatarUrl = $doctor->avatar_url ?? $doctor['avatar_url'] ?? '';
          $specialty = $doctor->specialty ?? $doctor['specialty'] ?? 'Doctor';
          $nowServingValue = $doctor->now_serving ?? $doctor['now_serving'] ?? '---';
          $waitingValue = $doctor->waiting ?? $doctor['waiting'] ?? 0;
        @endphp
        <div class="col">
          <article class="doctor-service-card">
            <div class="doctor-service-card__header">
              <div class="doctor-service-card__profile">
                @if ($avatarUrl)
                  <img class="doctor-service-card__avatar" src="{{ $avatarUrl }}" alt="{{ $doctorName }}">
                @else
                  <span class="doctor-service-card__avatar">
                    <i class="bi bi-person-circle"></i>
                  </span>
                @endif
                <div>
                  <h3 class="doctor-service-card__name">Dr. {{ $doctorName }}</h3>
                  <p class="doctor-service-card__meta">{{ $specialty }}</p>
                </div>
              </div>
              <span class="doctor-service-card__status {{ $isActive ? '' : 'doctor-service-card__status--inactive' }}">
                <i class="bi {{ $isActive ? 'bi-circle-fill' : 'bi-pause-circle' }}"></i>
                {{ $isActive ? 'Active' : 'Unavailable' }}
              </span>
            </div>

            <div class="doctor-service-card__stats">
              <div class="doctor-service-card__stat">
                <div class="doctor-service-card__stat-label">Now Serving</div>
                <div class="doctor-service-card__stat-value doctor-service-card__stat-value--primary">
                  {{ $isActive ? $nowServingValue : '---' }}
                </div>
              </div>
              <div class="doctor-service-card__stat">
                <div class="doctor-service-card__stat-label">Waiting</div>
                <div class="doctor-service-card__stat-value">{{ number_format($waitingValue) }}</div>
              </div>
            </div>

            <a
              class="doctor-service-card__btn {{ $isActive ? '' : 'doctor-service-card__btn--disabled' }}"
              href="{{ $isActive
                ? route('secretary.services.doctors.queue', [
                    'service_id' => $serviceId,
                    'doctor_id' => $doctorId,
                  ])
                : '#' }}"
              {{ $isActive ? '' : 'aria-disabled=true' }}>
              {{ $isActive ? 'Open Queue' : 'Queue Unavailable' }}
              @if ($isActive)
                <i class="bi bi-arrow-right"></i>
              @endif
            </a>
          </article>
        </div>
      @empty
        <div class="col-12">
          <div class="service-empty-panel">
            <div class="service-empty-panel__icon">
              <i class="bi bi-person-plus"></i>
            </div>
            No active doctors are assigned to this service yet.
          </div>
        </div>
      @endforelse
    </div>
  </section>
</div>
@endsection

@push('scripts')
  @include('partials.secretary-doctor-served-toast')
@endpush
