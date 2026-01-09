@once
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmActionTitle">Please Confirm</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="confirmActionMessage">Are you sure you want to continue?</p>
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
    if (!modalEl || !continueBtn || !titleEl || !messageEl || typeof bootstrap === 'undefined') {
      return;
    }
    const modal = new bootstrap.Modal(modalEl);
    let pendingForm = null;

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
      pendingForm = target;
      titleEl.textContent = target.dataset.confirmTitle || 'Please Confirm';
      messageEl.textContent = target.dataset.confirm || 'Are you sure you want to continue?';
      continueBtn.textContent = target.dataset.confirmBtn || 'Confirm';
      continueBtn.disabled = false;
      modal.show();
    }, true);

    continueBtn.addEventListener('click', function() {
      if (!pendingForm) return;
      continueBtn.disabled = true;
      pendingForm.dataset.confirmed = 'true';
      modal.hide();
      pendingForm.requestSubmit();
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
      continueBtn.disabled = false;
      pendingForm = null;
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
