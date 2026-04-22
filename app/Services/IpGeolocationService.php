<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IpGeolocationService
{
    /**
     * Get geolocation data for an IP address.
     * Uses ip-api.com (free service, no API key required)
     *
     * @param string $ipAddress
     * @return array
     */
    public function getLocationData(string $ipAddress): array
    {
        // Skip lookup for local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return [
                'country' => 'Local Network',
                'region' => 'N/A',
                'city' => 'Localhost',
                'timezone' => config('app.timezone'),
                'isp' => 'Local Network',
            ];
        }

        // Cache the result for 24 hours to avoid hitting rate limits
        $cacheKey = "ip_location_{$ipAddress}";
        
        return Cache::remember($cacheKey, 86400, function () use ($ipAddress) {
            try {
                $response = Http::timeout(10)->get("http://ip-api.com/json/{$ipAddress}", [
                    'fields' => 'status,message,country,regionName,city,timezone,isp,query'
                ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    $data = $response->json();
                    return [
                        'country' => $data['country'] ?? 'Unknown',
                        'region' => $data['regionName'] ?? 'Unknown',
                        'city' => $data['city'] ?? 'Unknown',
                        'timezone' => $data['timezone'] ?? 'Unknown',
                        'isp' => $data['isp'] ?? 'Unknown',
                    ];
                }

                Log::warning('IP Geolocation API failed', [
                    'ip' => $ipAddress,
                    'response' => $response->json()
                ]);

                return $this->getDefaultLocationData();
            } catch (\Exception $e) {
                Log::error('IP Geolocation Service Error', [
                    'ip' => $ipAddress,
                    'error' => $e->getMessage()
                ]);

                return $this->getDefaultLocationData();
            }
        });
    }

    /**
     * Check if IP is a local/private address.
     *
     * @param string $ipAddress
     * @return bool
     */
    private function isLocalIp(string $ipAddress): bool
    {
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Check for private IP ranges
        return !filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Get default location data when lookup fails.
     *
     * @return array
     */
    private function getDefaultLocationData(): array
    {
        return [
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'timezone' => 'Unknown',
            'isp' => 'Unknown',
        ];
    }

    /**
     * Get formatted location string.
     *
     * @param string $ipAddress
     * @return string
     */
    public function getFormattedLocation(string $ipAddress): string
    {
        $location = $this->getLocationData($ipAddress);
        
        return sprintf(
            "%s, %s, %s (ISP: %s)",
            $location['city'],
            $location['region'],
            $location['country'],
            $location['isp']
        );
    }
}
