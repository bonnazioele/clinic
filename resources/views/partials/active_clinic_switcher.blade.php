@php
    $user = auth()->user();
    $clinics = collect();
    $action = null;
    $includeReturnTo = false;

    if ($user?->is_secretary) {
        $clinics = $user->secretaryClinics()->orderBy('name')->get();
        $action = route('secretary.active-clinic.update');
    } elseif ($user?->is_doctor) {
        $clinics = $user->clinics()->orderBy('name')->get();
        $action = route('doctor.choose-clinic.select');
        $includeReturnTo = true;
    }
@endphp

@if($clinics->count() > 1 && $action)
    <form action="{{ $action }}" method="POST" class="d-inline me-2" id="active-clinic-switcher-form">
        @csrf
        @if($includeReturnTo)
            <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
        @endif
        <select name="clinic_id"
                class="form-select form-select-sm"
                onchange="this.form.submit()"
                aria-label="Active clinic"
                style="width:auto; display:inline-block; min-width:220px;">
            @foreach($clinics as $c)
                <option value="{{ $c->id }}" @selected((int) session('active_clinic_id') === (int) $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </form>
@elseif($clinics->count() === 1)
    <span class="badge bg-light text-primary me-2">{{ $clinics->first()->name }}</span>
@endif
