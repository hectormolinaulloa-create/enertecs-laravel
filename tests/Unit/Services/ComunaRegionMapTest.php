<?php
namespace Tests\Unit\Services;

use App\Services\ComunaRegionMap;
use Tests\TestCase;

class ComunaRegionMapTest extends TestCase
{
    public function test_lookup_por_comuna_exacta(): void
    {
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('Santiago'));
        $this->assertSame('Valparaíso', ComunaRegionMap::lookup('Viña del Mar'));
        $this->assertSame('Biobío', ComunaRegionMap::lookup('Concepción'));
    }

    public function test_lookup_normaliza_tildes(): void
    {
        $this->assertSame('La Araucanía', ComunaRegionMap::lookup('Temuco'));
        $this->assertSame("O'Higgins", ComunaRegionMap::lookup('Rancagua'));
    }

    public function test_lookup_normaliza_mayusculas(): void
    {
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('SANTIAGO'));
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('providencia'));
    }

    public function test_lookup_retorna_null_para_comuna_desconocida(): void
    {
        $this->assertNull(ComunaRegionMap::lookup('CiudadInventada'));
    }

    public function test_lookup_string_vacio_retorna_null(): void
    {
        $this->assertNull(ComunaRegionMap::lookup(''));
    }
}
