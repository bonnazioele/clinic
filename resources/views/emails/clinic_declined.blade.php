<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Clinic Application Update</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; 
            color: #111827; 
            line-height: 1.6;
        }
        .container { max-width: 640px; margin: 0 auto; padding: 20px; }
        .card { 
            border: 1px solid #e5e7eb; 
            border-radius: 8px; 
            padding: 24px; 
            margin-bottom: 20px;
        }
        .header {
            background: linear-gradient(135deg, #f87171, #dc2626);
            color: white;
            text-align: center;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .reason-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }
        .btn { 
            display: inline-block; 
            background: #2563eb; 
            color: #fff !important; 
            text-decoration: none; 
            padding: 12px 20px; 
            border-radius: 6px; 
            font-weight: 500;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .muted { 
            color: #6b7280; 
            font-size: 14px; 
        }
        .small { font-size: 12px; }
        code { 
            background: #f3f4f6; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-family: Monaco, Consolas, 'Courier New', monospace;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📋</div>
            <h2 style="margin: 0;">Clinic Application Update</h2>
        </div>

        <div class="card">
            <p><strong>Hello {{ $name }},</strong></p>
            
            <p>
                Thank you for your interest in joining our healthcare network. We have carefully reviewed your clinic application for <strong>{{ $clinicName }}</strong>.
            </p>

            <p>
                Unfortunately, we are unable to approve your application at this time. Please see the reason below:
            </p>

            <div class="reason-box">
                <h4 style="margin-top: 0; color: #dc2626;">
                    <strong>📝 Reason for Decline:</strong>
                </h4>
                <p style="margin-bottom: 0;">{{ $reason }}</p>
            </div>

            <p>
                <strong>What you can do next:</strong>
            </p>
            
            <ul>
                <li>Review the feedback provided above</li>
                <li>Address the mentioned concerns</li>
                <li>Submit a new application when ready</li>
                <li>Contact our support team if you need clarification</li>
            </ul>

            <div style="text-align: center; margin: 32px 0;">
                <a class="btn" href="{{ $reapplyUrl }}" target="_blank" rel="noopener">
                    🔄 Submit New Application
                </a>
            </div>

            <p class="muted">
                <strong>Need Help?</strong><br>
                If you have questions about this decision or need assistance with your application, please don't hesitate to contact our support team. We're here to help you succeed.
            </p>
        </div>

        <div style="text-align: center;">
            <p class="muted small">
                This is an automated message regarding your clinic application. Please do not reply directly to this email.
            </p>
            <p class="muted small">
                <strong>Application Email:</strong> <code>{{ $email }}</code><br>
                <strong>Clinic Name:</strong> {{ $clinicName }}
            </p>
        </div>
    </div>
</body>
</html>