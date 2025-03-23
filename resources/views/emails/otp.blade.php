<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
</head>
<body>
    <h2>Hello {{ $username }},</h2>
    <p>Your OTP code is: <strong>{{ $otp }}</strong></p>
    <p>This code will expire in 10 minutes.</p>
</body>
</html>

