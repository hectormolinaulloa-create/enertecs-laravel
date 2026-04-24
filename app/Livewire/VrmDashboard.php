<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\VrmClient;

class VrmDashboard extends Component
{
    public array  $stats    = [];
    public bool   $error    = false;
    public string $errorMsg = '';

    public function mount(): void
    {
        $this->fetchData();
    }

    public function fetchData(): void
    {
        try {
            $client  = app(VrmClient::class);
            $siteId  = (int) config('services.vrm.site_id');
            $records = $client->getInstallationOverview($siteId);

            $this->stats = [
                'pv_power'     => $records['Pdc']      ?? 0,
                'consumption'  => $records['Pc']       ?? 0,
                'battery_soc'  => $records['bs']       ?? 0,
                'battery_state'=> $records['bs_state'] ?? 'unknown',
                'grid_power'   => $records['Pg']       ?? 0,
            ];
            $this->error = false;
        } catch (\Throwable $e) {
            $this->error    = true;
            $this->errorMsg = 'No se pudo conectar con el sistema VRM.';
        }
    }

    public function render()
    {
        return view('livewire.vrm-dashboard');
    }
}
