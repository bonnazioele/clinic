@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>Success!</strong> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Error!</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('warning'))
  <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Warning!</strong> {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('info'))
  <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <strong>Info!</strong> {{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('status'))
  <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
