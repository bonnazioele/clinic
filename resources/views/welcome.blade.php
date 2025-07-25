@extends('layouts.app')

@section('title','Welcome to CliniQ')

@section('content')
<div class="text-center my-5">
  <h1>Welcome to CliniQ</h1>
  <p class="lead">Your one-stop portal for booking and registering with local clinics.</p>
  <a href="{{ route('clinics.index') }}" class="btn btn-primary">Find a Clinic</a>
  @guest
    <a href="{{ route('login') }}" class="btn btn-outline-secondary ms-2">Login / Register</a>
  @endguest
</div>
@endsection
