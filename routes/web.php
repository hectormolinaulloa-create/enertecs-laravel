<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\CalculadoraController;

Route::get('/', [PaginaController::class, 'home'])->name('home');
Route::get('/nosotros', [PaginaController::class, 'nosotros'])->name('nosotros');
Route::get('/experiencia', [PaginaController::class, 'experiencia'])->name('experiencia');
Route::get('/servicios', [PaginaController::class, 'servicios'])->name('servicios');
Route::get('/servicios/{slug}', [PaginaController::class, 'servicio'])->name('servicio');
Route::get('/calculadora/solar-ongrid', [PaginaController::class, 'calculadora'])->name('calculadora');

// Endpoints de la calculadora
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/calculadora/job-status/{solicitud}', [CalculadoraController::class, 'jobStatus'])
         ->name('calculadora.job-status');
    Route::get('/calculadora/solar-ongrid/informe/{solicitud}', [CalculadoraController::class, 'descargarInforme'])
         ->name('calculadora.informe');
});

// Ruta temporal de migraciones — eliminar tras primer deploy exitoso
Route::get('/run-migrations', function (\Illuminate\Http\Request $request) {
    abort_unless($request->get('token') === config('app.migration_token'), 403);
    \Artisan::call('migrate', ['--force' => true]);
    return \Artisan::output();
})->middleware('throttle:1,60');
