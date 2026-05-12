@once
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmActionTitle">Please Confirm</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-0" id="confirmActionMessage">Are you sure you want to continue?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmActionContinue">Confirm</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const ready = () => {
    const modalEl = document.getElementById('confirmActionModal');
    const continueBtn = document.getElementById('confirmActionContinue');
    const titleEl = document.getElementById('confirmActionTitle');
    const messageEl = document.getElementById('confirmActionMessage');
    const cancelBtn = modalEl ? modalEl.querySelector('.modal-footer [data-bs-dismiss="modal"]') : null;
    const headerEl = modalEl ? modalEl.querySelector('.modal-header') : null;
    if (!modalEl || !continueBtn || !titleEl || !messageEl || typeof bootstrap === 'undefined') {
      return;
    }
    const modal = new bootstrap.Modal(modalEl);
    let pendingForm = null;
    let messageOnly = false;

    const resetModal = () => {
      messageOnly = false;
      pendingForm = null;
      titleEl.textContent = 'Please Confirm';
      messageEl.textContent = 'Are you sure you want to continue?';
      continueBtn.textContent = 'Confirm';
      continueBtn.disabled = false;
      continueBtn.className = 'btn btn-danger';
      if (cancelBtn) {
        cancelBtn.classList.remove('d-none');
      }
      if (headerEl) {
        headerEl.className = 'modal-header bg-danger text-white';
      }
    };

    window.showGlobalModal = function(options) {
      const config = typeof options === 'string' ? { message: options } : (options || {});
      const type = config.type || 'danger';
      const headerClasses = {
        danger: 'modal-header bg-danger text-white',
        warning: 'modal-header bg-warning text-dark',
        success: 'modal-header bg-success text-white',
        info: 'modal-header bg-primary text-white',
      };
      const buttonClasses = {
        danger: 'btn btn-danger',
        warning: 'btn btn-warning',
        success: 'btn btn-success',
        info: 'btn btn-primary',
      };

      messageOnly = true;
      pendingForm = null;
      titleEl.textContent = config.title || 'Notice';
      messageEl.textContent = config.message || 'Something went wrong. Please try again.';
      continueBtn.textContent = config.buttonText || 'OK';
      continueBtn.disabled = false;
      continueBtn.className = buttonClasses[type] || buttonClasses.danger;
      if (cancelBtn) {
        cancelBtn.classList.add('d-none');
      }
      if (headerEl) {
        headerEl.className = headerClasses[type] || headerClasses.danger;
      }
      modal.show();
    };

    document.body.addEventListener('submit', function(event) {
      const target = event.target;
      if (!target || !target.matches('form[data-confirm]')) {
        return;
      }
      if (target.dataset.confirmed === 'true') {
        delete target.dataset.confirmed;
        return;
      }
      event.preventDefault();
      messageOnly = false;
      pendingForm = target;
      titleEl.textContent = target.dataset.confirmTitle || 'Please Confirm';
      if (target.dataset.confirmHtml) {
        messageEl.innerHTML = target.dataset.confirmHtml;
      } else {
        messageEl.textContent = target.dataset.confirm || 'Are you sure you want to continue?';
      }
      continueBtn.textContent = target.dataset.confirmBtn || 'Confirm';
      continueBtn.disabled = false;
      modal.show();
    }, true);

    continueBtn.addEventListener('click', function() {
      if (messageOnly) {
        modal.hide();
        return;
      }
      if (!pendingForm) return;
      continueBtn.disabled = true;
      pendingForm.dataset.confirmed = 'true';
      modal.hide();
      pendingForm.requestSubmit();
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
      resetModal();
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }
})();
</script>
@endpush
@endonce
