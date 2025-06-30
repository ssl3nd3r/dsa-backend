# Login OTP Implementation

## Overview
This implementation adds a two-step login process with OTP verification for enhanced security. The login flow now requires users to:
1. Provide email and password
2. Receive and verify OTP via email
3. Complete login with valid OTP

## Login Flow

### Step 1: Initial Login (Email + Password)
**Endpoint:** `POST /api/login`
**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "message": "Login OTP sent successfully. Please check your email.",
    "requires_otp": true,
    "email": "user@example.com"
}
```

**Error Responses:**
- **401** - Invalid credentials
- **401** - Account is deactivated
- **500** - Failed to send OTP

### Step 2: Complete Login (OTP Verification)
**Endpoint:** `POST /api/login/complete`
**Request Body:**
```json
{
    "email": "user@example.com",
    "otp_code": "123456"
}
```

**Success Response (200):**
```json
{
    "message": "Login successful",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        // ... other user data
    }
}
```

**Error Responses:**
- **400** - Invalid or expired OTP
- **404** - User not found
- **401** - Account is deactivated

## Additional OTP Endpoints

### Send Login OTP (Alternative)
**Endpoint:** `POST /api/otp/login/send`
**Request Body:**
```json
{
    "email": "user@example.com"
}
```
**Note:** This endpoint can be used to resend OTP if needed, but requires the user to exist.

### Verify Login OTP (Alternative)
**Endpoint:** `POST /api/otp/login/verify`
**Request Body:**
```json
{
    "email": "user@example.com",
    "otp_code": "123456"
}
```

### Resend Login OTP
**Endpoint:** `POST /api/otp/login/resend`
**Request Body:**
```json
{
    "email": "user@example.com"
}
```

## Frontend Implementation Example

### React/JavaScript Example
```javascript
// Step 1: Initial login
const handleLogin = async (email, password) => {
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (response.ok && data.requires_otp) {
            // Show OTP input form
            setShowOtpForm(true);
            setUserEmail(email);
        } else {
            // Handle error
            setError(data.error);
        }
    } catch (error) {
        setError('Login failed. Please try again.');
    }
};

// Step 2: Complete login with OTP
const handleOtpVerification = async (otpCode) => {
    try {
        const response = await fetch('/api/login/complete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                email: userEmail, 
                otp_code: otpCode 
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Store token and redirect
            localStorage.setItem('token', data.token);
            setUser(data.user);
            navigate('/dashboard');
        } else {
            setOtpError(data.error);
        }
    } catch (error) {
        setOtpError('OTP verification failed. Please try again.');
    }
};
```

### Flutter/Dart Example
```dart
// Step 1: Initial login
Future<void> login(String email, String password) async {
  try {
    final response = await http.post(
      Uri.parse('$baseUrl/api/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);
    
    if (response.statusCode == 200 && data['requires_otp'] == true) {
      // Show OTP input screen
      Navigator.pushNamed(context, '/otp-verification', arguments: email);
    } else {
      // Handle error
      throw Exception(data['error']);
    }
  } catch (e) {
    // Handle error
  }
}

// Step 2: Complete login with OTP
Future<void> completeLogin(String email, String otpCode) async {
  try {
    final response = await http.post(
      Uri.parse('$baseUrl/api/login/complete'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'otp_code': otpCode,
      }),
    );

    final data = jsonDecode(response.body);
    
    if (response.statusCode == 200) {
      // Store token and navigate
      await storage.write(key: 'token', value: data['token']);
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      throw Exception(data['error']);
    }
  } catch (e) {
    // Handle error
  }
}
```

## Security Features

### OTP Security
- **6-digit codes** generated randomly
- **10-minute expiration** for security
- **One-time use** - OTP becomes invalid after verification
- **Rate limiting** - Prevents multiple OTP requests
- **Email validation** - Ensures email ownership

### Login Flow Security
- **Two-factor authentication** - Requires both password and OTP
- **Credential validation first** - Prevents OTP spam for invalid accounts
- **Account status check** - Deactivated accounts cannot receive OTPs
- **Secure token generation** - Uses Laravel Sanctum for API tokens

## Error Handling

### Common Error Scenarios
1. **Invalid credentials** - User enters wrong email/password
2. **Account deactivated** - User account is disabled
3. **OTP expired** - User waits too long to enter OTP
4. **Invalid OTP** - User enters wrong OTP code
5. **Email sending failure** - Technical issues with email delivery

### Error Response Format
```json
{
    "error": "Error message description"
}
```

## Configuration

### Mail Configuration
Ensure your `.env` file has proper mail settings:
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
Use the `log` driver to write emails to `storage/logs/laravel.log`:
```env
MAIL_MAILER=log
```

## Testing

### Manual Testing Flow
1. **Step 1:** `POST /api/login` with valid credentials
2. **Check email** for OTP code (or check logs if using log driver)
3. **Step 2:** `POST /api/login/complete` with email and OTP
4. **Verify** successful login and token generation

### API Testing with Postman
1. Create a collection for login flow
2. Set up environment variables for base URL
3. Test both endpoints in sequence
4. Verify token is received and valid

## Benefits

1. **Enhanced Security** - Two-factor authentication
2. **Account Protection** - Prevents unauthorized access
3. **User Verification** - Ensures email ownership
4. **Audit Trail** - Login attempts are logged
5. **Flexible Implementation** - Can be made optional per user preference

## Future Enhancements

- **SMS OTP** as alternative to email
- **Remember device** option to skip OTP for trusted devices
- **Biometric authentication** integration
- **Login attempt monitoring** and blocking
- **User preference settings** for OTP frequency 