document.addEventListener('DOMContentLoaded', function () {
    let selectedClinicId = null;
    const modalElement = document.getElementById('approveModal');
    const approveModal = new bootstrap.Modal(modalElement);

    // Setup approve button click handlers
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            
            selectedClinicId = this.getAttribute('data-id');
            const clinicName = this.getAttribute('data-name');
            
            document.getElementById('approveClinicName').textContent = clinicName;
            document.getElementById('approveError').classList.add('d-none');
            
            approveModal.show();
        });
    });

    // Confirm approve button
    document.getElementById('confirmApproveBtn').addEventListener('click', function () {
        if (!selectedClinicId) return;

        // Disable button and show loading state
        const confirmBtn = this;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Approving...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        axios.post(`/admin/clinics/${selectedClinicId}/approve`, {}, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.data.success) {
                // Show success message
                showAlert('success', response.data.message);
                
                // Hide modal
                approveModal.hide();
                
                // Remove clinic row from table
                const clinicRow = document.getElementById('clinic-row-' + selectedClinicId);
                if (clinicRow) {
                    clinicRow.remove();
                }
                
                // Reset selection
                selectedClinicId = null;
            }
        })
        .catch(error => {
            let msg = 'An error occurred while approving the clinic.';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            
            // Show error in modal
            document.getElementById('approveError').textContent = msg;
            document.getElementById('approveError').classList.remove('d-none');
            
            // Also show page-level alert
            showAlert('error', msg);
        })
        .finally(() => {
            // Re-enable button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Approve';
        });
    });

    // Clean up modal events
    modalElement.addEventListener('hidden.bs.modal', function () {
        selectedClinicId = null;
        document.getElementById('approveError').classList.add('d-none');
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertContainer = document.querySelector('.container-fluid.px-4');
        if (!alertContainer) return;

        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show mt-3" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss success alerts after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert-success');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    }
});
