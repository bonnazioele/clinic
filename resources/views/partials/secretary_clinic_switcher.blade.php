@php($clinics = auth()->check() && auth()->user()->is_secretary ? auth()->user()->secretaryClinics()->orderBy('name')->get() : collect())
@if($clinics->count() > 1)
<form action="{{ route('secretary.active-clinic.update') }}" method="POST" class="d-inline" id="clinic-switcher-form">
    @csrf
    <select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto; display:inline-block; min-width:220px;">
        @foreach($clinics as $c)
            <option value="{{ $c->id }}" @selected(($activeClinicId ?? session('active_clinic_id')) == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</form>
@elseif($clinics->count() === 1)
<span class="badge bg-primary">{{ $clinics->first()->name }}</span>
@else
<span class="text-warning small">No assigned clinics</span>
@endif
