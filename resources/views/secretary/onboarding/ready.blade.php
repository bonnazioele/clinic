@extends('layouts.app')

@section('title', 'Clinic Ready')

@section('content')
<style>
    .ready-page {
        width: min(96%, 900px);
        margin: 0 auto;
        padding: 2rem 0 3rem;
    }

    .ready-card {
        border: 1px solid #bbf7d0;
        border-radius: 30px;
        background:
            radial-gradient(circle at 90% 20%, rgba(34,197,94,.12), transparent 18%),
            #ffffff;
        box-shadow: 0 20px 55px rgba(15, 23, 42, .10);
        padding: 2rem;
        text-align: center;
    }

    .ready-icon {
        width: 96px;
        height: 96px;
        border-radius: 32px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #087b3d, #22c55e);
        color: #fff;
        font-size: 3rem;
        margin: 0 auto 1rem;
        box-shadow: 0 18px 35px rgba(34,197,94,.25);
    }

    .ready-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.8rem, 4vw, 2.7rem);
        font-weight: 950;
        letter-spacing: -.06em;
    }

    .ready-text {
        color: #475569;
        font-size: 1rem;
        font-weight: 700;
        margin: .65rem auto 1.5rem;
        max-width: 620px;
    }

    .ready-steps {
        display: flex;
        justify-content: center;
        gap: .65rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .step-pill {
        border-radius: 999px;
        padding: .5rem .85rem;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-size: .8rem;
        font-weight: 950;
    }

    .ready-actions {
        display: flex;
        justify-content: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .ready-actions .btn {
        border-radius: 14px;
        font-weight: 900;
        padding: .78rem 1.15rem;
    }
</style>

<div class="ready-page">
    <section class="ready-card">
        <div class="ready-icon">
            <i class="bi bi-check2-circle"></i>
        </div>

        <h1 class="ready-title">Your clinic is ready now</h1>
        <p class="ready-text">
            {{ $clinic->name }} has completed the required setup. You can now continue to the secretary dashboard and start managing appointments, queues, and clinic services.
        </p>

        <div class="ready-steps">
            <span class="step-pill"><i class="bi bi-check-circle me-1"></i> Password changed</span>
            <span class="step-pill"><i class="bi bi-check-circle me-1"></i> Operational hours configured</span>
            <span class="step-pill"><i class="bi bi-check-circle me-1"></i> Clinic marked ready</span>
        </div>

        <form method="POST" action="{{ route('secretary.onboarding.finish') }}" class="ready-actions">
            @csrf
            <button type="submit" class="btn btn-success">
                Go to Dashboard
                <i class="bi bi-arrow-right ms-1"></i>
            </button>

            <a href="{{ route('secretary.onboarding.operational-hours') }}" class="btn btn-outline-secondary">
                Edit Operational Hours
            </a>
        </form>
    </section>
</div>
@endsection
