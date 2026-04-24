<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VrmClient
{
    private string $token;
    private string $base = 'https://vrmapi.victronenergy.com/v2';
    private ?int $idUser = null;

    public function __construct()
    {
        $this->token = config('services.vrm.token', '');
    }

    public function login(): int
    {
        $res = $this->req('GET', '/users/me');
        $this->idUser = $res['user']['id'] ?? null;
        if (!$this->idUser) {
            throw new \RuntimeException('VRM login: no se obtuvo idUser');
        }
        return $this->idUser;
    }

    public function getInstallations(int $idUser): array
    {
        return $this->req('GET', "/users/{$idUser}/installations", ['extended' => 1])['records'] ?? [];
    }

    public function getOverallStats(int $siteId): array
    {
        return $this->req('GET', "/installations/{$siteId}/overallstats", [
            'type' => 'kwh', 'interval' => 'days',
        ])['records'] ?? [];
    }

    public function getDailyStats(int $siteId, int $start, int $end): array
    {
        return $this->req('GET', "/installations/{$siteId}/stats", [
            'type' => 'kwh', 'interval' => 'days', 'start' => $start, 'end' => $end,
        ])['records'] ?? [];
    }

    public function getHourlyStats(int $siteId, int $start, int $end): array
    {
        return $this->req('GET', "/installations/{$siteId}/stats", [
            'type' => 'kwh', 'interval' => 'hours', 'start' => $start, 'end' => $end,
        ])['records'] ?? [];
    }

    public function getStatusWidget(int $siteId): array
    {
        return $this->req('GET', "/installations/{$siteId}/widgets/Status")['records'] ?? [];
    }

    public function getDiagnostics(int $siteId): array
    {
        return $this->req('GET', "/installations/{$siteId}/diagnostics")['records'] ?? [];
    }

    private function req(string $method, string $path, array $query = []): array
    {
        $response = Http::withHeaders(['X-Authorization' => "Token {$this->token}"])
            ->timeout(15)
            ->{strtolower($method)}($this->base . $path, $query);

        $response->throw();
        return $response->json() ?? [];
    }
}
