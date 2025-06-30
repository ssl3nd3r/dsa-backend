# OTP Email Verification Implementation

## Overview
This implementation adds email OTP (One-Time Password) verification before user registration to enhance security and ensure email ownership.

## Registration Flow

### Step 1: Send OTP
**Endpoint:** `POST /api/otp/send`
**Request Body:**
```json
{
    "email": "user@example.com"
}
```
**Response:**
```json
{
    "message": "OTP sent successfully",
    "email": "user@example.com"
}
```

### Step 2: Verify OTP
**Endpoint:** `POST /api/otp/verify`
**Request Body:**
```json
{
    "email": "user@example.com",
    "otp_code": "123456"
}
```
**Response:**
```json
{
    "message": "OTP verified successfully",
    "email": "user@example.com",
    "verified": true
}
```

### Step 3: Register User (After OTP Verification)
**Endpoint:** `POST /api/register`
**Request Body:**
```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "lifestyle": "active",
    "personality_traits": ["extrovert", "organized"],
    "work_schedule": "9-5",
    "cultural_preferences": ["vegetarian"]
}
```

**Note:** The `otp_code` is NOT required in the registration payload. The system checks if the email has been verified with OTP in a previous step.

## Additional OTP Endpoints

### Resend OTP
**Endpoint:** `POST /api/otp/resend`
**Request Body:**
```json
{
    "email": "user@example.com"
}
```

## Features

### Security Features
- **6-digit OTP codes** generated randomly
- **10-minute expiration** for security
- **One-time use** - OTP becomes invalid after verification
- **Email verification window** - Email remains verified for 30 minutes after OTP expiry
- **Rate limiting** - Prevents multiple OTP requests for the same email
- **Email validation** - Ensures email format and uniqueness

### Database Schema
The `otps` table includes:
- `email` - User's email address
- `otp_code` - 6-digit verification code
- `expires_at` - Expiration timestamp
- `is_used` - Boolean flag for one-time use
- `type` - OTP type (registration, password_reset, etc.)

### Email Template
- Professional HTML email template
- Clear OTP display with styling
- Security warnings and instructions
- Responsive design

## Configuration

### Mail Configuration
Update your `.env` file with your email service credentials:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Your App Name"
```

### For Development
For testing, you can use the `log` driver which will write emails to `storage/logs/laravel.log`:
```env
MAIL_MAILER=log
```

## Error Handling

### Common Error Responses
- **400** - Invalid email format or user already exists
- **400** - Invalid or expired OTP
- **400** - OTP already sent (rate limiting)
- **400** - Email not verified (when trying to register without OTP verification)
- **500** - Email sending failure

### Validation Rules
- Email must be valid format and unique
- OTP must be exactly 6 digits
- All registration fields are required
- Email must be verified with OTP before registration

## Testing

### Manual Testing
1. Send OTP: `POST /api/otp/send`
2. Check email for OTP code
3. Verify OTP: `POST /api/otp/verify`
4. Register user: `POST /api/register` (without OTP in payload)

### Using Log Driver
When using `MAIL_MAILER=log`, check `storage/logs/laravel.log` for the email content and OTP code.

## Security Considerations

1. **OTP Expiration**: 10-minute window prevents long-term code reuse
2. **One-time Use**: OTP becomes invalid after first use
3. **Verification Window**: Email remains verified for 30 minutes after OTP expiry
4. **Rate Limiting**: Prevents spam and abuse
5. **Email Validation**: Ensures email ownership
6. **Secure Storage**: OTP codes are hashed in database
7. **Clear Instructions**: Email template includes security warnings

## Future Enhancements

- SMS OTP support
- Voice call OTP
- Biometric verification
- Multi-factor authentication
- OTP for password reset
- Account recovery options 