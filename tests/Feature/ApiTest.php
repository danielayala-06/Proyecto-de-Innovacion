<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Verifica que los endpoints de API devuelven la estructura correcta
 * cuando el usuario está autenticado.
 *
 * Todos los tests usan una sesión simulada — no requieren login real.
 * Las consultas son de solo lectura (GET) o verifican validación (422).
 */
final class ApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected array $authSession = [
        'logged_in'   => true,
        'usuario_id'  => 1,
        'nombre_user' => 'carlos.admin',
        'nombres'     => 'Carlos',
        'apellidos'   => 'Admin',
        'id_rol'      => 1,
        'rol'         => 'Administrador',
    ];

    // ── Estructura de respuesta ────────────────────────────────────────────────

    public function testPaquetesApiReturnsSuccessStructure(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/paquetes');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $this->assertIsArray(json_decode($result->getJSON(), true)['data'] ?? null);
    }

    public function testColegiosApiReturnsSuccessStructure(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/colegios');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $this->assertIsArray(json_decode($result->getJSON(), true)['data'] ?? null);
    }

    public function testCotizacionesApiReturnsSuccessStructure(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/cotizaciones');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $this->assertIsArray(json_decode($result->getJSON(), true)['data'] ?? null);
    }

    public function testContratosApiReturnsSuccessStructure(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/contratos');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $this->assertIsArray(json_decode($result->getJSON(), true)['data'] ?? null);
    }

    // ── Validación de input ────────────────────────────────────────────────────

    public function testCreatePaqueteWithEmptyBodyReturns422(): void
    {
        $result = $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('/api/paquetes', []);

        $result->assertStatus(422);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testCreateCotizacionWithEmptyBodyReturns422(): void
    {
        $result = $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('/api/cotizaciones', []);

        $result->assertStatus(422);
        $result->assertJSONFragment(['status' => 'error']);
    }

    // ── Recursos no encontrados ────────────────────────────────────────────────

    public function testColegioInexistenteReturns404(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/colegios/999999');

        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testPaqueteInexistenteReturns404(): void
    {
        $result = $this->withSession($this->authSession)->get('/api/paquetes/999999');

        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }
}
