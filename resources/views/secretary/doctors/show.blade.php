@extends('layouts.app')

@section('title', 'Doctor Profile')

@section('content')
@php
  $user = auth()->user();

  $secretaryClinicRouteValue = request()->route('clinic');
  $secretaryClinicId = null;

  if (is_object($secretaryClinicRouteValue) && isset($secretaryClinicRouteValue->id)) {
      $secretaryClinicId = $secretaryClinicRouteValue->id;
  }

  if (!$secretaryClinicId && is_numeric($secretaryClinicRouteValue)) {
      $secretaryClinicId = $secretaryClinicRouteValue;
  }

  if (!$secretaryClinicId && session('active_clinic_id')) {
      $secretaryClinicId = session('active_clinic_id');
  }

  if (!$secretaryClinicId && isset($user->clinic_id)) {
      $secretaryClinicId = $user->clinic_id;
  }

  if (!$secretaryClinicId && isset($user->clinics) && $user->clinics->count()) {
      $secretaryClinicId = $user->clinics->first()->id;
  }

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) {
          return url($fallback);
      }

      try {
          return route($routeName, $params);
      } catch (\Throwable $firstError) {
          if ($secretaryClinicId) {
              try {
                  return route($routeName, array_merge(['clinic' => $secretaryClinicId], (array) $params));
              } catch (\Throwable $secondError) {
                  return url($fallback);
              }
          }

          return url($fallback);
      }
  };

  $recent = $recent ?? collect();
@endphp

<style>
  .doctor-profile-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .doctor-profile-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255,255,255,.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
  }

  .doctor-profile-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .doctor-profile-title-wrap {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .doctor-profile-avatar {
    width: 62px;
    height: 62px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.7rem;
    font-weight: 900;
    flex: 0 0 62px;
  }

  .doctor-profile-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .doctor-profile-subtitle {
    margin: .3rem 0 0;
    font-size: .95rem;
    font-weight: 650;
    opacity: .94;
  }

  .doctor-profile-actions {
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
  }

  .doctor-profile-actions .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: minmax(290px, .85fr) minmax(0, 1.35fr);
    gap: 1rem;
    align-items: start;
  }

  .profile-card {
    border-radius: 24px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .profile-card-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
  }

  .profile-card-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .profile-card-title i {
    color: #0d6efd;
  }

  .profile-card-body {
    padding: 1.2rem;
  }

  .profile-info-list {
    display: grid;
    gap: .75rem;
  }

  .profile-info-item {
    border-radius: 17px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    padding: .8rem;
  }

  .profile-info-label {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .2rem;
  }

  .profile-info-value {
    color: #0f172a;
    font-size: .88rem;
    font-weight: 850;
    word-break: break-word;
  }

  .tag-list {
    display: flex;
    gap: .35rem;
    flex-wrap: wrap;
  }

  .profile-tag {
    border-radius: 999px;
    padding: .35rem .6rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .72rem;
    font-weight: 900;
  }

  .schedule-table,
  .recent-table {
    margin: 0;
  }

  .schedule-table thead th,
  .recent-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem;
  }

  .schedule-table tbody td,
  .recent-table tbody td {
    padding: .9rem 1rem;
    vertical-align: middle;
  }

  .empty-state {
    text-align: center;
    color: #64748b;
    padding: 2.5rem 1rem;
  }

  .empty-state i {
    color: #94a3b8;
    font-size: 2.8rem;
    display: block;
    margin-bottom: .75rem;
  }

  .recent-card {
    margin-top: 1rem;
  }

  @media (max-width: 1100px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .doctor-profile-page {
      width: 100%;
    }

    .doctor-profile-hero {
      padding: 1rem;
      border-radius: 22px;
    }

    .doctor-profile-actions,
    .doctor-profile-actions .btn {
      width: 100%;
    }
  }
</style>

<div class="doctor-profile-page">
  @include('partials.alerts')

  <section class="doctor-profile-hero">
    <div class="doctor-profile-hero-row">
      <div class="doctor-profile-title-wrap">
        <div class="doctor-profile-avatar">
          {{ strtoupper(substr($doctor->name ?? 'D', 0, 1)) }}
        </div>

        <div>
          <h1 class="doctor-profile-title">Dr. {{ $doctor->name }}</h1>
          <p class="doctor-profile-subtitle">
            Doctor profile, assigned services, availability, and recent appointments.
          </p>
        </div>
      </div>

      <div class="doctor-profile-actions">
        <a href="{{ $secUrl('secretary.doctors.edit', ['doctor' => $doctor->id]) }}"
           class="btn btn-light text-primary">
          <i class="bi bi-pencil me-1"></i>
          Edit
        </a>

        <a href="{{ $secUrl('secretary.doctors.index') }}"
           class="btn btn-outline-light">
          <i class="bi bi-arrow-left me-1"></i>
          Back
        </a>
      </div>
    </div>
  </section>

  <div class="profile-grid">
    <section class="profile-card">
      <div class="profile-card-head">
        <h2 class="profile-card-title">
          <i class="bi bi-person-lines-fill"></i>
          Basic Information
        </h2>
      </div>

      <div class="profile-card-body">
        <div class="profile-info-list">
          <div class="profile-info-item">
            <div class="profile-info-label">Name</div>
            <div class="profile-info-value">Dr. {{ $doctor->name }}</div>
          </div>

          <div class="profile-info-item">
            <div class="profile-info-label">Email</div>
            <div class="profile-info-value">{{ $doctor->email }}</div>
          </div>

          <div class="profile-info-item">
            <div class="profile-info-label">Phone</div>
            <div class="profile-info-value">{{ $doctor->phone ?: 'Not provided' }}</div>
          </div>

          <div class="profile-info-item">
            <div class="profile-info-label">Address</div>
            <div class="profile-info-value">{{ $doctor->address ?: 'Not provided' }}</div>
          </div>

          <div class="profile-info-item">
            <div class="profile-info-label">Clinics</div>
            <div class="tag-list">
              @forelse($doctor->clinics as $c)
                <span class="profile-tag">{{ $c->name }}</span>
              @empty
                <span class="text-muted">None</span>
              @endforelse
            </div>
          </div>

          <div class="profile-info-item">
            <div class="profile-info-label">Services</div>
            <div class="tag-list">
              @forelse($doctor->services as $s)
                <span class="profile-tag">{{ $s->name }}</span>
              @empty
                <span class="text-muted">None</span>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="profile-card">
      <div class="profile-card-head">
        <h2 class="profile-card-title">
          <i class="bi bi-calendar-range"></i>
          Weekly Availability
        </h2>
      </div>

      <div class="profile-card-body p-0">
        @if($scheduleByDay->isEmpty())
          <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h5 class="fw-bold">No availability set</h5>
            <p class="mb-0">This doctor does not have a weekly schedule yet.</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="table schedule-table align-middle">
              <thead>
                <tr>
                  <th>Day</th>
                  <th>Clinic</th>
                  <th>Time Window</th>
                </tr>
              </thead>

              <tbody>
                @foreach($scheduleByDay as $day => $entries)
                  @foreach($entries as $entry)
                    <tr>
                      <td class="fw-bold">
                        {{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$day] }}
                      </td>
                      <td>{{ $entry->clinic->name }}</td>
                      <td>{{ time12($entry->start_time) }} - {{ time12($entry->end_time) }}</td>
                    </tr>
                  @endforeach
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>
  </div>

  <section class="profile-card recent-card">
    <div class="profile-card-head">
      <h2 class="profile-card-title">
        <i class="bi bi-clock-history"></i>
        Recent Appointments
      </h2>
    </div>

    <div class="profile-card-body p-0">
      @if($recent->isEmpty())
        <div class="empty-state">
          <i class="bi bi-calendar-x"></i>
          <h5 class="fw-bold">No recent appointments</h5>
          <p class="mb-0">Appointments handled by this doctor will appear here.</p>
        </div>
      @else
        <div class="table-responsive">
          <table class="table recent-table align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Clinic</th>
                <th>Service</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody>
              @foreach($recent as $a)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->format('M d, Y') }}</td>
                  <td>{{ time12($a->appointment_time) }}</td>
                  <td class="fw-bold">{{ $a->user->name ?? 'Unknown patient' }}</td>
                  <td>{{ $a->clinic->name ?? 'N/A' }}</td>
                  <td>{{ $a->service->name ?? 'N/A' }}</td>
                  <td>
                    @php
                      $status = strtolower($a->status ?? 'scheduled');
                      $badgeClass = 'bg-warning text-dark';

                      if ($status === 'completed') {
                          $badgeClass = 'bg-success';
                      }

                      if ($status === 'cancelled') {
                          $badgeClass = 'bg-danger';
                      }

                      if ($status === 'no_show') {
                          $badgeClass = 'bg-secondary';
                      }
                    @endphp

                    <span class="badge {{ $badgeClass }}">
                      {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </section>
</div>
@endsection
