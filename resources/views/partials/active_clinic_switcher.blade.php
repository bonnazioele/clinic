@php
    $user = auth()->user();
    $clinics = collect();

    if ($user?->is_secretary) {
        $clinics = $user->secretaryClinics()->orderBy('name')->get();
    } elseif ($user?->is_doctor) {
        $clinics = $user->clinics()->orderBy('name')->get();
    }
@endphp

@if($clinics->isNotEmpty())
    @php
        $activeClinicName = optional($clinics->firstWhere('id', (int) session('active_clinic_id')))->name
            ?? optional($clinics->first())->name;
    @endphp
    @if($activeClinicName)
        <span class="badge bg-light text-primary me-2">{{ $activeClinicName }}</span>
    @endif
@endif
