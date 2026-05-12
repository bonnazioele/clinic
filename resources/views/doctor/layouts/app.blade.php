@extends($profileLayout ?? 'layouts.app')
@section('content')
  <main class="doctor-layout-content">
    @yield('doctor-content')
  </main>

  <style>
    .doctor-layout-content {
      width: 100%;
      min-height: calc(100vh - 96px);
      padding: 24px 28px 36px;
      background:
        radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 28%),
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
        #f6f9fc;
    }

    @media (max-width: 768px) {
      .doctor-layout-content {
        padding: 18px 12px 28px;
      }
    }
  </style>

  <script>
    window.escapeDoctorToastHtml = function (value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    window.showDoctorToast = function (message, type = 'info', options = {}) {
      const containerId = 'doctor-action-toast-container';
      let container = document.getElementById(containerId);

      if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '2100';
        container.style.marginTop = '6rem';
        document.body.appendChild(container);
      }

      const bgClass = {
        success: 'text-bg-success',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-primary'
      }[type] || 'text-bg-primary';

      const toast = document.createElement('div');
      const safeMessage = window.escapeDoctorToastHtml(message);
      toast.className = `toast align-items-center ${bgClass} border-0 mb-2`;
      toast.setAttribute('role', 'alert');
      toast.setAttribute('aria-live', 'assertive');
      toast.setAttribute('aria-atomic', 'true');
      toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body fw-semibold">${safeMessage}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      `;

      container.appendChild(toast);

      if (window.bootstrap && bootstrap.Toast) {
        const instance = new bootstrap.Toast(toast, {
          autohide: options.autohide !== false,
          delay: options.delay || 3200
        });
        instance.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
      } else {
        toast.classList.add('show');

        if (options.autohide !== false) {
          setTimeout(() => toast.remove(), options.delay || 3200);
        }
      }

      return toast;
    };

    window.confirmDoctorToast = function ({ title = 'Confirm action', message = 'Continue?', confirmText = 'Confirm', cancelText = 'Cancel', type = 'warning', onConfirm }) {
      const containerId = 'doctor-action-toast-container';
      let container = document.getElementById(containerId);

      if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '2100';
        container.style.marginTop = '6rem';
        document.body.appendChild(container);
      }

      const accentClass = type === 'danger' ? 'border-danger' : 'border-warning';
      const confirmClass = type === 'danger' ? 'btn-danger' : 'btn-primary';
      const toast = document.createElement('div');
      const safeTitle = window.escapeDoctorToastHtml(title);
      const safeMessage = window.escapeDoctorToastHtml(message);
      const safeConfirmText = window.escapeDoctorToastHtml(confirmText);
      const safeCancelText = window.escapeDoctorToastHtml(cancelText);
      toast.className = `toast bg-white text-dark ${accentClass} border-2 shadow mb-2`;
      toast.setAttribute('role', 'alert');
      toast.setAttribute('aria-live', 'assertive');
      toast.setAttribute('aria-atomic', 'true');
      toast.innerHTML = `
        <div class="toast-body">
          <div class="fw-bold mb-1">${safeTitle}</div>
          <div class="text-muted small mb-3">${safeMessage}</div>
          <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-doctor-toast-cancel>${safeCancelText}</button>
            <button type="button" class="btn btn-sm ${confirmClass}" data-doctor-toast-confirm>${safeConfirmText}</button>
          </div>
        </div>
      `;

      container.appendChild(toast);

      const instance = window.bootstrap && bootstrap.Toast
        ? new bootstrap.Toast(toast, { autohide: false })
        : null;

      toast.querySelector('[data-doctor-toast-cancel]')?.addEventListener('click', function () {
        instance ? instance.hide() : toast.remove();
      });

      toast.querySelector('[data-doctor-toast-confirm]')?.addEventListener('click', function () {
        if (typeof onConfirm === 'function') {
          onConfirm();
        }
        instance ? instance.hide() : toast.remove();
      });

      if (instance) {
        instance.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
      } else {
        toast.classList.add('show');
      }

      return toast;
    };

    @if (($errors ?? null)?->any())
      document.addEventListener('DOMContentLoaded', function () {
        @foreach ($errors->all() as $error)
          window.showDoctorToast(@json($error), 'danger', { delay: 5200 });
        @endforeach
      });
    @endif
  </script>
@endsection
