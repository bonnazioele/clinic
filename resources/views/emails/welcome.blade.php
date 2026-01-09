<!DOCTYPE html>
<html>
<head>
    <title>Welcome to CliniQ</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Your patient account is ready. Use the credentials below to sign in:</p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Temporary Password:</strong> {{ $password }}</li>
    </ul>
    <p>Please change your password after your first login.</p>

    <a href="{{ $loginUrl ?? route('login') }}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Go to Login Page
    </a>
</body>
</html>
