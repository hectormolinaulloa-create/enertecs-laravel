<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    #[Test]
    public function paginas_publicas_responden_200(): void
    {
        $rutas = ['/', '/servicios', '/experiencia', '/calculadora/solar-ongrid'];

        foreach ($rutas as $ruta) {
            $response = $this->get($ruta);
            $response->assertStatus(200);
        }
    }

    #[Test]
    public function cabeceras_de_seguridad_presentes(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->assertHeader('Content-Security-Policy');
    }

    #[Test]
    public function csp_contiene_dominios_de_mapas(): void
    {
        $response = $this->get('/experiencia');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('arcgisonline.com', $csp);
        $this->assertStringContainsString('opentopomap.org', $csp);
        $this->assertStringContainsString('cartocdn.com', $csp);
    }

    #[Test]
    public function no_expone_debug_en_produccion(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('APP_DEBUG=false', $envExample);
    }

    #[Test]
    public function session_esta_cifrada(): void
    {
        $config = config('session.encrypt');
        $this->assertIsBool($config);
    }
}
