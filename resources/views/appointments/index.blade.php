@extends('layouts.patient-dashboard')

@section('title', 'My Appointments')

@section('content')
@php
    $todayAppointments = $todayAppointments ?? collect();
    $upcomingAppointments = $upcomingAppointments ?? collect();
    $pastAppointments = $pastAppointments ?? collect();

    $activeQueueStatuses = ['waiting', 'called', 'now_serving'];

    $totalActive = $todayAppointments->count() + $upcomingAppointments->count();
    $totalHistory = $pastAppointments->count();

    $formatAppointmentTime = function ($appointment) {
        if (!$appointment->appointment_time) {
            return '—';
        }

        if ($appointment->appointment_time instanceof \Carbon\Carbon) {
            return $appointment->appointment_time->format('g:i A');
        }

        try {
            return \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A');
        } catch (\Exception $e) {
            return $appointment->appointment_time;
        }
    };

    $formatStatusLabel = function ($status) {
        return match ($status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
            default => ucfirst(str_replace('_', ' ', $status ?? 'Unknown')),
        };
    };

    $formatStatusClass = function ($status) {
        return match ($status) {
            'scheduled' => 'status-pill status-scheduled',
            'completed' => 'status-pill status-completed',
            'cancelled' => 'status-pill status-cancelled',
            'no_show' => 'status-pill status-noshow',
            'rescheduled' => 'status-pill status-rescheduled',
            default => 'status-pill status-default',
        };
    };

    $formatQueueStatusLabel = function ($status) {
        return match ($status) {
            'waiting' => 'Waiting',
            'called' => 'Called',
            'now_serving' => 'Now Serving',
            'served' => 'Completed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
            default => ucfirst(str_replace('_', ' ', $status ?? 'Unknown')),
        };
    };

    $formatQueueBadgeClass = function ($status) {
        return match ($status) {
            'waiting' => 'queue-pill queue-waiting',
            'called' => 'queue-pill queue-called',
            'now_serving' => 'queue-pill queue-serving',
            'served', 'completed' => 'queue-pill queue-completed',
            'cancelled' => 'queue-pill queue-cancelled',
            'no_show' => 'queue-pill queue-noshow',
            'rescheduled' => 'queue-pill queue-rescheduled',
            default => 'queue-pill queue-default',
        };
    };

    $getActiveQueueEntry = function ($appointment) use ($activeQueueStatuses) {
        if ($appointment->relationLoaded('queueEntries') && $appointment->queueEntries) {
            return $appointment->queueEntries
                ->whereIn('status', $activeQueueStatuses)
                ->sortByDesc('created_at')
                ->first();
        }

        return null;
    };

    $renderAppointmentCard = function ($appointment, $allowActions = false, $mode = 'default') use (
        $formatAppointmentTime,
        $formatStatusLabel,
        $formatStatusClass,
        $formatQueueStatusLabel,
        $formatQueueBadgeClass,
        $getActiveQueueEntry
    ) {
        $statusLabel = $formatStatusLabel($appointment->status);
        $statusClass = $formatStatusClass($appointment->status);
        $time = $formatAppointmentTime($appointment);

        $date = $appointment->appointment_date
            ? $appointment->appointment_date->format('M d, Y')
            : '—';

        $day = $appointment->appointment_date
            ? $appointment->appointment_date->format('D')
            : '—';

        $clinicName = $appointment->clinic->name ?? 'Clinic';
        $serviceName = $appointment->service->name ?? 'Service not specified';
        $doctorName = $appointment->doctor->name ?? 'Not assigned';
        $activeQueueEntry = $getActiveQueueEntry($appointment);

        ob_start();
@endphp

<article class="appointment-card {{ $mode === 'today' ? 'appointment-card-featured' : '' }}">
    <div class="appointment-date-tile">
        <span>{{ $day }}</span>
        <strong>{{ $appointment->appointment_date ? $appointment->appointment_date->format('d') : '—' }}</strong>
        <small>{{ $appointment->appointment_date ? $appointment->appointment_date->format('M') : '' }}</small>
    </div>

    <div class="appointment-card-main">
        <div class="appointment-card-top">
            <div>
                <div class="appointment-clinic">
                    {{ $clinicName }}
                </div>

                <div class="appointment-service">
                    <i class="bi bi-clipboard2-pulse"></i>
                    {{ $serviceName }}
                </div>
            </div>

            <span class="{{ $statusClass }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="appointment-details-row">
            <div class="appointment-detail">
                <span>
                    <i class="bi bi-clock"></i>
                    Time
                </span>
                <strong>{{ $time }}</strong>
            </div>

            <div class="appointment-detail">
                <span>
                    <i class="bi bi-person-badge"></i>
                    Doctor
                </span>
                <strong>{{ $doctorName }}</strong>
            </div>

            <div class="appointment-detail">
                <span>
                    <i class="bi bi-calendar-event"></i>
                    Date
                </span>
                <strong>{{ $date }}</strong>
            </div>
        </div>

        @if($activeQueueEntry)
            <div class="queue-status-card">
                <div class="queue-number">
                    <span>Queue Number</span>
                    <strong>#{{ $activeQueueEntry->queue_number }}</strong>
                </div>

                <div class="queue-current-status">
                    <span>Queue Status</span>
                    <strong class="{{ $formatQueueBadgeClass($activeQueueEntry->status) }}">
                        {{ $formatQueueStatusLabel($activeQueueEntry->status) }}
                    </strong>
                </div>
            </div>
        @endif

        @if($allowActions && $appointment->status === 'scheduled')
            <div class="appointment-actions">
                @if($activeQueueEntry && Route::has('queue.status.entry'))
                    <a href="{{ route('queue.status.entry', $activeQueueEntry) }}" class="btn action-primary">
                        <i class="bi bi-eye"></i>
                        View Queue Status
                    </a>
                @endif

                @if(Route::has('appointments.edit'))
                    <a href="{{ route('appointments.edit', $appointment) }}" class="btn action-light">
                        <i class="bi bi-pencil-square"></i>
                        Edit
                    </a>
                @endif

                @if(Route::has('appointments.destroy'))
                    <form method="POST"
                          action="{{ route('appointments.destroy', $appointment) }}"
                          onsubmit="return confirm('Cancel this appointment? It will still appear in your history.');">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn action-danger">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</article>

@php
        return ob_get_clean();
    };
@endphp

<style>
    .appointments-page {
        width: min(96%, 1500px);
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }

    .appointments-shell {
        display: grid;
        gap: 1.1rem;
    }

    .appointments-hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        padding: 1.5rem;
        color: #fff;
        background:
            radial-gradient(circle at 8% 18%, rgba(255,255,255,.26), transparent 18%),
            radial-gradient(circle at 88% 14%, rgba(125,211,252,.28), transparent 20%),
            linear-gradient(135deg, #0f52ba 0%, #0d6efd 48%, #1d4ed8 100%);
        box-shadow: 0 24px 60px rgba(37, 99, 235, .28);
    }

    .appointments-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -110px auto;
        width: 280px;
        height: 280px;
        border-radius: 999px;
        background: rgba(255,255,255,.13);
    }

    .appointments-hero-content {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .appointments-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .7rem;
        margin-bottom: .7rem;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.22);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .appointments-hero h2 {
        font-weight: 900;
        font-size: clamp(1.55rem, 2vw, 2.4rem);
        margin-bottom: .35rem;
    }

    .appointments-hero p {
        max-width: 650px;
        margin-bottom: 0;
        opacity: .92;
        line-height: 1.6;
    }

    .hero-actions {
        display: flex;
        justify-content: flex-end;
        gap: .65rem;
        flex-wrap: wrap;
    }

    .hero-book-btn {
        border: 0;
        border-radius: 16px;
        padding: .8rem 1rem;
        background: #fff;
        color: #0d6efd;
        font-weight: 900;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .16);
        white-space: nowrap;
    }

    .hero-book-btn:hover {
        color: #0b5ed7;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .appointment-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: .9rem;
        border-radius: 24px;
        padding: 1rem;
        background: rgba(255,255,255,.96);
        border: 1px solid rgba(226,232,240,.95);
        box-shadow: 0 14px 35px rgba(15,23,42,.07);
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 18px;
        font-size: 1.35rem;
        color: #0d6efd;
        background: rgba(13,110,253,.1);
    }

    .summary-icon.today {
        color: #047857;
        background: rgba(16,185,129,.12);
    }

    .summary-icon.history {
        color: #7c3aed;
        background: rgba(124,58,237,.12);
    }

    .summary-text span {
        display: block;
        color: #64748b;
        font-size: .82rem;
        font-weight: 800;
    }

    .summary-text strong {
        display: block;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .appointments-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(360px, .8fr);
        gap: 1rem;
        align-items: start;
    }

    .appointments-main-column,
    .appointments-side-column {
        display: grid;
        gap: 1rem;
    }

    .appointment-section {
        background: rgba(255,255,255,.98);
        border: 1px solid rgba(226,232,240,.95);
        border-radius: 28px;
        box-shadow: 0 16px 40px rgba(15,23,42,.07);
        overflow: hidden;
    }

    .appointment-section-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid rgba(226,232,240,.9);
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        background:
            linear-gradient(135deg, rgba(248,250,252,.98), rgba(255,255,255,.98));
    }

    .section-title-wrap {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 16px;
        color: #0d6efd;
        background: rgba(13,110,253,.1);
        flex: 0 0 auto;
    }

    .section-icon.today {
        color: #047857;
        background: rgba(16,185,129,.12);
    }

    .section-icon.history {
        color: #7c3aed;
        background: rgba(124,58,237,.12);
    }

    .appointment-section-title {
        margin: 0;
        font-size: 1.03rem;
        font-weight: 900;
        color: #0f172a;
    }

    .appointment-section-subtitle {
        margin: .15rem 0 0;
        font-size: .83rem;
        color: #64748b;
    }

    .appointment-count {
        border-radius: 999px;
        padding: .42rem .8rem;
        font-size: .78rem;
        font-weight: 900;
        color: #0d6efd;
        background: rgba(13,110,253,.1);
        white-space: nowrap;
    }

    .appointment-list {
        padding: 1rem;
        display: grid;
        gap: .85rem;
    }

    .appointment-card {
        display: grid;
        grid-template-columns: 74px minmax(0, 1fr);
        gap: .9rem;
        border: 1px solid rgba(226,232,240,.95);
        border-radius: 24px;
        padding: .95rem;
        background:
            linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 10px 28px rgba(15,23,42,.055);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .appointment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 36px rgba(15,23,42,.09);
        border-color: rgba(13,110,253,.2);
    }

    .appointment-card-featured {
        border-color: rgba(16,185,129,.24);
        background:
            linear-gradient(180deg, rgba(240,253,250,.95) 0%, #ffffff 58%);
    }

    .appointment-date-tile {
        min-height: 92px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        align-content: center;
        background: #f1f5f9;
        border: 1px solid rgba(226,232,240,.95);
        color: #0f172a;
        text-align: center;
    }

    .appointment-date-tile span {
        font-size: .72rem;
        color: #64748b;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .appointment-date-tile strong {
        font-size: 1.65rem;
        font-weight: 950;
        line-height: 1;
        color: #0f172a;
    }

    .appointment-date-tile small {
        margin-top: .12rem;
        color: #64748b;
        font-weight: 800;
    }

    .appointment-card-main {
        min-width: 0;
    }

    .appointment-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .appointment-clinic {
        font-weight: 950;
        font-size: 1.05rem;
        color: #0f172a;
        margin-bottom: .25rem;
    }

    .appointment-service {
        display: flex;
        align-items: center;
        gap: .4rem;
        color: #64748b;
        font-size: .9rem;
        line-height: 1.4;
    }

    .appointment-details-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .85rem;
    }

    .appointment-detail {
        border-radius: 16px;
        background: #f8fafc;
        padding: .72rem;
        border: 1px solid rgba(226,232,240,.9);
    }

    .appointment-detail span {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .73rem;
        color: #64748b;
        font-weight: 850;
        margin-bottom: .2rem;
    }

    .appointment-detail strong {
        display: block;
        color: #0f172a;
        font-weight: 900;
        font-size: .9rem;
        overflow-wrap: anywhere;
    }

    .status-pill,
    .queue-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        border-radius: 999px;
        padding: .35rem .7rem;
        font-size: .74rem;
        line-height: 1;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-scheduled {
        color: #0d6efd;
        background: rgba(13,110,253,.1);
    }

    .status-completed {
        color: #047857;
        background: rgba(16,185,129,.12);
    }

    .status-cancelled {
        color: #b91c1c;
        background: rgba(239,68,68,.12);
    }

    .status-noshow {
        color: #111827;
        background: rgba(17,24,39,.12);
    }

    .status-rescheduled {
        color: #92400e;
        background: rgba(245,158,11,.16);
    }

    .status-default {
        color: #475569;
        background: rgba(100,116,139,.12);
    }

    .queue-status-card {
        margin-top: .85rem;
        padding: .85rem;
        border-radius: 20px;
        border: 1px solid rgba(34,197,94,.22);
        background:
            linear-gradient(135deg, rgba(34,197,94,.09), rgba(240,253,250,.8));
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .85rem;
        align-items: center;
    }

    .queue-number span,
    .queue-current-status span {
        display: block;
        font-size: .72rem;
        font-weight: 900;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .2rem;
    }

    .queue-number strong {
        color: #047857;
        font-size: 1.3rem;
        font-weight: 950;
    }

    .queue-waiting {
        color: #475569;
        background: rgba(100,116,139,.13);
    }

    .queue-called {
        color: #075985;
        background: rgba(14,165,233,.15);
    }

    .queue-serving {
        color: #047857;
        background: rgba(16,185,129,.15);
    }

    .queue-completed {
        color: #047857;
        background: rgba(16,185,129,.13);
    }

    .queue-cancelled {
        color: #b91c1c;
        background: rgba(239,68,68,.12);
    }

    .queue-noshow {
        color: #111827;
        background: rgba(17,24,39,.12);
    }

    .queue-rescheduled {
        color: #92400e;
        background: rgba(245,158,11,.16);
    }

    .queue-default {
        color: #475569;
        background: rgba(100,116,139,.12);
    }

    .appointment-actions {
        display: flex;
        gap: .55rem;
        flex-wrap: wrap;
        margin-top: .95rem;
    }

    .appointment-actions form {
        margin: 0;
    }

    .appointment-actions .btn {
        border-radius: 14px;
        min-height: 38px;
        padding: .48rem .78rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .42rem;
        font-weight: 900;
        font-size: .83rem;
    }

    .action-primary {
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, #16a34a, #059669);
        box-shadow: 0 10px 22px rgba(22,163,74,.2);
    }

    .action-primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(22,163,74,.26);
    }

    .action-light {
        border: 1px solid rgba(13,110,253,.18);
        color: #0d6efd;
        background: rgba(13,110,253,.08);
    }

    .action-light:hover {
        color: #0b5ed7;
        background: rgba(13,110,253,.12);
    }

    .action-danger {
        border: 1px solid rgba(220,38,38,.18);
        color: #dc2626;
        background: rgba(220,38,38,.08);
    }

    .action-danger:hover {
        color: #b91c1c;
        background: rgba(220,38,38,.12);
    }

    .empty-appointments {
        text-align: center;
        padding: 2.4rem 1rem;
        color: #64748b;
    }

    .empty-appointments-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin: 0 auto .75rem;
        border-radius: 22px;
        color: #0d6efd;
        background: rgba(13,110,253,.1);
        font-size: 1.65rem;
    }

    .empty-appointments strong {
        display: block;
        color: #0f172a;
        font-weight: 900;
        margin-bottom: .2rem;
    }

    .empty-appointments p {
        max-width: 340px;
        margin: 0 auto;
        line-height: 1.5;
    }

    @media (max-width: 1200px) {
        .appointments-layout {
            grid-template-columns: 1fr;
        }

        .appointments-side-column {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 850px) {
        .appointments-page {
            width: 100%;
            padding-inline: .2rem;
        }

        .appointments-hero-content {
            grid-template-columns: 1fr;
        }

        .hero-actions {
            justify-content: flex-start;
        }

        .appointment-summary-grid {
            grid-template-columns: 1fr;
        }

        .appointment-section-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .appointment-card {
            grid-template-columns: 1fr;
        }

        .appointment-date-tile {
            min-height: auto;
            grid-template-columns: auto auto auto;
            justify-content: start;
            gap: .35rem;
            padding: .75rem;
            text-align: left;
        }

        .appointment-date-tile strong {
            font-size: 1.2rem;
        }

        .appointment-details-row {
            grid-template-columns: 1fr;
        }

        .queue-status-card {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="appointments-page">
    @include('partials.alerts')

    <div class="appointments-shell">
        <section class="appointments-hero">
            <div class="appointments-hero-content">
                <div>
                    <span class="appointments-eyebrow">
                        <i class="bi bi-heart-pulse"></i>
                        Patient Appointments
                    </span>

                    <h2>Manage your clinic visits clearly.</h2>

                    <p>
                        Today’s appointments, future schedules, and appointment history are separated
                        so queue status and past records are easier to track.
                    </p>
                </div>

                <div class="hero-actions">
                    @if(Route::has('appointments.create'))
                        <a href="{{ route('appointments.create') }}" class="btn hero-book-btn">
                            <i class="bi bi-plus-circle me-1"></i>
                            Book Appointment
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section class="appointment-summary-grid">
            <div class="summary-card">
                <div class="summary-icon today">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <div class="summary-text">
                    <span>Today</span>
                    <strong>{{ $todayAppointments->count() }}</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="summary-text">
                    <span>Active / Upcoming</span>
                    <strong>{{ $totalActive }}</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon history">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="summary-text">
                    <span>History</span>
                    <strong>{{ $totalHistory }}</strong>
                </div>
            </div>
        </section>

        <div class="appointments-layout">
            <main class="appointments-main-column">
                <section class="appointment-section">
                    <div class="appointment-section-header">
                        <div class="section-title-wrap">
                            <div class="section-icon today">
                                <i class="bi bi-calendar-day"></i>
                            </div>

                            <div>
                                <h4 class="appointment-section-title">Today’s Appointments</h4>
                                <p class="appointment-section-subtitle">
                                    Queue status stays visible while waiting, called, or now serving.
                                </p>
                            </div>
                        </div>

                        <span class="appointment-count">
                            {{ $todayAppointments->count() }} Today
                        </span>
                    </div>

                    <div class="appointment-list">
                        @forelse($todayAppointments as $appointment)
                            {!! $renderAppointmentCard($appointment, true, 'today') !!}
                        @empty
                            <div class="empty-appointments">
                                <div class="empty-appointments-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <strong>No appointments today.</strong>
                                <p>Your appointments for today will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="appointment-section">
                    <div class="appointment-section-header">
                        <div class="section-title-wrap">
                            <div class="section-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>

                            <div>
                                <h4 class="appointment-section-title">Upcoming Appointments</h4>
                                <p class="appointment-section-subtitle">
                                    Only future scheduled appointments are shown here.
                                </p>
                            </div>
                        </div>

                        <span class="appointment-count">
                            {{ $upcomingAppointments->count() }} Upcoming
                        </span>
                    </div>

                    <div class="appointment-list">
                        @forelse($upcomingAppointments as $appointment)
                            {!! $renderAppointmentCard($appointment, true, 'upcoming') !!}
                        @empty
                            <div class="empty-appointments">
                                <div class="empty-appointments-icon">
                                    <i class="bi bi-calendar-plus"></i>
                                </div>
                                <strong>No upcoming appointments.</strong>
                                <p>Future appointments will appear here after booking.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>

            <aside class="appointments-side-column">
                <section class="appointment-section">
                    <div class="appointment-section-header">
                        <div class="section-title-wrap">
                            <div class="section-icon history">
                                <i class="bi bi-clock-history"></i>
                            </div>

                            <div>
                                <h4 class="appointment-section-title">Past / History</h4>
                                <p class="appointment-section-subtitle">
                                    Completed, cancelled, rescheduled, and no-show records stay here.
                                </p>
                            </div>
                        </div>

                        <span class="appointment-count">
                            {{ $pastAppointments->count() }} History
                        </span>
                    </div>

                    <div class="appointment-list">
                        @forelse($pastAppointments as $appointment)
                            {!! $renderAppointmentCard($appointment, false, 'history') !!}
                        @empty
                            <div class="empty-appointments">
                                <div class="empty-appointments-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <strong>No appointment history yet.</strong>
                                <p>Completed, cancelled, rescheduled, and no-show appointments will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection