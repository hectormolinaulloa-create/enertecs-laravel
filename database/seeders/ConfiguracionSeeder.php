<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['clave' => 'email_contacto',   'valor' => 'contacto@enertecs.cl'],
            ['clave' => 'telefono',         'valor' => '+56 61 XXX XXXX'],
            ['clave' => 'vrm_site_id',      'valor' => ''],
            ['clave' => 'nombre_empresa',   'valor' => 'Enertecs SpA'],
        ];
        foreach ($configs as $c) {
            Configuracion::firstOrCreate(['clave' => $c['clave']], $c);
        }
    }
}
