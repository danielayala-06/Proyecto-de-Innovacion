<?php

namespace App\Services\Promociones;

use App\Models\PromocionesEscolaresModel;
use App\Models\CotizacionesModel;
use App\Models\EstudiantesModel;
use App\Models\SesionesFotograficasModel;

class PromocionService
{
    protected PromocionesEscolaresModel $promocionModel;
    protected CotizacionesModel         $cotizacionModel;
    protected EstudiantesModel          $estudianteModel;
    protected SesionesFotograficasModel $sesionModel;

    public function __construct()
    {
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->cotizacionModel = new CotizacionesModel();
        $this->estudianteModel = new EstudiantesModel();
        $this->sesionModel     = new SesionesFotograficasModel();
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

        if (!empty($filters['id_cotizacion'])) {
            $q->where('promociones_escolares.id_cotizacion', (int) $filters['id_cotizacion']);
        }
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
        $promocion = $this->promocionModel->obtenerConRelaciones($id);

        if (!$promocion) return null;

        $promocion['estudiantes']          = $this->estudianteModel->listarConApoderado($id);
        $promocion['sesiones_fotograficas'] = $this->sesionModel->listarPorPromocion($id);

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
