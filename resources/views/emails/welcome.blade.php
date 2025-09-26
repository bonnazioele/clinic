<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Thank you for joining our application.</p>
    <p>We're excited to have you on board.</p>
    
    <a href="{{ url('localhost:8000/login') }}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Go to Login Page
    </a>
</body>
</html>