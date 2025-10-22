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
            'success' => 'toast-success-custom',
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
  <div class="toast align-items-center {{ $bg }} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
          <div class="toast-body d-flex align-items-center gap-2">
            <i class="bi {{ $icon }}"></i>
            <span>{{ $t['message'] }}</span>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <style>
    /* Custom toast styling for success messages to match clinic approved alert colors */
    .toast.toast-success-custom {
        background-color: #0c7a5bff !important;
        border: 1px solid #0c7a5bff !important;
        color: #a7f3d0 !important;
    }

    .toast.toast-success-custom .toast-body {
        color: white !important;
        font-weight: 500 !important;
    }
  </style>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const container = document.getElementById('app-toast-container');
      if (!container || typeof bootstrap === 'undefined') return;

      const AUTO_DISMISS_MS = 3000; // all toasts unified
      container.querySelectorAll('.toast').forEach(t => {
        const toast = new bootstrap.Toast(t, { delay: AUTO_DISMISS_MS });
        toast.show();
        t.addEventListener('hidden.bs.toast', () => t.remove());
      });
    });
  </script>
  @endpush
@endif
