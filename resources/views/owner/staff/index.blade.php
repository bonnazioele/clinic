@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Staff Management</h3>
    <span class="badge bg-success ms-2">{{ $clinic->name }}</span>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-primary text-white">
          <i class="bi bi-stethoscope me-1"></i> Doctors
        </div>
        <div class="card-body">
          <form class="row g-2 mb-3" method="POST" action="{{ route('owner.staff.attach') }}">
            @csrf
            <input type="hidden" name="role" value="doctor">
            <div class="col-9">
              <select name="user_id" class="form-select">
                @foreach($doctorCandidates as $candidate)
                  <option value="{{ $candidate->id }}">Dr. {{ $candidate->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-3 d-grid">
              <button class="btn btn-primary">Attach</button>
            </div>
          </form>

          <ul class="list-group list-group-flush">
            @forelse($doctors as $doc)
              <li class="list-group-item d-flex align-items-center justify-content-between">
                <div>
                  <i class="bi bi-person-badge me-1 text-primary"></i>
                  Dr. {{ $doc->name }}
                </div>
                <form method="POST" action="{{ route('owner.staff.detach') }}">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="role" value="doctor">
                  <input type="hidden" name="user_id" value="{{ $doc->id }}">
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x"></i> Detach
                  </button>
                </form>
              </li>
            @empty
              <li class="list-group-item text-muted">No doctors attached.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-success text-white">
          <i class="bi bi-clipboard2-pulse me-1"></i> Secretaries
        </div>
        <div class="card-body">
          <form class="row g-2 mb-3" method="POST" action="{{ route('owner.staff.attach') }}">
            @csrf
            <input type="hidden" name="role" value="secretary">
            <div class="col-9">
              <select name="user_id" class="form-select">
                @foreach($secretaryCandidates as $candidate)
                  <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-3 d-grid">
              <button class="btn btn-success">Attach</button>
            </div>
          </form>

          <ul class="list-group list-group-flush">
            @forelse($secretaries as $sec)
              <li class="list-group-item d-flex align-items-center justify-content-between">
                <div>
                  <i class="bi bi-person-workspace me-1 text-success"></i>
                  {{ $sec->name }}
                </div>
                <form method="POST" action="{{ route('owner.staff.detach') }}">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="role" value="secretary">
                  <input type="hidden" name="user_id" value="{{ $sec->id }}">
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x"></i> Detach
                  </button>
                </form>
              </li>
            @empty
              <li class="list-group-item text-muted">No secretaries attached.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
