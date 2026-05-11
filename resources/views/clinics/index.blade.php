@extends(auth()->check() ? 'layouts.patient-dashboard' : 'layouts.app')

@section('title', 'Find Clinics')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9vD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<style>
    .clinics-page {
        width: min(96%, 1500px);
        margin: 0 auto;
        padding: 1rem 0 2.5rem;
    }

    .clinics-shell {
        display: grid;
        gap: 1.1rem;
    }

    .clinics-hero {
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

    .clinics-hero::after {
        content: "";
        position: absolute;
        inset: auto -60px -110px auto;
        width: 280px;
        height: 280px;
        border-radius: 999px;
        background: rgba(255,255,255,.13);
    }

    .clinics-hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .clinics-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .42rem .72rem;
        border-radius: 999px;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.22);
        font-size: .78rem;
        font-weight: 800;
        margin-bottom: .85rem;
    }

    .clinics-hero h1 {
        margin: 0;
        font-size: clamp(1.7rem, 3vw, 2.45rem);
        font-weight: 900;
        letter-spacing: -.05em;
        line-height: 1.05;
    }

    .clinics-hero p {
        margin: .55rem 0 0;
        max-width: 650px;
        color: rgba(255,255,255,.86);
        font-size: .96rem;
        font-weight: 500;
        line-height: 1.6;
    }

    .clinics-hero-stat {
        min-width: 150px;
        padding: 1rem;
        border-radius: 22px;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.22);
        backdrop-filter: blur(12px);
        text-align: right;
    }

    .clinics-hero-stat strong {
        display: block;
        font-size: 2rem;
        font-weight: 950;
        line-height: 1;
    }

    .clinics-hero-stat span {
        display: block;
        margin-top: .25rem;
        color: rgba(255,255,255,.82);
        font-size: .78rem;
        font-weight: 800;
    }

    .clinics-search-card,
    .clinics-map-card,
    .clinic-card {
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, .95);
        background: rgba(255,255,255,.96);
        box-shadow: 0 16px 42px rgba(15, 23, 42, .08);
    }

    .clinics-search-card {
        padding: 1rem;
    }

    .clinics-search-card .form-label {
        font-size: .8rem;
        font-weight: 850;
        color: #334155;
    }

    .clinics-search-card .form-control,
    .clinics-search-card .form-select {
        border-radius: 15px;
        border-color: #dbe3ef;
        padding: .7rem .8rem;
        font-weight: 650;
    }

    .clinics-search-card .form-control:focus,
    .clinics-search-card .form-select:focus {
        border-color: rgba(13, 110, 253, .55);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .10);
    }

    .clinics-search-card .btn {
        border-radius: 15px;
        font-weight: 850;
    }

    .clinics-map-card {
        padding: .9rem;
        overflow: hidden;
    }

    #clinics-map {
        height: 380px;
        width: 100%;
        border-radius: 20px;
        overflow: hidden;
        background: #f8fafc;
    }

    .clinic-card {
        overflow: hidden;
        height: 100%;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .clinic-card:hover {
        transform: translateY(-2px);
        border-color: rgba(13, 110, 253, .22);
        box-shadow: 0 20px 50px rgba(15, 23, 42, .11);
    }

    .clinic-cover {
        height: 145px;
        background:
            radial-gradient(circle at 15% 15%, rgba(255,255,255,.33), transparent 22%),
            linear-gradient(135deg, #dbeafe, #eff6ff);
        position: relative;
        overflow: hidden;
    }

    .clinic-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .clinic-cover-placeholder {
        height: 100%;
        display: grid;
        place-items: center;
        color: #1d4ed8;
        font-size: 2.25rem;
    }

    .clinic-status-badge {
        position: absolute;
        top: .85rem;
        right: .85rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .38rem .68rem;
        border-radius: 999px;
        background: rgba(220, 252, 231, .95);
        color: #047857;
        border: 1px solid rgba(22, 163, 74, .16);
        font-size: .72rem;
        font-weight: 900;
    }

    .clinic-card-body {
        padding: 1rem 1.05rem 1.1rem;
    }

    .clinic-name-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
    }

    .clinic-name {
        margin: 0;
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .clinic-address {
        margin: .35rem 0 0;
        color: #64748b;
        font-size: .85rem;
        line-height: 1.45;
        font-weight: 600;
    }

    .clinic-logo {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .clinic-logo-placeholder {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        color: #0d6efd;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-size: 1.2rem;
    }

    .clinic-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .95rem;
    }

    .clinic-info-box {
        padding: .7rem;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        background: #f8fafc;
        min-width: 0;
    }

    .clinic-info-box span {
        display: flex;
        align-items: center;
        gap: .35rem;
        color: #64748b;
        font-size: .72rem;
        font-weight: 850;
        margin-bottom: .2rem;
    }

    .clinic-info-box strong {
        display: block;
        color: #0f172a;
        font-size: .84rem;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .clinic-description {
        margin: .95rem 0 0;
        color: #475569;
        font-size: .86rem;
        line-height: 1.6;
        font-weight: 500;
    }

    .clinic-section-title {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin: 1rem 0 .65rem;
        color: #0f172a;
        font-size: .88rem;
        font-weight: 950;
    }

    .clinic-section-title i {
        color: #0d6efd;
    }

    .service-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .service-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        border: 1px solid rgba(13, 110, 253, .24);
        background: rgba(13, 110, 253, .07);
        color: #0b5ed7;
        padding: .45rem .7rem;
        font-size: .78rem;
        font-weight: 850;
        transition: all .16s ease;
    }

    .service-chip:hover {
        background: #0d6efd;
        color: #fff;
        transform: translateY(-1px);
    }

    .service-hint {
        margin-top: .55rem;
        color: #64748b;
        font-size: .78rem;
        font-weight: 650;
    }

    .clinic-card-footer {
        padding: .9rem 1.05rem 1.05rem;
        border-top: 1px solid #edf2f7;
        background: #fbfdff;
        display: flex;
        gap: .55rem;
        flex-wrap: wrap;
    }

    .clinic-card-footer .btn {
        border-radius: 14px;
        font-size: .82rem;
        font-weight: 900;
        padding: .55rem .82rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .empty-clinics {
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
    }

    .empty-clinics i {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto .75rem;
        border-radius: 20px;
        background: #eff6ff;
        color: #0d6efd;
        font-size: 1.6rem;
    }

    .empty-clinics strong {
        display: block;
        color: #0f172a;
        font-weight: 950;
        margin-bottom: .25rem;
    }

    .service-doctor-modal .modal-content {
        border: 0;
        border-radius: 26px;
        overflow: hidden;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .28);
    }

    .service-doctor-modal .modal-header {
        border-bottom: 1px solid #edf2f7;
        background:
            radial-gradient(circle at top left, rgba(13, 110, 253, .12), transparent 32%),
            linear-gradient(135deg, #ffffff, #f8fbff);
        padding: 1.1rem 1.25rem;
    }

    .service-doctor-modal .modal-title {
        color: #0f172a;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .service-modal-subtitle {
        color: #64748b;
        font-size: .82rem;
        font-weight: 650;
        margin-top: .15rem;
    }

    .service-doctor-modal .modal-body {
        background: #f8fafc;
        padding: 1rem;
    }

    .doctor-click-hint {
        margin-bottom: .85rem;
        padding: .7rem .85rem;
        border-radius: 16px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: .82rem;
        font-weight: 750;
    }

    .modal-doctor-list {
        display: grid;
        gap: .75rem;
    }

    .modal-doctor-card {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .055);
    }

    .modal-doctor-toggle {
        width: 100%;
        border: 0;
        background: #fff;
        padding: .95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        text-align: left;
    }

    .modal-doctor-toggle:hover {
        background: #fbfdff;
    }

    .modal-doctor-left {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        min-width: 0;
    }

    .modal-doctor-avatar {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 15px;
        display: grid;
        place-items: center;
        color: #0d6efd;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-size: 1.1rem;
    }

    .modal-doctor-name {
        color: #0f172a;
        font-size: .95rem;
        font-weight: 950;
        margin: 0;
    }

    .modal-doctor-contact {
        color: #64748b;
        font-size: .78rem;
        font-weight: 650;
        margin-top: .12rem;
        overflow-wrap: anywhere;
    }

    .modal-doctor-instruction {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .45rem;
        color: #0b5ed7;
        font-size: .73rem;
        font-weight: 900;
    }

    .modal-doctor-arrow {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: #f1f5f9;
        color: #334155;
        transition: transform .16s ease;
    }

    .modal-doctor-card.is-open .modal-doctor-arrow {
        transform: rotate(180deg);
        background: #0d6efd;
        color: #fff;
    }

    .modal-schedule-panel {
        display: none;
        padding: 0 .95rem .95rem;
    }

    .modal-doctor-card.is-open .modal-schedule-panel {
        display: block;
    }

    .schedule-inner {
        border-top: 1px solid #edf2f7;
        padding-top: .8rem;
    }

    .schedule-title {
        display: flex;
        align-items: center;
        gap: .4rem;
        color: #0f172a;
        font-size: .82rem;
        font-weight: 950;
        margin-bottom: .55rem;
    }

    .schedule-row {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        gap: .7rem;
        align-items: center;
        padding: .55rem .65rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        margin-bottom: .4rem;
    }

    .schedule-day {
        color: #0f172a;
        font-size: .8rem;
        font-weight: 950;
    }

    .schedule-time {
        color: #475569;
        font-size: .8rem;
        font-weight: 800;
        text-align: right;
    }

    .no-schedule-box,
    .no-doctor-box {
        border-radius: 16px;
        border: 1px dashed #cbd5e1;
        background: #fff;
        color: #64748b;
        padding: .85rem;
        font-size: .83rem;
        font-weight: 650;
    }

    @media (max-width: 900px) {
        .clinics-page {
            width: 100%;
            padding-inline: .5rem;
        }

        .clinics-hero {
            border-radius: 24px;
            padding: 1.15rem;
        }

        .clinics-hero-stat {
            text-align: left;
            width: 100%;
        }

        .clinic-info-grid {
            grid-template-columns: 1fr;
        }

        #clinics-map {
            height: 320px;
        }

        .schedule-row {
            grid-template-columns: 1fr;
        }

        .schedule-time {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
@php
    $clinicsCollection = $clinics instanceof \Illuminate\Pagination\AbstractPaginator
        ? $clinics->getCollection()
        : collect($clinics ?? []);

    $mapClinicsCollection = isset($mapClinics)
        ? collect($mapClinics)
        : $clinicsCollection;

    $clinicsForMap = $mapClinicsCollection
        ->filter(fn ($clinic) => is_object($clinic) && filled($clinic->gps_latitude) && filled($clinic->gps_longitude))
        ->map(function ($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'address' => $clinic->address,
                'lat' => (float) $clinic->gps_latitude,
                'lng' => (float) $clinic->gps_longitude,
            ];
        })
        ->values();

    $clinicsDoctorData = $clinicsCollection
        ->filter(fn ($clinic) => is_object($clinic))
        ->mapWithKeys(function ($clinic) {
            return [
                $clinic->id => [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'doctors' => collect($clinic->doctors ?? [])->map(function ($doctor) {
                        return [
                            'id' => $doctor->id,
                            'name' => $doctor->name ?: trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
                            'email' => $doctor->email,
                            'phone' => $doctor->phone,
                            'services' => collect($doctor->services ?? [])->map(function ($service) {
                                return [
                                    'id' => $service->id,
                                    'name' => $service->name,
                                    'clinic_id' => $service->pivot->clinic_id ?? null,
                                ];
                            })->values(),
                            'schedules' => collect($doctor->doctorSchedules ?? [])->map(function ($schedule) {
                                return [
                                    'id' => $schedule->id,
                                    'day_of_week' => $schedule->day_of_week ?? null,
                                    'start_time' => $schedule->start_time ?? null,
                                    'end_time' => $schedule->end_time ?? null,
                                    'clinic_id' => $schedule->clinic_id ?? null,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ],
            ];
        });
@endphp

<div class="clinics-page">
    @include('partials.alerts')

    <div class="clinics-shell">
        <section class="clinics-hero">
            <div class="clinics-hero-content">
                <div>
                    <span class="clinics-kicker">
                        <i class="bi bi-hospital"></i>
                        CliniQ Clinic Directory
                    </span>

                    <h1>Find Clinics</h1>

                    <p>
                        Search nearby clinics, view available services, and check doctors assigned to each service.
                    </p>
                </div>

                <div class="clinics-hero-stat">
                    <strong>{{ $clinicsCollection->count() }}</strong>
                    <span>Clinic{{ $clinicsCollection->count() === 1 ? '' : 's' }} shown</span>
                </div>
            </div>
        </section>

        <section class="clinics-search-card">
    <form method="GET" action="{{ route('clinics.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-lg-10">
                <label for="search" class="form-label">Search</label>
                <input type="text"
                       name="search"
                       id="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search clinic, doctor, location, service, email, or contact number">
            </div>

            <div class="col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>
            </div>
        </div>

        @if(request()->filled('search'))
            <div class="mt-3">
                <a href="{{ route('clinics.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear search
                </a>
            </div>
        @endif
    </form>
</section>

        <section class="clinics-map-card">
            <div id="clinics-map"></div>
        </section>

        <section class="clinics-list">
            @if($clinicsCollection->isEmpty())
                <div class="empty-clinics">
                    <i class="bi bi-hospital"></i>
                    <strong>No clinics found.</strong>
                    <p class="mb-0">Try searching with another keyword or check again after clinics are approved.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($clinicsCollection as $clinic)
                        @php
                            $clinicServices = collect($clinic->services ?? []);
                            $hasCoordinates = filled($clinic->gps_latitude) && filled($clinic->gps_longitude);
                        @endphp

                        <div class="col-xl-6">
                            <article class="clinic-card" id="clinic-{{ $clinic->id }}">
                                <div class="clinic-cover">
                                    @if(filled($clinic->cover_image))
                                        <img src="{{ asset('storage/' . $clinic->cover_image) }}" alt="{{ $clinic->name }}">
                                    @else
                                        <div class="clinic-cover-placeholder">
                                            <i class="bi bi-hospital"></i>
                                        </div>
                                    @endif

                                    <span class="clinic-status-badge">
                                        <i class="bi bi-check-circle"></i>
                                        {{ ucfirst($clinic->status ?? 'Active') }}
                                    </span>
                                </div>

                                <div class="clinic-card-body">
                                    <div class="clinic-name-row">
                                        <div>
                                            <h2 class="clinic-name">{{ $clinic->name ?? 'Clinic' }}</h2>

                                            <p class="clinic-address">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $clinic->address ?? 'No address provided' }}
                                            </p>
                                        </div>

                                        @if(filled($clinic->logo))
                                            <img src="{{ asset('storage/' . $clinic->logo) }}"
                                                 alt="{{ $clinic->name }}"
                                                 class="clinic-logo">
                                        @else
                                            <div class="clinic-logo-placeholder">
                                                <i class="bi bi-hospital"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="clinic-info-grid">
                                        <div class="clinic-info-box">
                                            <span>
                                                <i class="bi bi-telephone"></i>
                                                Contact
                                            </span>
                                            <strong>{{ $clinic->contact_number ?? 'Not provided' }}</strong>
                                        </div>

                                        <div class="clinic-info-box">
                                            <span>
                                                <i class="bi bi-envelope"></i>
                                                Email
                                            </span>
                                            <strong>{{ $clinic->email ?? 'Not provided' }}</strong>
                                        </div>
                                    </div>

                                    @if(filled($clinic->description))
                                        <p class="clinic-description">
                                            {{ $clinic->description }}
                                        </p>
                                    @endif

                                    <div>
                                        <div class="clinic-section-title">
                                            <i class="bi bi-clipboard2-pulse"></i>
                                            Services
                                        </div>

                                        @if($clinicServices->isNotEmpty())
                                            <div class="service-chip-list">
                                                @foreach($clinicServices as $service)
                                                    <button type="button"
                                                            class="service-chip js-service-doctors"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#serviceDoctorsModal"
                                                            data-clinic-id="{{ $clinic->id }}"
                                                            data-clinic-name="{{ $clinic->name }}"
                                                            data-service-id="{{ $service->id }}"
                                                            data-service-name="{{ $service->name }}">
                                                        <i class="bi bi-plus-circle"></i>
                                                        {{ $service->name }}
                                                    </button>
                                                @endforeach
                                            </div>

                                            <div class="service-hint">
                                                Click a service to view assigned doctors.
                                            </div>
                                        @else
                                            <div class="text-muted small">
                                                No services listed yet.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="clinic-card-footer">
                                    @if(auth()->check() && Route::has('appointments.create'))
                                        <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}"
                                           class="btn btn-primary">
                                            <i class="bi bi-calendar-plus"></i>
                                            Book Appointment
                                        </a>
                                    @elseif(Route::has('login'))
                                        <a href="{{ route('login') }}" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                            Login to Book
                                        </a>
                                    @endif

                                    @if($hasCoordinates)
                                        <a href="https://www.google.com/maps?q={{ $clinic->gps_latitude }},{{ $clinic->gps_longitude }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-outline-secondary">
                                            <i class="bi bi-geo-alt"></i>
                                            Directions
                                        </a>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if(method_exists($clinics, 'links'))
                    <div class="mt-4">
                        {{ $clinics->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</div>

<div class="modal fade service-doctor-modal" id="serviceDoctorsModal" tabindex="-1" aria-labelledby="serviceDoctorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="serviceDoctorsModalLabel">
                        Doctors
                    </h5>
                    <div class="service-modal-subtitle" id="serviceDoctorsModalSubtitle"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="serviceDoctorsContent">
                    <div class="no-doctor-box">
                        Select a service to view assigned doctors.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clinicDoctors = @json($clinicsDoctorData);
        const clinicsForMap = @json($clinicsForMap);

        const modalTitle = document.getElementById('serviceDoctorsModalLabel');
        const modalSubtitle = document.getElementById('serviceDoctorsModalSubtitle');
        const content = document.getElementById('serviceDoctorsContent');

        const dayNames = {
            0: 'Sunday',
            1: 'Monday',
            2: 'Tuesday',
            3: 'Wednesday',
            4: 'Thursday',
            5: 'Friday',
            6: 'Saturday',
            7: 'Sunday',
            sunday: 'Sunday',
            monday: 'Monday',
            tuesday: 'Tuesday',
            wednesday: 'Wednesday',
            thursday: 'Thursday',
            friday: 'Friday',
            saturday: 'Saturday'
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatDay(value) {
            if (value === null || value === undefined || value === '') {
                return 'Day not set';
            }

            const key = String(value).toLowerCase();

            return dayNames[key] || dayNames[value] || String(value);
        }

        function formatTime(value) {
            if (!value) {
                return null;
            }

            const parts = String(value).split(':');

            if (parts.length < 2) {
                return value;
            }

            let hour = parseInt(parts[0], 10);
            const minute = parts[1];
            const ampm = hour >= 12 ? 'PM' : 'AM';

            hour = hour % 12;
            hour = hour ? hour : 12;

            return `${hour}:${minute} ${ampm}`;
        }

        function renderDoctorCard(doctor, clinicId, index) {
            const schedules = doctor.schedules.filter(function (schedule) {
                return schedule.clinic_id === null || Number(schedule.clinic_id) === clinicId;
            });

            const schedulesHtml = schedules.length
                ? schedules.map(function (schedule) {
                    const start = formatTime(schedule.start_time);
                    const end = formatTime(schedule.end_time);

                    return `
                        <div class="schedule-row">
                            <div class="schedule-day">${escapeHtml(formatDay(schedule.day_of_week))}</div>
                            <div class="schedule-time">
                                ${escapeHtml(start || 'Time not set')}
                                ${end ? ' - ' + escapeHtml(end) : ''}
                            </div>
                        </div>
                    `;
                }).join('')
                : `
                    <div class="no-schedule-box">
                        No schedule has been set for this doctor yet.
                    </div>
                `;

            return `
                <div class="modal-doctor-card" data-doctor-card>
                    <button type="button" class="modal-doctor-toggle" data-doctor-toggle>
                        <div class="modal-doctor-left">
                            <div class="modal-doctor-avatar">
                                <i class="bi bi-person-vcard"></i>
                            </div>

                            <div>
                                <h6 class="modal-doctor-name">Dr. ${escapeHtml(doctor.name)}</h6>

                                <div class="modal-doctor-contact">
                                    ${doctor.phone ? escapeHtml(doctor.phone) : escapeHtml(doctor.email || 'No contact provided')}
                                </div>

                                <div class="modal-doctor-instruction">
                                    <i class="bi bi-hand-index-thumb"></i>
                                    Click to show schedule
                                </div>
                            </div>
                        </div>

                        <div class="modal-doctor-arrow">
                            <i class="bi bi-chevron-down"></i>
                        </div>
                    </button>

                    <div class="modal-schedule-panel">
                        <div class="schedule-inner">
                            <div class="schedule-title">
                                <i class="bi bi-calendar-week"></i>
                                Schedule
                            </div>

                            ${schedulesHtml}
                        </div>
                    </div>
                </div>
            `;
        }

        function bindDoctorToggles() {
            document.querySelectorAll('[data-doctor-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const card = this.closest('[data-doctor-card]');

                    if (!card) {
                        return;
                    }

                    card.classList.toggle('is-open');
                });
            });
        }

        document.querySelectorAll('.js-service-doctors').forEach(function (button) {
            button.addEventListener('click', function () {
                const clinicId = Number(this.dataset.clinicId);
                const serviceId = Number(this.dataset.serviceId);
                const clinicName = this.dataset.clinicName || 'Clinic';
                const serviceName = this.dataset.serviceName || 'Service';

                modalTitle.textContent = serviceName;
                modalSubtitle.textContent = clinicName + ' assigned doctors';

                const clinic = clinicDoctors[clinicId] || { doctors: [] };

                const doctors = clinic.doctors.filter(function (doctor) {
                    return doctor.services.some(function (service) {
                        return Number(service.id) === serviceId
                            && (
                                service.clinic_id === null
                                || Number(service.clinic_id) === clinicId
                            );
                    });
                });

                if (!doctors.length) {
                    content.innerHTML = `
                        <div class="no-doctor-box">
                            No doctor is assigned to <strong>${escapeHtml(serviceName)}</strong> in this clinic yet.
                        </div>
                    `;
                    return;
                }

                content.innerHTML = `
                    <div class="doctor-click-hint">
                        <i class="bi bi-info-circle me-1"></i>
                        Select a doctor card below to show or hide the schedule.
                    </div>

                    <div class="modal-doctor-list">
                        ${doctors.map(function (doctor, index) {
                            return renderDoctorCard(doctor, clinicId, index);
                        }).join('')}
                    </div>
                `;

                bindDoctorToggles();
            });
        });

        const mapElement = document.getElementById('clinics-map');

        if (mapElement && typeof L !== 'undefined') {
            const defaultCenter = [10.3157, 123.8854];
            const map = L.map('clinics-map').setView(defaultCenter, 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            if (!clinicsForMap.length) {
                L.marker(defaultCenter)
                    .addTo(map)
                    .bindPopup('No clinic coordinates available yet.');
            } else {
                const bounds = [];

                clinicsForMap.forEach(function (clinic) {
                    const latLng = [clinic.lat, clinic.lng];
                    bounds.push(latLng);

                    L.marker(latLng)
                        .addTo(map)
                        .bindPopup(`
                            <strong>${escapeHtml(clinic.name || 'Clinic')}</strong><br>
                            <span>${escapeHtml(clinic.address || 'No address provided')}</span><br>
                            <a href="#clinic-${clinic.id}">View clinic details</a>
                        `);
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 15);
                } else {
                    map.fitBounds(bounds, {
                        padding: [24, 24]
                    });
                }
            }
        }
    });
</script>
@endpush