<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Verifica que el sistema de autenticación funciona correctamente:
 * - Las rutas protegidas bloquean acceso sin sesión
 * - La API devuelve 401 con el formato correcto
 * - El login está disponible
 */
final class AuthTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    // ── Rutas web ──────────────────────────────────────────────────────────────

    public function testLoginPageLoads(): void
    {
        $result = $this->get('/login');

        $result->assertStatus(200);
    }

    public function testProtectedWebRouteRedirectsToLoginWithoutSession(): void
    {
        $result = $this->get('/cotizaciones');

        $result->assertRedirectTo(base_url('/login'));
    }

    public function testProtectedWebRouteContratoRedirectsWithoutSession(): void
    {
        $result = $this->get('/contratos');

        $result->assertRedirectTo(base_url('/login'));
    }

    // ── Rutas API ──────────────────────────────────────────────────────────────

    public function testApiCotizacionesReturns401WithoutSession(): void
    {
        $result = $this->get('/api/cotizaciones');

        $result->assertStatus(401);
    }

    public function testApiPaquetesReturns401WithoutSession(): void
    {
        $result = $this->get('/api/paquetes');

        $result->assertStatus(401);
    }

    public function testApiColegiosReturns401WithoutSession(): void
    {
        $result = $this->get('/api/colegios');

        $result->assertStatus(401);
    }

    public function testApiContratosReturns401WithoutSession(): void
    {
        $result = $this->get('/api/contratos');

        $result->assertStatus(401);
    }

    // ── Formato del 401 ────────────────────────────────────────────────────────

    public function test401ResponseHasCorrectJsonFormat(): void
    {
        $result = $this->get('/api/cotizaciones');

        $result->assertStatus(401);
        $result->assertJSONFragment(['status' => 'error', 'message' => 'Unauthorized']);
    }
}
