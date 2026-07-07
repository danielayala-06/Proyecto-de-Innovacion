<?php

/**
 * @file    ColegiosApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para el módulo de Colegios (clientes reales del sistema).
 * Un "colegio" es la entidad que el usuario ve como cliente: tiene datos
 * institucionales, un historial de promociones escolares y productos comprados.
 * El campo `clientes` de la BD corresponde a la persona de contacto.
 *
 * Endpoints:
 *   GET /api/colegios         → lista con última promoción y contacto
 *   GET /api/colegios/{id}    → detalle completo (promociones + productos)
 *   PUT /api/colegios/{id}    → actualizar datos del colegio
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\ColegiosModel;
use CodeIgniter\HTTP\ResponseInterface;

class ColegiosApi extends BaseApiController
{
    protected ColegiosModel $colegiosModel;

    public function __construct()
    {
        $this->colegiosModel = model(ColegiosModel::class);
    }

    /**
     * GET /api/colegios
     * Lista todos los colegios con resumen de su última promoción y contacto.
     */
    public function index(): ResponseInterface
    {
        $filas = $this->colegiosModel->listarConResumen();

        $data = array_map(fn($r) => $this->_transformarResumen($r), $filas);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $data]);
    }

    /**
     * GET /api/colegios/{id}
     * Detalle completo: datos del colegio, historial de promociones y productos.
     */
    public function show($id): ResponseInterface
    {
        $colegio = $this->colegiosModel->detalle((int) $id);

        if (!$colegio) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Colegio no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->_transformarDetalle($colegio)]);
    }

    /**
     * PUT /api/colegios/{id}
     * Actualiza nombre, distrito o provincia del colegio.
     * Body: { nombre_colegio?, distrito?, provincia?, estado? }
     */
    public function update($id): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_colegio' => 'permit_empty|max_length[100]',
            'distrito'       => 'permit_empty|max_length[100]',
            'provincia'      => 'permit_empty|max_length[100]',
            'estado'         => 'permit_empty|in_list[ACTIVO,INACTIVO]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $colegio = $this->colegiosModel->find((int) $id);
        if (!$colegio) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Colegio no encontrado']);
        }

        $campos = array_filter(
            array_intersect_key($body, array_flip(['nombre_colegio', 'distrito', 'provincia', 'estado'])),
            fn($v) => $v !== null && $v !== ''
        );

        if (!empty($campos)) {
            $this->colegiosModel->update((int) $id, $campos);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Colegio actualizado']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Transformadores privados
    // ─────────────────────────────────────────────────────────────────────────

    private function _transformarResumen(array $r): array
    {
        $contacto = null;
        if (!empty($r['contacto_nombres'])) {
            $contacto = [
                'nombres'   => $r['contacto_nombres'],
                'apellidos' => $r['contacto_apellidos'] ?? null,
                'telefono'  => $r['contacto_telefono']  ?? null,
            ];
        }

        $ultima_promo = null;
        if (!empty($r['ultima_promo_anio'])) {
            $ultima_promo = [
                'nombre'         => $r['ultima_promo_nombre']      ?? null,
                'grado'          => $r['ultima_promo_grado']        ?? null,
                'seccion'        => $r['ultima_promo_seccion']      ?? null,
                'anio'           => (int) $r['ultima_promo_anio'],
                'num_estudiantes'=> (int) ($r['ultima_promo_estudiantes'] ?? 0),
            ];
        }

        return [
            'id_colegio'       => (int) $r['id_colegio'],
            'nombre_colegio'   => $r['nombre_colegio'],
            'distrito'         => $r['distrito']           ?? null,
            'provincia'        => $r['provincia']          ?? null,
            'estado'           => $r['estado'],
            'total_promociones'=> (int) ($r['total_promociones'] ?? 0),
            'contacto'         => $contacto,
            'ultima_promo'     => $ultima_promo,
        ];
    }

    private function _transformarDetalle(array $r): array
    {
        $promociones = array_map(fn($p) => [
            'id_promocion'    => (int) $p['id_promocion'],
            'nombre'          => $p['nombre'],
            'grado'           => $p['grado'],
            'seccion'         => $p['seccion']         ?? null,
            'anio'            => (int) $p['anio'],
            'num_estudiantes' => (int) ($p['num_estudiantes'] ?? 0),
            'is_active'       => (bool) $p['is_active'],
            'id_cotizacion'   => $p['id_cotizacion'] ? (int) $p['id_cotizacion'] : null,
            'cot_estado'      => $p['cot_estado']      ?? null,
            'total_estimado'  => $p['total_estimado'] !== null ? (float) $p['total_estimado'] : null,
            'id_contrato'     => $p['id_contrato']   ? (int) $p['id_contrato']  : null,
            'con_estado'      => $p['con_estado']      ?? null,
            'contacto'        => !empty($p['contacto_nombres']) ? [
                'nombres'   => $p['contacto_nombres'],
                'apellidos' => $p['contacto_apellidos'] ?? null,
                'telefono'  => $p['contacto_telefono']  ?? null,
            ] : null,
        ], $r['promociones'] ?? []);

        $productos = array_map(fn($p) => [
            'id_paquete'      => (int) $p['id_paquete'],
            'nombre_paquete'  => $p['nombre_paquete'],
            'categoria'       => $p['categoria']       ?? null,
            'cantidad_total'  => (int) ($p['cantidad_total']   ?? 1),
            'precio_unitario' => (float) ($p['precio_unitario'] ?? 0),
            'en_promociones'  => (int) ($p['en_promociones'] ?? 1),
            'ultimo_anio'     => (int) ($p['ultimo_anio']     ?? 0),
        ], $r['productos'] ?? []);

        return [
            'id_colegio'     => (int) $r['id_colegio'],
            'nombre_colegio' => $r['nombre_colegio'],
            'distrito'       => $r['distrito']  ?? null,
            'provincia'      => $r['provincia'] ?? null,
            'estado'         => $r['estado'],
            'promociones'    => $promociones,
            'productos'      => $productos,
        ];
    }
}
