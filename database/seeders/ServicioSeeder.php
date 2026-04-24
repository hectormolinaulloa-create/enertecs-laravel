<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Instalaciones Eléctricas',  'slug' => 'instalaciones-electricas',  'descripcion' => 'Proyectos de instalación eléctrica industrial y comercial.',    'icono' => 'zap',      'orden' => 1],
            ['nombre' => 'Media Tensión',              'slug' => 'media-tension',              'descripcion' => 'Diseño y construcción de subestaciones de media tensión.',       'icono' => 'activity', 'orden' => 2],
            ['nombre' => 'HVAC',                       'slug' => 'hvac',                       'descripcion' => 'Sistemas de climatización y ventilación industrial.',             'icono' => 'wind',     'orden' => 3],
            ['nombre' => 'Redes y Networking',         'slug' => 'redes-networking',           'descripcion' => 'Infraestructura de redes de datos y telecomunicaciones.',        'icono' => 'network',  'orden' => 4],
            ['nombre' => 'Audio y Video',              'slug' => 'audio-video',                'descripcion' => 'Sistemas de audio, video y señalización digital.',               'icono' => 'monitor',  'orden' => 5],
            ['nombre' => 'Sistemas Contra Incendio',   'slug' => 'sistemas-contra-incendio',   'descripcion' => 'Detección y supresión de incendios según norma NFPA.',           'icono' => 'flame',    'orden' => 6],
            ['nombre' => 'Industrial',                 'slug' => 'industrial',                 'descripcion' => 'Automatización y control de procesos industriales.',             'icono' => 'settings', 'orden' => 7],
            ['nombre' => 'Distribución Eléctrica',     'slug' => 'distribucion-electrica',     'descripcion' => 'Tableros y sistemas de distribución eléctrica.',                 'icono' => 'git-merge','orden' => 8],
        ];
        foreach ($servicios as $s) {
            Servicio::firstOrCreate(['slug' => $s['slug']], array_merge($s, ['activo' => true]));
        }
    }
}
