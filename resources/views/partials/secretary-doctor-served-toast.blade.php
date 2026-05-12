@auth
<script>
(function () {
  if (window.__secretaryDoctorServedToastLoaded) {
    return;
  }

  window.__secretaryDoctorServedToastLoaded = true;

  const baseDoctorQueueUrl = @json(url('/secretary/services'));

  function getNotificationValue(notification, key) {
    return notification?.[key]
      ?? notification?.data?.[key]
      ?? null;
  }

  function buildDoctorQueueUrl(notification) {
    const directUrl =
      getNotificationValue(notification, 'url')
      ?? getNotificationValue(notification, 'queue_url')
      ?? getNotificationValue(notification, 'redirect_url')
      ?? getNotificationValue(notification, 'action_url');

    if (directUrl) {
      return directUrl;
    }

    const serviceId = getNotificationValue(notification, 'service_id');
    const doctorId = getNotificationValue(notification, 'doctor_id');

    if (serviceId && doctorId) {
      return `${baseDoctorQueueUrl}/${serviceId}/doctors/${doctorId}/queue`;
    }

    return null;
  }

  function isDoctorServedNotification(notification) {
    const type = String(
      getNotificationValue(notification, 'type')
      ?? getNotificationValue(notification, 'notification_type')
      ?? ''
    ).toLowerCase();

    const message = String(getNotificationValue(notification, 'message') ?? '').toLowerCase();

    return type === 'doctor_served'
      || type.includes('doctor_served')
      || message.includes('served')
      || message.includes('done with the doctor');
  }

  function showDoctorServedToast(notification) {
    const message =
      getNotificationValue(notification, 'message')
      ?? 'Doctor marked a patient as served.';

    const targetUrl = buildDoctorQueueUrl(notification);

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Patient served',
        text: message,
        showConfirmButton: true,
        confirmButtonText: targetUrl ? 'Open Queue' : 'OK',
        timer: 9000,
        timerProgressBar: true,
        didOpen: function (toast) {
          if (!targetUrl) return;

          toast.style.cursor = 'pointer';

          toast.addEventListener('click', function (event) {
            const clickedConfirmButton = event.target.closest('.swal2-confirm');

            if (!clickedConfirmButton) {
              window.location.href = targetUrl;
            }
          });
        }
      }).then(function (result) {
        if (result.isConfirmed && targetUrl) {
          window.location.href = targetUrl;
        }
      });

      return;
    }

    alert(message);

    if (targetUrl) {
      window.location.href = targetUrl;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Echo === 'undefined') {
      return;
    }

    Echo.private('user.notifications.{{ auth()->id() }}')
      .listen('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', function (event) {
        const notification = event.notification ?? event;

        const role = String(getNotificationValue(notification, 'role') ?? '').toLowerCase();

        if (role !== 'secretary') {
          return;
        }

        if (!isDoctorServedNotification(notification)) {
          return;
        }

        showDoctorServedToast(notification);
      });
  });
})();
</script>
@endauth