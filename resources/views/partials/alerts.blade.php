@if (session('status'))
  <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert" data-auto-dismiss="5000">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert" data-auto-dismiss="5000">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert" data-auto-dismiss="5000">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert" data-auto-dismiss="5000">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<script>
// Auto-dismiss alerts after specified time
document.addEventListener('DOMContentLoaded', function() {
    const autoDismissAlerts = document.querySelectorAll('.auto-dismiss');
    
    autoDismissAlerts.forEach(function(alert) {
        const dismissTime = parseInt(alert.dataset.autoDismiss) || 1000; // Default 5 seconds
        
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, dismissTime);
    });
});
</script>
