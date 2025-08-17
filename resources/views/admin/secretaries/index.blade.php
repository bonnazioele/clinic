@extends('layouts.app')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-person-gear medical-icon me-2"></i>Manage Secretaries
        </h2>
        <p class="text-muted mb-0">Create and manage secretary accounts</p>
      </div>
      <a href="{{ route('admin.secretaries.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-2"></i>Add Secretary
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th><i class="bi bi-person me-1"></i>Name</th>
            <th><i class="bi bi-envelope me-1"></i>Email</th>
            <th><i class="bi bi-telephone me-1"></i>Phone</th>
            <th><i class="bi bi-building me-1"></i>Clinics</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($secretaries as $s)
            <tr>
              <td class="py-3">{{ $s->name }}</td>
              <td class="py-3">{{ $s->email }}</td>
              <td class="py-3">{{ $s->phone }}</td>
              <td class="py-3">
                @if($s->secretaryClinics->isEmpty())
                  <span class="badge bg-secondary">None</span>
                @else
                  <div class="d-flex flex-wrap gap-2">
                    @foreach($s->secretaryClinics as $c)
                      <span class="badge bg-info text-dark">{{ $c->name }}</span>
                    @endforeach
                  </div>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('admin.secretaries.edit', $s) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                  <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('admin.secretaries.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete this secretary?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-trash me-1"></i>Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No secretaries yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $secretaries->links() }}</div>
</div>
@endsection
