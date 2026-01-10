@extends('layouts.app')

@section('title','Walk-In Registration')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  @include('secretary.walkin._form', [
    'clinicServices' => $clinicServices ?? collect(),
    'activeClinic' => $activeClinic ?? null,
  ])
</div>
@endsection
