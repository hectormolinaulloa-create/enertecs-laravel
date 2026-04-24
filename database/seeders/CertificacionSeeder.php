<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Certificacion;

class CertificacionSeeder extends Seeder
{
    public function run(): void
    {
        $certs = [
            ['nombre' => 'Schneider Electric', 'tipo' => 'marca'],
            ['nombre' => 'DAHUA',              'tipo' => 'marca'],
            ['nombre' => 'BASH',               'tipo' => 'marca'],
            ['nombre' => 'SCHARFSTEIN',        'tipo' => 'marca'],
            ['nombre' => 'SELECOM',            'tipo' => 'marca'],
        ];
        foreach ($certs as $c) {
            Certificacion::firstOrCreate(['nombre' => $c['nombre']], $c);
        }
    }
}
