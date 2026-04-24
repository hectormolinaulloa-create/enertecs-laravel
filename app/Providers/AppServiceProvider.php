<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
    }
}
