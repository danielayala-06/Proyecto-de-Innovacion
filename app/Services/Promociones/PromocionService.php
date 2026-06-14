<?php

/**
 * @file    PromocionService.php
 * @package App\Services\Promociones
 *
 * Capa de negocio para la gestión de promociones escolares.
 * Una promoción representa el grupo de estudiantes de un grado/sección
 * que contrató el servicio fotográfico mediante una cotización aprobada.
 */

namespace App\Services\Promociones;

use App\Models\PromocionesEscolaresModel;
use App\Models\CotizacionesModel;
use App\Models\EstudiantesModel;
use App\Models\SesionesFotograficasModel;

/**
 * Servicio de Promociones Escolares.
 *
 * Responsabilidades:
 * - Listar promociones con filtros por cotización, colegio, año y estado.
 * - Obtener el detalle completo de una promoción (colegio, estudiantes, sesiones).
 * - Crear una promoción validando que la cotización esté APROBADA.
 * - Actualizar datos editables de la promoción.
 * - Activar o desactivar una promoción (toggle).
 */
class PromocionService
{
    /** @var PromocionesEscolaresModel Acceso a la tabla `promociones_escolares`. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var CotizacionesModel Acceso a la tabla `cotizaciones` (validación de estado). */
    protected CotizacionesModel $cotizacionModel;

    /** @var EstudiantesModel Acceso a la tabla `estudiantes`. */
    protected EstudiantesModel $estudianteModel;

    /** @var SesionesFotograficasModel Acceso a la tabla `sesiones_fotograficas`. */
    protected SesionesFotograficasModel $sesionModel;

    public function __construct()
    {
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->cotizacionModel = new CotizacionesModel();
        $this->estudianteModel = new EstudiantesModel();
        $this->sesionModel     = new SesionesFotograficasModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista promociones con datos del colegio y estado de la cotización.
     *
     * Filtros admitidos:
     * - id_cotizacion: filtra por cotización específica.
     * - colegio:       filtra por id_colegio.
     * - anio:          filtra por año académico.
     * - activa:        filtra por estado activo/inactivo (booleano).
     *
     * @param  array<string, mixed>     $filters
     * @return array<int, array<string, mixed>>
     */
    public function listar(array $filters = []): array
    {
        $q = $this->promocionModel
            ->select('promociones_escolares.*, colegios.nombre_colegio, colegios.distrito,
                      cotizaciones.total_estimado, cotizaciones.estado AS estado_cotizacion,
                      MAX(contratos.id_contrato) AS id_contrato')
            ->join('colegios',     'colegios.id_colegio = promociones_escolares.id_colegio')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = promociones_escolares.id_cotizacion')
            ->join('contratos',    'contratos.id_cotizacion = promociones_escolares.id_cotizacion', 'left')
            ->groupBy('promociones_escolares.id_promocion')
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

    /**
     * Retorna el detalle completo de una promoción:
     * datos del colegio, cotización, cliente, lista de estudiantes y sesiones.
     *
     * @param  int                       $id ID de la promoción.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerPorId(int $id): ?array
    {
        $promocion = $this->promocionModel->obtenerConRelaciones($id);

        if (!$promocion) {
            return null;
        }

        $promocion['estudiantes']           = $this->estudianteModel->listarConApoderado($id);
        $promocion['sesiones_fotograficas'] = $this->sesionModel->listarPorPromocion($id);

        return $promocion;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea una promoción vinculada a una cotización APROBADA.
     *
     * @param  array<string, mixed> $data Campos requeridos:
     *                                    id_colegio, id_cotizacion, nombre, grado,
     *                                    num_estudiantes, seccion?, anio?
     * @return int                        ID de la promoción creada.
     *
     * @throws \RuntimeException 404 si la cotización no existe.
     * @throws \RuntimeException 409 si la cotización no está APROBADA.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function crear(array $data): int
    {
        $cotizacion = $this->cotizacionModel->find((int) $data['id_cotizacion']);

        if (!$cotizacion) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            throw new \RuntimeException(
                'La cotización debe estar APROBADA para crear una promoción',
                409
            );
        }

        $idPromocion = $this->promocionModel->insert([
            'id_colegio'      => $data['id_colegio'],
            'id_cotizacion'   => $data['id_cotizacion'],
            'nombre'          => $data['nombre'],
            'grado'           => $data['grado'],
            'seccion'         => $data['seccion']  ?? null,
            'num_estudiantes' => $data['num_estudiantes'],
            'anio'            => $data['anio']     ?? (int) date('Y'),
            'is_active'       => true,
        ]);

        if ($idPromocion === false) {
            throw new \RuntimeException(json_encode($this->promocionModel->errors()), 422);
        }

        return $idPromocion;
    }

    /**
     * Actualiza campos editables de una promoción (nombre, grado, sección, num_estudiantes).
     *
     * @param  int                  $id   ID de la promoción.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function actualizar(int $id, array $data): void
    {
        if (!$this->promocionModel->find($id)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $updateData = array_filter([
            'nombre'          => $data['nombre']          ?? null,
            'grado'           => $data['grado']           ?? null,
            'seccion'         => $data['seccion']         ?? null,
            'num_estudiantes' => $data['num_estudiantes'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($updateData) && $this->promocionModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->promocionModel->errors()), 422);
        }
    }

    /**
     * Activa o desactiva una promoción.
     *
     * Si no se pasa $isActive, invierte el estado actual (toggle).
     *
     * @param  int       $id       ID de la promoción.
     * @param  bool|null $isActive true = activar, false = desactivar, null = toggle.
     * @return bool                Nuevo estado de is_active.
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     */
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
