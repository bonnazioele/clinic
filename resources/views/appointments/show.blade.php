@extends('layouts.patient-dashboard')

@section('title', 'Appointment Details')

@section('content')
@php
    $clinic = $appointment->clinic;
    $service = $appointment->service;
    $doctor = $appointment->doctor;
    $latestQueue = $appointment->queueEntries->first();

    $status = ucfirst(str_replace('_', ' ', $appointment->status));
@endphp

<style>
    .details-page {
        width: min(96%, 1100px);
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }

    .details-hero {
        border-radius: 26px;
        padding: 1.5rem;
        color: #fff;
        background:
            radial-gradient(circle at 90% 20%, rgba(255,255,255,.18), transparent 18%),
            linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
        box-shadow: 0 18px 45px rgba(37, 99, 235, .22);
        margin-bottom: 1rem;
    }

    .details-title {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.25rem);
        font-weight: 900;
        letter-spacing: -.045em;
    }

    .details-subtitle {
        margin: .35rem 0 0;
        opacity: .95;
        font-weight: 650;
    }

    .details-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, .6fr);
        gap: 1rem;
    }

    .details-card {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .details-card-body {
        padding: 1.25rem;
    }

    .section-title {
        margin: 0 0 1rem;
        color: #0f172a;
        font-weight: 900;
        display: flex;
        gap: .5rem;
        align-items: center;
    }

    .info-list {
        display: grid;
        gap: .75rem;
    }

    .info-item {
        border: 1px solid #edf2f7;
        background: #f8fafc;
        border-radius: 18px;
        padding: .85rem;
    }

    .info-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .info-value {
        color: #0f172a;
        font-weight: 900;
        margin-top: .2rem;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        padding: .45rem .75rem;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-weight: 900;
    }

    .actions {
        display: grid;
        gap: .65rem;
    }

    .actions .btn {
        border-radius: 14px;
        font-weight: 900;
        padding: .75rem 1rem;
    }

    @media(max-width: 900px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="details-page">
    <section class="details-hero">
        <h1 class="details-title">Appointment Details</h1>
        <p class="details-subtitle">
            View your appointment schedule, clinic, service, doctor, and queue information.
        </p>
    </section>

    <div class="details-grid">
        <section class="details-card">
            <div class="details-card-body">
                <h2 class="section-title">
                    <i class="bi bi-calendar-check"></i>
                    Appointment Information
                </h2>

                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Clinic</div>
                        <div class="info-value">{{ $clinic->name ?? 'Clinic' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Service</div>
                        <div class="info-value">{{ $service->name ?? 'Service' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Doctor</div>
                        <div class="info-value">{{ $doctor->name ?? 'Doctor' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Date and Time</div>
                        <div class="info-value">
                            {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') : '—' }}
                            at
                            {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : '—' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-pill">
                                <i class="bi bi-circle-fill small"></i>
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="details-card">
            <div class="details-card-body">
                <h2 class="section-title">
                    <i class="bi bi-list-ol"></i>
                    Queue Information
                </h2>

                <div class="info-list mb-3">
                    <div class="info-item">
                        <div class="info-label">Queue Number</div>
                        <div class="info-value">
                            {{ $latestQueue ? '#' . $latestQueue->queue_number : 'Not yet queued' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Queue Status</div>
                        <div class="info-value">
                            {{ $latestQueue ? ucfirst(str_replace('_', ' ', $latestQueue->status)) : '—' }}
                        </div>
                    </div>
                </div>

                <div class="actions">
                    @if(in_array($appointment->status, ['scheduled', 'pending', 'confirmed'], true))
                        <a href="{{ route('appointments.reschedule.edit', $appointment) }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Reschedule
                        </a>
                    @endif

                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to My Appointments
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection