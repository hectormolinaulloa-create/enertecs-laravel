<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServicioSeeder::class,
            ProyectoSeeder::class,
            CertificacionSeeder::class,
            ConfiguracionSeeder::class,
        ]);
    }
}
