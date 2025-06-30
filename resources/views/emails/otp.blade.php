<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'login' ? 'Login Verification OTP' : 'Email Verification OTP' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .otp-code {
            background-color: #4F46E5;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background-color: #FEF3C7;
            border: 1px solid #F59E0B;
            color: #92400E;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $type === 'login' ? 'Login Verification' : 'Email Verification' }}</h1>
    </div>
    
    <div class="content">
        <h2>Hello!</h2>
        
        @if($type === 'login')
            <p>We received a login request for your account. To complete your login, please use the following verification code:</p>
        @else
            <p>Thank you for registering with our platform. To complete your registration, please use the following verification code:</p>
        @endif
        
        <div class="otp-code">
            {{ $otpCode }}
        </div>
        
        <p>This code will expire in 10 minutes for security reasons.</p>
        
        <div class="warning">
            <strong>Important:</strong> Never share this code with anyone. Our team will never ask for this code via phone, email, or any other communication method.
        </div>
        
        @if($type === 'login')
            <p>If you didn't attempt to login to your account, please change your password immediately and contact our support team.</p>
        @else
            <p>If you didn't request this verification code, please ignore this email.</p>
        @endif
        
        <p>Best regards,<br>The Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} All rights reserved.</p>
    </div>
</body>
</html> 