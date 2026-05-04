@extends('layouts.app')

@section('title', 'Guest Walk-In Queue')

@section('content')
<div class="walkin-page-shell py-4">
  @include('partials.alerts')

  @include('secretary.walkin._form', [
    'clinicServices' => $clinicServices ?? collect(),
    'clinicDoctors' => $clinicDoctors ?? collect(),
    'activeClinic' => $activeClinic ?? null,
  ])
</div>
@endsection