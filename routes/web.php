<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\CalculadoraController;

Route::get('/git-status', function () {
    if (request('token') !== config('app.migration_token')) abort(403);
    $base = base_path();
    $out = shell_exec("cd {$base} && git status 2>&1");
    return response("<pre>{$out}</pre>", 200)->header('Content-Type', 'text/html');
});

Route::get('/git-reset', function () {
    if (request('token') !== config('app.migration_token')) abort(403);
    $base = base_path();
    $out = shell_exec("cd {$base} && git checkout -- . 2>&1 && git clean -fd 2>&1");
    return response("<pre>{$out}</pre>", 200)->header('Content-Type', 'text/html');
});

Route::get('/', [PaginaController::class, 'home'])->name('home');
Route::get('/nosotros', [PaginaController::class, 'nosotros'])->name('nosotros');
Route::get('/experiencia', [PaginaController::class, 'experiencia'])->name('experiencia');
Route::get('/servicios', [PaginaController::class, 'servicios'])->name('servicios');
Route::get('/servicios/{slug}', [PaginaController::class, 'servicio'])->name('servicio');
Route::get('/calculadora/solar-ongrid', [PaginaController::class, 'calculadora'])->name('calculadora');

// API VRM chart data
Route::get('/api/vrm/chart', [CalculadoraController::class, 'vrmChart'])
     ->middleware('throttle:30,1')
     ->name('vrm.chart');

// Endpoints de la calculadora
// Nota: la ruta Livewire /calculadora/solar-ongrid es gestionada por Livewire internamente;
// el rate limiting de subida de PDF se controla desde el componente CalculadoraWizard.
// El named limiter 'calculadora' (20 req/min por IP) está definido en AppServiceProvider.
Route::middleware('throttle:calculadora')->group(function () {
    Route::get('/calculadora/job-status/{solicitud}', [CalculadoraController::class, 'jobStatus'])
         ->name('calculadora.job-status');
    Route::get('/calculadora/solar-ongrid/informe/{solicitud}', [CalculadoraController::class, 'descargarInforme'])
         ->name('calculadora.informe');
});

