<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Proyecto;

class ProyectoSeeder extends Seeder
{
    public function run(): void
    {
        $proyectos = [
            ['nombre' => 'Proyecto SSM',         'cliente' => 'SSM',          'categoria' => 'Industrial',          'lat' => -53.15, 'lng' => -70.91, 'año' => 2023, 'activo' => true],
            ['nombre' => 'Proyecto Entrevientos', 'cliente' => 'Entrevientos', 'categoria' => 'Energía',             'lat' => -52.80, 'lng' => -69.50, 'año' => 2024, 'activo' => true],
            ['nombre' => 'Proyecto Salfa',        'cliente' => 'Salfa',        'categoria' => 'Comercial',           'lat' => -53.10, 'lng' => -70.85, 'año' => 2023, 'activo' => true],
            ['nombre' => 'Proyecto KSAT',         'cliente' => 'KSAT',         'categoria' => 'Telecomunicaciones',  'lat' => -53.16, 'lng' => -70.90, 'año' => 2024, 'activo' => true],
            ['nombre' => 'Proyecto UMAG',         'cliente' => 'UMAG',         'categoria' => 'Educacional',         'lat' => -53.15, 'lng' => -70.92, 'año' => 2022, 'activo' => true],
        ];
        foreach ($proyectos as $p) {
            Proyecto::firstOrCreate(['nombre' => $p['nombre']], $p);
        }
    }
}
