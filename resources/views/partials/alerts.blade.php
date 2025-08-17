@php
  $toasts = [];
  if (session('status')) {
    $toasts[] = ['type' => 'success', 'message' => session('status')];
  }
  if (session('success')) {
    $toasts[] = ['type' => 'success', 'message' => session('success')];
  }
  if (session('error')) {
    $toasts[] = ['type' => 'danger', 'message' => session('error')];
  }
  if (session('warning')) {
    $toasts[] = ['type' => 'warning', 'message' => session('warning')];
  }
  if (session('info')) {
    $toasts[] = ['type' => 'info', 'message' => session('info')];
  }
  if ($errors->any()) {
    $toasts[] = ['type' => 'danger', 'message' => 'Please fix the highlighted errors.'];
  }
@endphp

@if(!empty($toasts))
  @php
    $toastOffsetTop = $toastOffsetTop ?? '5rem';
    $toastOffsetRight = $toastOffsetRight ?? '1rem';
  @endphp
  <div class="position-fixed" style="z-index: 2000; top: {{ $toastOffsetTop }}; right: {{ $toastOffsetRight }}; max-width: 360px;">
    <div class="toast-container" id="app-toast-container">
      @foreach($toasts as $i => $t)
        @php
          $bg = match($t['type']) {
            'success' => 'text-bg-success',
            'danger'  => 'text-bg-danger',
            'warning' => 'text-bg-warning',
            'info'    => 'text-bg-info',
            default   => 'text-bg-primary'
          };
          $icon = match($t['type']) {
            'success' => 'bi-check-circle',
            'danger'  => 'bi-x-circle',
            'warning' => 'bi-exclamation-triangle',
            'info'    => 'bi-info-circle',
            default   => 'bi-bell'
          };
        @endphp
        <div class="toast align-items-center {{ $bg }} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
          <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
              <i class="bi {{ $icon }}"></i>
              <span>{{ $t['message'] }}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      @endforeach

      {{-- Validation errors details (collapsible) --}}
      @if($errors->any())
        <div class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
          <div class="toast-header">
            <i class="bi bi-bug-fill text-danger me-2"></i>
            <strong class="me-auto">Form Errors</strong>
            <small>now</small>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </div>
  </div>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const container = document.getElementById('app-toast-container');
      if (!container || typeof bootstrap === 'undefined') return;
      container.querySelectorAll('.toast').forEach(t => new bootstrap.Toast(t).show());
    });
  </script>
  @endpush
@endif
