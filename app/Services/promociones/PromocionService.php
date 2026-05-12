<?php

namespace App\Services\Promociones;

use App\Models\PromocionesEscolaresModel;
use App\Models\CotizacionesModel;

class PromocionService
{
    protected PromocionesEscolaresModel $promocionModel;
    protected CotizacionesModel         $cotizacionModel;

    public function __construct()
    {
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->cotizacionModel = new CotizacionesModel();
    }

    public function listar(array $filters = []): array
    {
        $q = $this->promocionModel
            ->select('promociones_escolares.*, colegios.nombre_colegio, colegios.distrito,
                      cotizaciones.total_estimado, cotizaciones.estado AS estado_cotizacion')
            ->join('colegios',    'colegios.id_colegio = promociones_escolares.id_colegio')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = promociones_escolares.id_cotizacion')
            ->orderBy('promociones_escolares.anio', 'DESC')
            ->orderBy('colegios.nombre_colegio', 'ASC');

        if (!empty($filters['colegio'])) {
            $q->where('promociones_escolares.id_colegio', $filters['colegio']);
        }
        if (!empty($filters['anio'])) {
            $q->where('promociones_escolares.anio', $filters['anio']);
        }
        if (isset($filters['activa'])) {
            $q->where('promociones_escolares.is_active', (bool) $filters['activa']);
        }

        return $q->findAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $promocion = $this->promocionModel
            ->select('promociones_escolares.*, colegios.nombre_colegio, colegios.distrito, colegios.provincia,
                      cotizaciones.total_estimado, cotizaciones.estado AS estado_cotizacion,
                      CONCAT(personas.nombres, " ", COALESCE(personas.apellidos,"")) AS cliente,
                      personas.telefono')
            ->join('colegios',    'colegios.id_colegio = promociones_escolares.id_colegio')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = promociones_escolares.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->find($id);

        if (!$promocion) return null;

        $db = $this->promocionModel->db;

        $promocion['estudiantes'] = $db->table('estudiantes e')
            ->select('e.id_estudiante, e.nombres, e.apellidos, e.fecha_nacimiento,
                      e.color_fav, e.profesion_futura,
                      a.tipo_relacion,
                      CONCAT(pa.nombres, " ", COALESCE(pa.apellidos,"")) AS apoderado,
                      pa.telefono AS tel_apoderado')
            ->join('apoderados a', 'a.id_apoderado = e.id_apoderado')
            ->join('personas pa',  'pa.id_persona = a.id_persona')
            ->where('e.id_promocion', $id)
            ->orderBy('e.apellidos', 'ASC')
            ->get()->getResultArray();

        $promocion['sesiones_fotograficas'] = $db->table('sesiones_fotograficas')
            ->where('id_promocion', $id)
            ->orderBy('fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        return $promocion;
    }

    public function crear(array $data): int
    {
        $cotizacion = $this->cotizacionModel->find((int) $data['id_cotizacion']);

        if (!$cotizacion) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            throw new \RuntimeException(
                'La cotización debe estar APROBADA para crear una promoción', 409
            );
        }

        $idPromocion = $this->promocionModel->insert([
            'id_colegio'      => $data['id_colegio'],
            'id_cotizacion'   => $data['id_cotizacion'],
            'nombre'          => $data['nombre'],
            'grado'           => $data['grado'],
            'seccion'         => $data['seccion'] ?? null,
            'num_estudiantes' => $data['num_estudiantes'],
            'anio'            => $data['anio'] ?? (int) date('Y'),
            'is_active'       => true,
        ]);

        if ($idPromocion === false) {
            throw new \RuntimeException(json_encode($this->promocionModel->errors()), 422);
        }

        return $idPromocion;
    }

    public function actualizar(int $id, array $data): void
    {
        if (!$this->promocionModel->find($id)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $updateData = array_filter([
            'nombre'          => $data['nombre'] ?? null,
            'grado'           => $data['grado'] ?? null,
            'seccion'         => $data['seccion'] ?? null,
            'num_estudiantes' => $data['num_estudiantes'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($updateData) && $this->promocionModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->promocionModel->errors()), 422);
        }
    }

    public function toggleActiva(int $id, ?bool $isActive = null): bool
    {
        $promocion = $this->promocionModel->find($id);

        if (!$promocion) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $nuevo = $isActive ?? !$promocion['is_active'];

        $this->promocionModel->update($id, ['is_active' => (bool) $nuevo]);

        return (bool) $nuevo;
    }
}
