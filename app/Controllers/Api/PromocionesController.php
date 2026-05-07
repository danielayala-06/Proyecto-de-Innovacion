<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PromocionesController
 * Base URL: /api/promociones
 *
 * GET    /api/promociones                  → listar con filtros
 * GET    /api/promociones/{id}             → detalle + estudiantes + sesiones
 * POST   /api/promociones                  → crear
 * PUT    /api/promociones/{id}             → actualizar
 * PATCH  /api/promociones/{id}/activar     → activar / desactivar
 */
class PromocionesController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/promociones[?colegio=1&anio=2026&activa=1]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $builder = $this->db->table('promociones_escolares pe')
            ->select('pe.*, col.nombre_colegio, col.distrito,
                      ct.total_estimado, ct.estado AS estado_cotizacion')
            ->join('colegios col',     'col.id_colegio = pe.id_colegio')
            ->join('cotizaciones ct',  'ct.id_cotizacion = pe.id_cotizacion')
            ->orderBy('pe.anio', 'DESC')
            ->orderBy('col.nombre_colegio', 'ASC');

        if ($idColegio = $this->request->getGet('colegio')) {
            $builder->where('pe.id_colegio', $idColegio);
        }
        if ($anio = $this->request->getGet('anio')) {
            $builder->where('pe.anio', $anio);
        }
        if ($this->request->getGet('activa') !== null) {
            $builder->where('pe.is_active', (bool)$this->request->getGet('activa'));
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $builder->get()->getResultArray()]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/promociones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $promocion = $this->db->table('promociones_escolares pe')
            ->select('pe.*, col.nombre_colegio, col.distrito, col.provincia,
                      ct.total_estimado, ct.estado AS estado_cotizacion,
                      CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente,
                      p.telefono')
            ->join('colegios col',    'col.id_colegio = pe.id_colegio')
            ->join('cotizaciones ct', 'ct.id_cotizacion = pe.id_cotizacion')
            ->join('clientes c',      'c.id_cliente = ct.id_cliente')
            ->join('personas p',      'p.id_persona = c.id_persona')
            ->where('pe.id_promocion', $id)
            ->get()->getRowArray();

        if (!$promocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Promoción no encontrada']);
        }

        // Estudiantes de la promoción
        $estudiantes = $this->db->table('estudiantes e')
            ->select('e.id_estudiante, e.nombres, e.apellidos, e.fecha_nacimiento,
                      e.color_fav, e.profesion_futura,
                      a.tipo_relacion,
                      CONCAT(pa.nombres, " ", COALESCE(pa.apellidos,"")) AS apoderado,
                      pa.telefono AS tel_apoderado')
            ->join('apoderados a',   'a.id_apoderado = e.id_apoderado')
            ->join('personas pa',    'pa.id_persona = a.id_persona')
            ->where('e.id_promocion', $id)
            ->orderBy('e.apellidos', 'ASC')
            ->get()->getResultArray();

        // Sesiones fotográficas
        $sesiones = $this->db->table('sesiones_fotograficas')
            ->where('id_promocion', $id)
            ->orderBy('fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        $promocion['estudiantes']         = $estudiantes;
        $promocion['sesiones_fotograficas'] = $sesiones;

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $promocion]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/promociones
    // Body: { id_colegio, id_cotizacion, nombre, grado, seccion, num_estudiantes, anio }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $rules = [
            'id_colegio'      => 'required|integer',
            'id_cotizacion'   => 'required|integer',
            'nombre'          => 'required|max_length[100]',
            'grado'           => 'required|max_length[10]',
            'num_estudiantes' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $body = $this->request->getJSON(true);

        // Verificar que la cotización esté APROBADA
        $cotizacion = $this->db->table('cotizaciones')
            ->where('id_cotizacion', $body['id_cotizacion'])
            ->get()->getRowArray();

        if (!$cotizacion || $cotizacion['estado'] !== 'APROBADA') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'La cotización debe estar APROBADA para crear una promoción']);
        }

        $this->db->table('promociones_escolares')->insert([
            'id_colegio'      => $body['id_colegio'],
            'id_cotizacion'   => $body['id_cotizacion'],
            'nombre'          => $body['nombre'],
            'grado'           => $body['grado'],
            'seccion'         => $body['seccion'] ?? null,
            'num_estudiantes' => $body['num_estudiantes'],
            'anio'            => $body['anio'] ?? date('Y'),
            'is_active'       => true,
        ]);

        $idPromocion = $this->db->insertID();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Promoción creada', 'id_promocion' => $idPromocion]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/promociones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $promocion = $this->db->table('promociones_escolares')
            ->where('id_promocion', $id)->get()->getRowArray();

        if (!$promocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Promoción no encontrada']);
        }

        $body = $this->request->getJSON(true);

        $updateData = array_filter([
            'nombre'          => $body['nombre'] ?? null,
            'grado'           => $body['grado'] ?? null,
            'seccion'         => $body['seccion'] ?? null,
            'num_estudiantes' => $body['num_estudiantes'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($updateData)) {
            $this->db->table('promociones_escolares')
                ->where('id_promocion', $id)
                ->update($updateData);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Promoción actualizada']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/promociones/{id}/activar
    // Body: { is_active: true | false }
    // ────────────────────────────────────────────────────────────────────────
    public function toggleActiva($id)
    {
        $promocion = $this->db->table('promociones_escolares')
            ->where('id_promocion', $id)->get()->getRowArray();

        if (!$promocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Promoción no encontrada']);
        }

        $body     = $this->request->getJSON(true);
        $isActive = $body['is_active'] ?? !$promocion['is_active'];

        $this->db->table('promociones_escolares')
            ->where('id_promocion', $id)
            ->update(['is_active' => (bool)$isActive]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Promoción ' . ($isActive ? 'activada' : 'desactivada'),
                'is_active' => (bool)$isActive,
            ]);
    }
}
