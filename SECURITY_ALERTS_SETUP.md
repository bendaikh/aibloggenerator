# SuperAdmin Login Security Alert System

This security system sends WhatsApp notifications whenever someone logs in as a superadmin, helping you detect unauthorized access attempts.

## 📋 What's Included

### 1. **IP Geolocation Service** (`app/Services/IpGeolocationService.php`)
- Automatically detects the geographical location of login attempts
- Uses ip-api.com (free, no API key required)
- Caches results to avoid rate limits
- Handles local/private IPs gracefully

### 2. **WhatsApp Notification Service** (`app/Services/WhatsAppNotificationService.php`)
- Sends WhatsApp messages via Twilio
- Formats comprehensive security alerts
- Includes error handling and logging

### 3. **Enhanced Authentication Controller**
- Automatically detects superadmin logins
- Captures: IP address, location, browser, platform, username, email, timestamp
- Sends real-time alerts without blocking the login process

## 🚀 Setup Instructions

### Step 1: Create a Twilio Account

1. Go to [https://www.twilio.com/try-twilio](https://www.twilio.com/try-twilio)
2. Sign up for a free account
3. Verify your email and phone number

### Step 2: Set Up WhatsApp Sandbox (for Testing)

1. In your Twilio Console, go to **Messaging** → **Try it out** → **Send a WhatsApp message**
2. Follow the instructions to connect your WhatsApp number:
   - You'll see a sandbox number (e.g., `+1 415 523 8886`)
   - Send a message like `join <your-sandbox-code>` from your WhatsApp (+212634741761)
   - Example: `join happy-elephant`
3. Your WhatsApp number is now connected to the sandbox!

### Step 3: Get Your Twilio Credentials

1. In your Twilio Console Dashboard, find:
   - **Account SID** (looks like: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)
   - **Auth Token** (click "View" to reveal it)
   - **WhatsApp From Number** (the sandbox number, e.g., `+14155238886`)

### Step 4: Update Your .env File

Open your `.env` file and update these values:

```env
# Twilio WhatsApp Configuration
TWILIO_SID=your_actual_account_sid_here
TWILIO_AUTH_TOKEN=your_actual_auth_token_here
TWILIO_WHATSAPP_FROM=+14155238886
ADMIN_WHATSAPP_NUMBER=+212634741761
```

**Important:** Replace the placeholder values with your actual Twilio credentials!

### Step 5: Clear Configuration Cache

Run this command to refresh your configuration:

```bash
php artisan config:clear
```

## 🧪 Testing the System

### Test the notification manually:

You can test the WhatsApp notification system using Laravel Tinker:

```bash
php artisan tinker
```

Then run this code:

```php
$service = new App\Services\WhatsAppNotificationService();

$testData = [
    'username' => 'Test User',
    'email' => 'test@example.com',
    'ip_address' => '8.8.8.8',
    'city' => 'Mountain View',
    'region' => 'California',
    'country' => 'United States',
    'isp' => 'Google LLC',
    'browser' => 'Chrome 120.0',
    'platform' => 'Windows 10',
    'timestamp' => now()->format('Y-m-d H:i:s T'),
];

$service->sendSuperAdminLoginAlert($testData);
```

If configured correctly, you should receive a WhatsApp message at **+212 634-741761**.

### Test with actual login:

1. Make sure you have a superadmin user in your database
2. Log in as that superadmin user
3. You should receive a WhatsApp notification immediately!

## 📱 WhatsApp Message Format

When a superadmin logs in, you'll receive a message like this:

```
🚨 SECURITY ALERT 🚨

SuperAdmin Login Detected!

👤 User: John Doe
📧 Email: admin@example.com

📍 Location Details:
   IP: 123.45.67.89
   City: Casablanca
   Region: Casablanca-Settat
   Country: Morocco
   ISP: Maroc Telecom

🌐 Browser Info:
   Chrome 120.0
   Windows 10

🕐 Time: 2026-04-22 15:30:45 UTC

If this wasn't you, please secure your account immediately!
```

## 🔒 Security Features

1. **Real-time Alerts**: Notifications are sent immediately when a superadmin logs in
2. **Comprehensive Information**: Includes IP, location, browser, and timestamp
3. **Non-blocking**: Login process continues even if notification fails
4. **Error Logging**: All errors are logged for troubleshooting
5. **IP Caching**: Location data is cached to avoid rate limits

## 🚨 Production Setup (Going Live)

For production use (not sandbox), you need to:

1. **Upgrade to a Paid Twilio Account** (required for production WhatsApp)
2. **Request WhatsApp Business API Access** from Twilio
3. **Get Your WhatsApp Number Approved** by Facebook/Meta
4. **Update Your Configuration** with the production WhatsApp number

Twilio provides detailed instructions for this process in their console.

## 🛠️ Troubleshooting

### "WhatsApp notification not sent - Twilio not configured"
- Check that all environment variables are set in `.env`
- Run `php artisan config:clear`
- Make sure there are no typos in your credentials

### "Failed to send WhatsApp notification"
- Check `storage/logs/laravel.log` for detailed error messages
- Verify your Twilio credentials are correct
- Make sure you've joined the WhatsApp sandbox
- Verify the phone number format (+212634741761)

### "IP Geolocation API failed"
- This is usually not a blocker - the system will use default values
- Check your internet connection
- The service uses a free API with rate limits (45 requests/minute)

## 📝 Files Modified/Created

- ✅ `app/Services/IpGeolocationService.php` - IP location lookup
- ✅ `app/Services/WhatsAppNotificationService.php` - WhatsApp messaging
- ✅ `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Login detection
- ✅ `config/services.php` - Twilio configuration
- ✅ `.env` - Environment variables

## 🔐 Privacy & Data Protection

- IP addresses are cached for 24 hours only
- All sensitive data is logged securely
- No data is sent to third parties except Twilio for messaging
- Location data comes from a privacy-friendly free API

## 💡 Additional Tips

1. **Test in sandbox first** before going to production
2. **Monitor your logs** during the first few logins
3. **Keep your Auth Token secret** - never commit it to Git
4. **Set up alerts for other roles** by modifying the controller

## 🆘 Support

If you encounter any issues:
1. Check `storage/logs/laravel.log`
2. Verify all configuration steps were followed
3. Test with the Tinker example first
4. Check Twilio Console for message delivery status

---

**Security Notice**: This system helps you monitor superadmin access. If you receive an alert for a login you didn't make, immediately:
1. Change your password
2. Enable 2FA if available
3. Check your recent account activity
4. Review other admin accounts for suspicious activity
