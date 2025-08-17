@extends('layouts.app')
@section('title','Manage Doctors')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h2 class="fw-bold text-primary mb-1">
        <i class="bi bi-person-badge medical-icon me-2"></i>Manage Doctors
      </h2>
      <p class="text-muted mb-0">Onboard and manage healthcare providers</p>
    </div>
    <a href="{{ route('secretary.doctors.create') }}" class="btn btn-success">
      <i class="bi bi-plus-circle me-2"></i>New Doctor
    </a>
  </div>

  @if($doctors->isEmpty())
    <div class="text-muted">No doctors added yet.</div>
  @else
    <table class="table align-middle">
      <thead class="table-light">
        <tr>
          <th><i class="bi bi-person me-1"></i>Name</th>
          <th><i class="bi bi-envelope me-1"></i>Email</th>
          <th><i class="bi bi-telephone me-1"></i>Phone</th>
          <th><i class="bi bi-building me-1"></i>Clinics</th>
          <th><i class="bi bi-scissors me-1"></i>Services</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($doctors as $d)
        <tr>
          <td>{{ $d->name }}</td>
          <td>{{ $d->email }}</td>
          <td>{{ $d->phone }}</td>
          <td>
            @if($d->clinics && $d->clinics->count())
              <div class="small text-muted">
                {{ $d->clinics->pluck('name')->join(', ') }}
              </div>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td>
            @if($d->services && $d->services->count())
              <div class="small text-muted">
                {{ $d->services->pluck('name')->join(', ') }}
              </div>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="text-end">
            <a href="{{ route('secretary.doctors.edit',$d) }}"
               class="btn btn-sm btn-outline-primary rounded-pill me-1">
              <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST"
                  action="{{ route('secretary.doctors.destroy',$d) }}"
                  class="d-inline"
                  onsubmit="return confirm('Remove this doctor?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger rounded-pill">
                <i class="bi bi-trash me-1"></i>Delete
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    {{ $doctors->links() }}
  @endif
</div>
@endsection
