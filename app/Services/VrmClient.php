<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VrmClient
{
    private string $token;
    private string $baseUrl = 'https://vrmapi.victronenergy.com/v2';

    public function __construct()
    {
        $this->token = config('services.vrm.token');
    }

    public function getInstallationOverview(int $siteId): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/installations/{$siteId}/overview");
        $response->throw();
        return $response->json('records', []);
    }

    public function getChartData(int $siteId, string $type, int $interval = 900): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/installations/{$siteId}/stats", [
                'type'     => $type,
                'interval' => $interval,
                'start'    => now()->subHours(24)->timestamp,
                'end'      => now()->timestamp,
            ]);
        $response->throw();
        return $response->json('records', []);
    }
}
