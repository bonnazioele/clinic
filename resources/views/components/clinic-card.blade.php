@props(['clinic'])

<div class="card h-100 shadow-sm border-0">
  <div class="card-body d-flex flex-column">
    <h5 class="card-title">{{ $clinic->name }}</h5>
    <p class="card-text text-muted small mb-2">{{ $clinic->address }}</p>
    <div class="mb-3">
      @foreach($clinic->services as $svc)
        <span class="badge bg-info text-dark me-1">{{ $svc->name }}</span>
      @endforeach
    </div>
    <a href="{{ route('appointments.create', ['clinic_id'=>$clinic->id]) }}"
       class="mt-auto btn btn-primary btn-sm">
      Book
    </a>
  </div>
</div>
