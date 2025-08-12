@extends('layouts.app')
@section('title','Edit Doctor')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <h3>Edit Doctor</h3>
  <form method="POST" action="{{ route('secretary.doctors.update',$doctor) }}">
    @csrf @method('PATCH')

    <!-- name, email, phone, address, password fields… -->

    <!-- Clinics -->
    <div class="mb-3">
      <label class="form-label">Clinics</label>
      <select name="clinic_ids[]" 
              class="form-select @error('clinic_ids') is-invalid @enderror"
              multiple>
        @foreach($clinics as $c)
          <option value="{{ $c->id }}"
            @selected(
              in_array(
                $c->id,
                old('clinic_ids',
                    $doctor->clinics->pluck('id')->toArray()
                )
              )
            )>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
      @error('clinic_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Services -->
    <div class="mb-3">
      <label class="form-label">Services</label>
      <select name="service_ids[]"
              class="form-select @error('service_ids') is-invalid @enderror"
              multiple>
        @foreach($services as $s)
          <option value="{{ $s->id }}"
            @selected(
              in_array(
                $s->id,
                old('service_ids',
                    $doctor->services->pluck('id')->toArray()
                )
              )
            )>
            {{ $s->name }}
          </option>
        @endforeach
      </select>
      @error('service_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-primary">Save Changes</button>
    <a href="{{ route('secretary.doctors.index') }}" class="btn btn-link">Cancel</a>
  </form>
</div>
@endsection
