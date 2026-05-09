@extends('layouts.app')

@section('title','Walk-In Patient Registration')

@section('content')
<style>
  .patient-create-page {
    width: 96%;
    max-width: 1380px;
    margin: 0 auto;
    padding: 1rem 0 2rem;
  }
</style>

<div class="container py-4 patient-create-page">
  @include('partials.alerts')

  @include('secretary.walkin._form', [
    'clinicServices' => $clinicServices ?? collect(),
    'activeClinic' => $activeClinic ?? null,
  ])
</div>
@endsection
