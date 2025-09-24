<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Clinic Approved</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; color:#111827; }
        .container { max-width: 640px; margin: 0 auto; padding: 20px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; }
        .btn { display:inline-block; background:#2563eb; color:#fff !important; text-decoration:none; padding:10px 16px; border-radius:6px; }
        .muted { color:#6b7280; font-size: 12px; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; }
    </style>
  </head>
  <body>
    <div class="container">
      <h2>Clinic Approved</h2>
      <div class="card">
        <p>Hello {{ $name }},</p>
        <p>
          Your clinic <strong>{{ $clinicName }}</strong> has been approved. You can now sign in to manage your clinic.
        </p>

        <p>
          <strong>Login Email:</strong> <code>{{ $email }}</code><br>
          @if($password)
            <strong>Temporary Password:</strong> <code>{{ $password }}</code>
          @else
            <strong>Password:</strong> Your existing account password
          @endif
        </p>

        <p>
          <a class="btn" href="{{ $loginUrl }}" target="_blank" rel="noopener">Sign in</a>
        </p>

        <p class="muted">
          For security, please change your password after signing in. If you did not request this, please contact support.
        </p>
      </div>
      <p class="muted">This is an automated message, please do not reply.</p>
    </div>
  </body>
</html>
