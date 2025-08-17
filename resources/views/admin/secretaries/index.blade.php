@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Secretaries</h3>
    <a href="{{ route('admin.secretaries.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Secretary
    </a>
  </div>

  @include('partials.alerts')

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Clinics</th>
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
                <a href="{{ route('admin.secretaries.edit', $s) }}" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('admin.secretaries.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete this secretary?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
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
