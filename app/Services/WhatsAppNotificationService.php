<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WhatsAppNotificationService
{
    protected $twilioSid;
    protected $twilioAuthToken;
    protected $twilioWhatsAppNumber;
    protected $client;

    public function __construct()
    {
        // Try to get credentials from authenticated user first
        $user = Auth::user();
        
        if ($user && !empty($user->twilio_sid) && !empty($user->twilio_auth_token)) {
            $this->twilioSid = $user->twilio_sid;
            $this->twilioAuthToken = $user->twilio_auth_token;
            $this->twilioWhatsAppNumber = $user->twilio_whatsapp_from ?? config('services.twilio.whatsapp_from');
        } else {
            // Fall back to config/env
            $this->twilioSid = config('services.twilio.sid');
            $this->twilioAuthToken = config('services.twilio.auth_token');
            $this->twilioWhatsAppNumber = config('services.twilio.whatsapp_from');
        }

        if ($this->isConfigured()) {
            $this->client = new Client($this->twilioSid, $this->twilioAuthToken);
        }
    }

    /**
     * Check if Twilio is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->twilioSid) 
            && !empty($this->twilioAuthToken) 
            && !empty($this->twilioWhatsAppNumber);
    }

    /**
     * Send a WhatsApp message.
     *
     * @param string $to Phone number with country code (e.g., +212634741761)
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('WhatsApp notification not sent - Twilio not configured');
            return false;
        }

        try {
            // Ensure phone number is in the correct format
            if (!str_starts_with($to, '+')) {
                $to = '+' . $to;
            }

            // Remove any spaces or dashes from phone number
            $to = 'whatsapp:' . str_replace([' ', '-'], '', $to);
            $from = 'whatsapp:' . $this->twilioWhatsAppNumber;

            $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message
                ]
            );

            Log::info('WhatsApp notification sent successfully', [
                'to' => $to,
                'message_preview' => substr($message, 0, 100)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send superadmin login alert.
     *
     * @param array $loginData
     * @return bool
     */
    public function sendSuperAdminLoginAlert(array $loginData): bool
    {
        // Try to get admin phone from authenticated user first
        $user = Auth::user();
        $adminPhone = null;
        
        if ($user && !empty($user->admin_whatsapp_number)) {
            $adminPhone = $user->admin_whatsapp_number;
        } else {
            $adminPhone = config('services.twilio.admin_phone');
        }

        if (empty($adminPhone)) {
            Log::warning('Admin phone number not configured for security alerts');
            return false;
        }

        $message = $this->formatSuperAdminLoginMessage($loginData);
        
        return $this->sendMessage($adminPhone, $message);
    }

    /**
     * Format the superadmin login alert message.
     *
     * @param array $loginData
     * @return string
     */
    private function formatSuperAdminLoginMessage(array $loginData): string
    {
        $lines = [
            "🚨 SECURITY ALERT 🚨",
            "",
            "SuperAdmin Login Detected!",
            "",
            "👤 User: {$loginData['username']}",
            "📧 Email: {$loginData['email']}",
            "",
            "📍 Location Details:",
            "   IP: {$loginData['ip_address']}",
            "   City: {$loginData['city']}",
            "   Region: {$loginData['region']}",
            "   Country: {$loginData['country']}",
            "   ISP: {$loginData['isp']}",
            "",
            "🌐 Browser Info:",
            "   {$loginData['browser']}",
            "   {$loginData['platform']}",
            "",
            "🕐 Time: {$loginData['timestamp']}",
            "",
            "If this wasn't you, please secure your account immediately!"
        ];

        return implode("\n", $lines);
    }
}
