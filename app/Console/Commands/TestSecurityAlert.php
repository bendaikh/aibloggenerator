<?php

namespace App\Console\Commands;

use App\Services\WhatsAppNotificationService;
use App\Services\IpGeolocationService;
use Illuminate\Console\Command;

class TestSecurityAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:test-alert {--ip=8.8.8.8 : IP address to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the SuperAdmin login security alert system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Testing SuperAdmin Security Alert System...');
        $this->newLine();

        // Check if Twilio is configured
        $whatsappService = new WhatsAppNotificationService();
        
        if (!$whatsappService->isConfigured()) {
            $this->error('❌ Twilio is not configured!');
            $this->newLine();
            $this->warn('Please set the following in your .env file:');
            $this->line('- TWILIO_SID');
            $this->line('- TWILIO_AUTH_TOKEN');
            $this->line('- TWILIO_WHATSAPP_FROM');
            $this->line('- ADMIN_WHATSAPP_NUMBER');
            $this->newLine();
            $this->info('Then run: php artisan config:clear');
            return Command::FAILURE;
        }

        $this->info('✅ Twilio configuration found');

        // Test IP Geolocation
        $this->info('📍 Testing IP Geolocation Service...');
        $ipAddress = $this->option('ip');
        $geoService = new IpGeolocationService();
        $locationData = $geoService->getLocationData($ipAddress);
        
        $this->line("   IP: {$ipAddress}");
        $this->line("   Location: {$locationData['city']}, {$locationData['region']}, {$locationData['country']}");
        $this->line("   ISP: {$locationData['isp']}");
        $this->newLine();

        // Prepare test data
        $testData = [
            'username' => 'Test SuperAdmin',
            'email' => 'test@example.com',
            'ip_address' => $ipAddress,
            'city' => $locationData['city'],
            'region' => $locationData['region'],
            'country' => $locationData['country'],
            'isp' => $locationData['isp'],
            'browser' => 'Chrome 120.0',
            'platform' => 'Windows 11',
            'timestamp' => now()->format('Y-m-d H:i:s T'),
        ];

        // Send test notification
        $this->info('📱 Sending test WhatsApp notification...');
        $this->info('   To: ' . config('services.twilio.admin_phone'));
        $this->newLine();

        $result = $whatsappService->sendSuperAdminLoginAlert($testData);

        if ($result) {
            $this->info('✅ WhatsApp notification sent successfully!');
            $this->newLine();
            $this->info('Check your WhatsApp at: ' . config('services.twilio.admin_phone'));
            $this->newLine();
            $this->warn('📝 Note: If using sandbox, make sure you\'ve joined with the code from Twilio Console');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send WhatsApp notification');
            $this->newLine();
            $this->warn('Check storage/logs/laravel.log for more details');
            $this->newLine();
            $this->info('Common issues:');
            $this->line('1. Haven\'t joined WhatsApp sandbox (send the join code from Twilio Console)');
            $this->line('2. Invalid credentials in .env');
            $this->line('3. Phone number format is incorrect');
            return Command::FAILURE;
        }
    }
}

