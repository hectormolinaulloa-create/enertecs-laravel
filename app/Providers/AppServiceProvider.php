<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Helper VRM: color según SOC
        Blade::directive('socColor', fn($soc) => "<?php echo \\App\\Providers\\AppServiceProvider::socColor($soc); ?>");

        // Funciones helper globales para vistas VRM
        require_once app_path('Helpers/VrmHelpers.php');

        // Rate limiter para la calculadora solar (subida de PDF via Livewire)
        // Se aplica a los endpoints de polling e informe en routes/web.php via throttle:calculadora
        RateLimiter::for('calculadora', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
