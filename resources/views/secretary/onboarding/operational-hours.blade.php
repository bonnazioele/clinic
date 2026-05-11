@extends('layouts.app')

@section('title', 'Configure Operational Hours')

@section('content')
<style>
    .onboarding-page {
        width: min(96%, 1150px);
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }

    .onboarding-hero {
        border-radius: 28px;
        padding: 1.6rem;
        color: #fff;
        background:
            radial-gradient(circle at 90% 20%, rgba(255,255,255,.18), transparent 18%),
            linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
        box-shadow: 0 18px 45px rgba(37, 99, 235, .22);
        margin-bottom: 1rem;
    }

    .onboarding-steps {
        display: flex;
        gap: .65rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .step-pill {
        border-radius: 999px;
        padding: .48rem .8rem;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.22);
        font-size: .78rem;
        font-weight: 900;
    }

    .step-pill.active {
        background: #fff;
        color: #0d6efd;
    }

    .onboarding-title {
        margin: 0;
        font-size: clamp(1.55rem, 3vw, 2.3rem);
        font-weight: 950;
        letter-spacing: -.055em;
    }

    .onboarding-subtitle {
        margin: .35rem 0 0;
        font-weight: 650;
        opacity: .95;
    }

    .hours-card {
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .hours-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #edf2f7;
        background: #f8fafc;
    }

    .hours-card-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 950;
    }

    .hours-card-body {
        padding: 1.25rem;
    }

    .hours-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 .75rem;
    }

    .hours-table th {
        color: #64748b;
        font-size: .75rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 0 .75rem;
        white-space: nowrap;
    }

    .hours-table td {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: .85rem .75rem;
        vertical-align: middle;
    }

    .hours-table td:first-child {
        border-left: 1px solid #e2e8f0;
        border-radius: 18px 0 0 18px;
    }

    .hours-table td:last-child {
        border-right: 1px solid #e2e8f0;
        border-radius: 0 18px 18px 0;
    }

    .day-name {
        color: #0f172a;
        font-weight: 950;
        min-width: 120px;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border-color: #cbd5e1;
        font-weight: 700;
        min-height: 42px;
    }

    .form-check-input {
        width: 2.5rem;
        height: 1.35rem;
    }

    .hours-cell-muted {
        opacity: .45;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: .7rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }

    .actions .btn {
        border-radius: 14px;
        font-weight: 900;
        padding: .75rem 1.1rem;
    }

    .help-box {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 18px;
        padding: .9rem 1rem;
        font-weight: 700;
    }

    @media(max-width: 900px) {
        .hours-table,
        .hours-table thead,
        .hours-table tbody,
        .hours-table tr,
        .hours-table th,
        .hours-table td {
            display: block;
            width: 100%;
        }

        .hours-table thead {
            display: none;
        }

        .hours-table tr {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: .75rem;
            margin-bottom: .75rem;
            background: #f8fafc;
        }

        .hours-table td,
        .hours-table td:first-child,
        .hours-table td:last-child {
            border: 0;
            border-radius: 0;
            padding: .45rem 0;
        }

        .hours-table td::before {
            content: attr(data-label);
            display: block;
            color: #64748b;
            font-size: .72rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .25rem;
        }
    }
</style>

<div class="onboarding-page">
    <section class="onboarding-hero">
        <h1 class="onboarding-title">Configure Operational Hours</h1>
        <p class="onboarding-subtitle">
            Set when {{ $clinic->name }} is open. Use 24 hours for days when the clinic never closes.
        </p>

        <div class="onboarding-steps">
            <span class="step-pill">1. Change Password</span>
            <span class="step-pill active">2. Operational Hours</span>
            <span class="step-pill">3. Clinic Ready</span>
        </div>
    </section>

    @if(session('status'))
        <div class="alert alert-success rounded-4 fw-bold">{{ session('status') }}</div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning rounded-4 fw-bold">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4 fw-bold">
            Please check the form. At least one open day is required. For normal open days, closing time must be after opening time. For 24-hour days, time fields are not required.
        </div>
    @endif

    <form method="POST" action="{{ route('secretary.onboarding.operational-hours.store') }}" class="hours-card">
        @csrf

        <div class="hours-card-header">
            <div>
                <h2 class="hours-card-title">{{ $clinic->name }} Operational Schedule</h2>
                <div class="text-muted small fw-semibold mt-1">Closed days can be switched off. 24-hour days do not need open/close times. Break time is optional for normal hours.</div>
            </div>

            <span class="badge rounded-pill text-bg-primary px-3 py-2">Clinic Setup</span>
        </div>

        <div class="hours-card-body">
            <div class="help-box mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Patients will use these hours as a guide when booking and visiting your clinic.
            </div>

            <div class="table-responsive">
                <table class="hours-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Open?</th>
                            <th>24 Hours?</th>
                            <th>Open Time</th>
                            <th>Close Time</th>
                            <th>Break Start</th>
                            <th>Break End</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($hours as $hour)
                            @php
                                $day = $hour['day'];
                                $isOpen = filter_var($hour['is_open'], FILTER_VALIDATE_BOOLEAN);
                                $is24Hours = filter_var($hour['is_24_hours'] ?? false, FILTER_VALIDATE_BOOLEAN);
                            @endphp

                            <tr data-day-row="{{ $day }}">
                                <td data-label="Day">
                                    <div class="day-name">{{ $hour['label'] }}</div>
                                </td>

                                <td data-label="Open?">
                                    <div class="form-check form-switch m-0">
                                        <input type="hidden" name="hours[{{ $day }}][is_open]" value="0">
                                        <input
                                            class="form-check-input js-open-toggle"
                                            type="checkbox"
                                            role="switch"
                                            name="hours[{{ $day }}][is_open]"
                                            value="1"
                                            data-day="{{ $day }}"
                                            {{ $isOpen ? 'checked' : '' }}>
                                    </div>
                                </td>

                                <td data-label="24 Hours?">
                                    <div class="form-check form-switch m-0">
                                        <input type="hidden" name="hours[{{ $day }}][is_24_hours]" value="0">
                                        <input
                                            class="form-check-input js-24-toggle"
                                            type="checkbox"
                                            role="switch"
                                            name="hours[{{ $day }}][is_24_hours]"
                                            value="1"
                                            data-day="{{ $day }}"
                                            {{ $is24Hours ? 'checked' : '' }}>
                                    </div>
                                </td>

                                <td data-label="Open Time" class="js-time-cell" data-day-time-cell="{{ $day }}">
                                    <input
                                        type="time"
                                        name="hours[{{ $day }}][open_time]"
                                        class="form-control js-hour-input @error("hours.{$day}.open_time") is-invalid @enderror"
                                        value="{{ $hour['open_time'] }}"
                                        data-day-input="{{ $day }}">
                                    @error("hours.{$day}.open_time")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td data-label="Close Time" class="js-time-cell" data-day-time-cell="{{ $day }}">
                                    <input
                                        type="time"
                                        name="hours[{{ $day }}][close_time]"
                                        class="form-control js-hour-input @error("hours.{$day}.close_time") is-invalid @enderror"
                                        value="{{ $hour['close_time'] }}"
                                        data-day-input="{{ $day }}">
                                    @error("hours.{$day}.close_time")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td data-label="Break Start" class="js-time-cell" data-day-time-cell="{{ $day }}">
                                    <input
                                        type="time"
                                        name="hours[{{ $day }}][break_start]"
                                        class="form-control js-hour-input @error("hours.{$day}.break_start") is-invalid @enderror"
                                        value="{{ $hour['break_start'] }}"
                                        data-day-input="{{ $day }}">
                                    @error("hours.{$day}.break_start")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td data-label="Break End" class="js-time-cell" data-day-time-cell="{{ $day }}">
                                    <input
                                        type="time"
                                        name="hours[{{ $day }}][break_end]"
                                        class="form-control js-hour-input @error("hours.{$day}.break_end") is-invalid @enderror"
                                        value="{{ $hour['break_end'] }}"
                                        data-day-input="{{ $day }}">
                                    @error("hours.{$day}.break_end")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('hours')
                <div class="text-danger fw-bold small mt-2">{{ $message }}</div>
            @enderror

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    Save and Continue
                    <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function syncDay(day) {
        const openToggle = document.querySelector('.js-open-toggle[data-day="' + day + '"]');
        const twentyFourToggle = document.querySelector('.js-24-toggle[data-day="' + day + '"]');
        const inputs = document.querySelectorAll('.js-hour-input[data-day-input="' + day + '"]');
        const timeCells = document.querySelectorAll('.js-time-cell[data-day-time-cell="' + day + '"]');

        const isOpen = openToggle && openToggle.checked;
        const is24Hours = twentyFourToggle && twentyFourToggle.checked;
        const enableTimeInputs = isOpen && !is24Hours;

        if (twentyFourToggle) {
            twentyFourToggle.disabled = !isOpen;
            if (!isOpen) {
                twentyFourToggle.checked = false;
            }
        }

        inputs.forEach(function (input) {
            input.disabled = !enableTimeInputs;
        });

        timeCells.forEach(function (cell) {
            cell.classList.toggle('hours-cell-muted', !enableTimeInputs);
        });
    }

    document.querySelectorAll('.js-open-toggle').forEach(function (toggle) {
        syncDay(toggle.dataset.day);

        toggle.addEventListener('change', function () {
            syncDay(toggle.dataset.day);
        });
    });

    document.querySelectorAll('.js-24-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            syncDay(toggle.dataset.day);
        });
    });
});
</script>
@endsection
