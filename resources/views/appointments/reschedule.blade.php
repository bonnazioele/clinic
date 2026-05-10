@extends('layouts.patient-dashboard')

@section('title', 'Reschedule Appointment')

@section('content')
@php
    $appointmentDate = $appointment->appointment_date
        ? \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')
        : now()->toDateString();

    $appointmentTime = $appointment->appointment_time
        ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')
        : '';

    $clinic = $appointment->clinic;
    $service = $appointment->service;
    $doctor = $appointment->doctor;
@endphp

<style>
    .reschedule-page {
        width: min(96%, 1100px);
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }

    .reschedule-hero {
        border-radius: 26px;
        padding: 1.5rem;
        color: #fff;
        background:
            radial-gradient(circle at 90% 20%, rgba(255,255,255,.18), transparent 18%),
            linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
        box-shadow: 0 18px 45px rgba(37, 99, 235, .22);
        margin-bottom: 1rem;
    }

    .reschedule-title {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.25rem);
        font-weight: 900;
        letter-spacing: -.045em;
    }

    .reschedule-subtitle {
        margin: .35rem 0 0;
        opacity: .95;
        font-weight: 650;
    }

    .reschedule-grid {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 1rem;
    }

    .reschedule-card {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .reschedule-card-body {
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

    .form-label {
        color: #0f172a;
        font-weight: 900;
    }

    .form-control {
        min-height: 48px;
        border-radius: 14px;
        border-color: #cbd5e1;
        font-weight: 700;
    }

    .slot-panel {
        border: 1px dashed #bfdbfe;
        background: #eff6ff;
        border-radius: 20px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .slot-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .8rem;
    }

    .slot-btn {
        border: 1px solid #bfdbfe;
        background: #fff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: .65rem .7rem;
        font-weight: 900;
        transition: .15s ease;
    }

    .slot-btn:hover {
        background: #dbeafe;
    }

    .slot-btn.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .slot-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .actions {
        display: flex;
        gap: .65rem;
        flex-wrap: wrap;
        margin-top: 1.1rem;
    }

    .actions .btn {
        border-radius: 14px;
        font-weight: 900;
        padding: .7rem 1rem;
    }

    @media(max-width: 900px) {
        .reschedule-grid {
            grid-template-columns: 1fr;
        }

        .slot-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="reschedule-page">
    <section class="reschedule-hero">
        <h1 class="reschedule-title">Reschedule Appointment</h1>
        <p class="reschedule-subtitle">
            Choose a new available date and time. Your old queue slot will be reopened after rescheduling.
        </p>
    </section>

    <div class="reschedule-grid">
        <section class="reschedule-card">
            <div class="reschedule-card-body">
                <h2 class="section-title">
                    <i class="bi bi-calendar-check"></i>
                    Current Appointment
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
                        <div class="info-label">Current Date & Time</div>
                        <div class="info-value">
                            {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') : '—' }}
                            at
                            {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="reschedule-card">
            <div class="reschedule-card-body">
                <h2 class="section-title">
                    <i class="bi bi-arrow-repeat"></i>
                    New Schedule
                </h2>

                <form method="POST" action="{{ route('appointments.reschedule.update', $appointment) }}" id="rescheduleForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">New Date</label>
                        <input
                            type="date"
                            name="appointment_date"
                            id="appointmentDate"
                            class="form-control @error('appointment_date') is-invalid @enderror"
                            min="{{ now()->toDateString() }}"
                            value="{{ old('appointment_date', $appointmentDate) }}"
                            required>

                        @error('appointment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input
                        type="hidden"
                        name="appointment_time"
                        id="appointmentTime"
                        value="{{ old('appointment_time') }}">

                    @error('appointment_time')
                        <div class="text-danger small fw-bold mb-2">{{ $message }}</div>
                    @enderror

                    <div class="slot-panel">
                        <div class="fw-bold text-primary">
                            <i class="bi bi-clock me-1"></i>
                            Available Time Slots
                        </div>

                        <div class="small text-muted mt-1" id="slotMessage">
                            Select a date to load available slots.
                        </div>

                        <div class="slot-grid" id="slotGrid"></div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i>
                            Save New Schedule
                        </button>

                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('appointmentDate');
    const timeInput = document.getElementById('appointmentTime');
    const slotGrid = document.getElementById('slotGrid');
    const slotMessage = document.getElementById('slotMessage');
    const submitBtn = document.getElementById('submitBtn');

    const availabilityUrl = @json(route('appointments.availability'));
    const clinicId = @json($appointment->clinic_id);
    const doctorId = @json($appointment->doctor_id);
    const serviceId = @json($appointment->service_id);
    const currentDate = @json($appointmentDate);
    const currentTime = @json($appointmentTime);
    const oldSelectedTime = @json(old('appointment_time'));

    function resetSlots(message) {
        slotGrid.innerHTML = '';
        slotMessage.textContent = message || 'No available slots.';
        timeInput.value = '';
        submitBtn.disabled = true;
    }

    function isCurrentSlot(date, time) {
        return date === currentDate && String(time).substring(0, 5) === String(currentTime).substring(0, 5);
    }

    async function loadSlots() {
        const selectedDate = dateInput.value;

        if (!selectedDate) {
            resetSlots('Select a date to load available slots.');
            return;
        }

        resetSlots('Loading available slots...');

        const url = new URL(availabilityUrl, window.location.origin);
        url.searchParams.set('clinic_id', clinicId);
        url.searchParams.set('doctor_id', doctorId);
        url.searchParams.set('service_id', serviceId);
        url.searchParams.set('date', selectedDate);

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            slotGrid.innerHTML = '';

            if (!data.slots || data.slots.length === 0) {
                slotMessage.textContent = data.message || 'No available slots for this date.';
                return;
            }

            slotMessage.textContent = 'Choose one of the available slots below.';

            data.slots.forEach(function (slot) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'slot-btn';
                button.textContent = slot.display || slot.time;

                const unavailable =
                    !slot.available ||
                    slot.occupied ||
                    slot.expired ||
                    isCurrentSlot(selectedDate, slot.time);

                if (unavailable) {
                    button.disabled = true;
                }

                button.addEventListener('click', function () {
                    document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('active'));

                    button.classList.add('active');
                    timeInput.value = slot.time;
                    submitBtn.disabled = false;
                });

                if (
                    oldSelectedTime &&
                    String(oldSelectedTime).substring(0, 5) === String(slot.time).substring(0, 5) &&
                    !unavailable
                ) {
                    button.classList.add('active');
                    timeInput.value = slot.time;
                    submitBtn.disabled = false;
                }

                slotGrid.appendChild(button);
            });
        } catch (error) {
            resetSlots('Unable to load available slots. Please try again.');
        }
    }

    dateInput.addEventListener('change', loadSlots);

    loadSlots();
});
</script>
@endsection
