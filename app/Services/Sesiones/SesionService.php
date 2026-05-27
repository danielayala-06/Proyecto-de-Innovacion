<?php

/**
 * @file    SesionService.php
 * @package App\Services\Sesiones
 *
 * Capa de negocio para la gestión de sesiones fotográficas.
 * Controla el cálculo de límites por tipo, la asistencia de estudiantes
 * y las transiciones de estado de cada sesión.
 */

namespace App\Services\Sesiones;

use App\Models\SesionesFotograficasModel;
use App\Models\SesionAsistenciaModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\EstudiantesModel;
use App\Models\ReglasPaquetesModel;
use App\Models\CotizacionesDetallesModel;
use App\Models\PaquetesSesionesModel;

/**
 * Servicio de Sesiones Fotográficas.
 *
 * Responsabilidades:
 * - Listar sesiones con filtros por promoción, contrato, estado o tipo.
 * - Obtener el detalle de una sesión con su lista de asistencia.
 * - Calcular el límite de sesiones permitidas según los paquetes contratados
 *   y las reglas de negocio vigentes.
 * - Crear sesiones respetando el límite calculado.
 * - Actualizar datos y estado de sesiones.
 * - Gestionar la asistencia: agregar, quitar y marcar presencia de estudiantes.
 */
class SesionService
{
    /** @var SesionesFotograficasModel Acceso a la tabla `sesiones_fotograficas`. */
    protected SesionesFotograficasModel $sesionModel;

    /** @var SesionAsistenciaModel Acceso a la tabla `sesion_asistencia`. */
    protected SesionAsistenciaModel $asistenciaModel;

    /** @var PromocionesEscolaresModel Acceso a la tabla `promociones_escolares`. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var EstudiantesModel Acceso a la tabla `estudiantes`. */
    protected EstudiantesModel $estudianteModel;

    /** @var ReglasPaquetesModel Acceso a las reglas de bonificación de sesiones. */
    protected ReglasPaquetesModel $reglasPaquetesModel;

    /** @var CotizacionesDetallesModel Acceso a los ítems de la cotización. */
    protected CotizacionesDetallesModel $detalleModel;

    /** @var PaquetesSesionesModel Acceso a la configuración de sesiones por paquete. */
    protected PaquetesSesionesModel $paquetesSesionesModel;

    public function __construct()
    {
        $this->sesionModel           = new SesionesFotograficasModel();
        $this->asistenciaModel       = new SesionAsistenciaModel();
        $this->promocionModel        = new PromocionesEscolaresModel();
        $this->estudianteModel       = new EstudiantesModel();
        $this->reglasPaquetesModel   = new ReglasPaquetesModel();
        $this->detalleModel          = new CotizacionesDetallesModel();
        $this->paquetesSesionesModel = new PaquetesSesionesModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista sesiones con datos de promoción, colegio y contrato vinculado.
     *
     * Filtros admitidos:
     * - id_promocion: filtra por promoción específica.
     * - id_contrato:  filtra por contrato (join inverso via cotización).
     * - estado:       pendiente | finalizado | cancelado.
     * - tipo:         tipo de sesión (individual, grupal, etc.).
     *
     * @param  array<string, mixed>     $filters
     * @return array<int, array<string, mixed>>
     */
    public function listar(array $filters = []): array
    {
        $builder = $this->sesionModel
            ->select('sesiones_fotograficas.*,
                      promociones_escolares.nombre AS nombre_promocion,
                      promociones_escolares.grado,
                      colegios.nombre_colegio,
                      contratos.id_contrato')
            ->join('promociones_escolares',
                   'promociones_escolares.id_promocion = sesiones_fotograficas.id_promocion')
            ->join('colegios',
                   'colegios.id_colegio = promociones_escolares.id_colegio', 'left')
            ->join('cotizaciones',
                   'cotizaciones.id_cotizacion = promociones_escolares.id_cotizacion', 'left')
            ->join('contratos',
                   "contratos.id_cotizacion = cotizaciones.id_cotizacion AND contratos.estado != 'CANCELADO'",
                   'left');

        if (!empty($filters['id_promocion'])) {
            $builder->where('sesiones_fotograficas.id_promocion', (int) $filters['id_promocion']);
        }
        if (!empty($filters['id_contrato'])) {
            $builder->where('contratos.id_contrato', (int) $filters['id_contrato']);
        }
        if (!empty($filters['estado'])) {
            $builder->where('sesiones_fotograficas.estado', $filters['estado']);
        }
        if (!empty($filters['tipo'])) {
            $builder->where('sesiones_fotograficas.tipo', $filters['tipo']);
        }

        return $builder->orderBy('sesiones_fotograficas.fecha_hora_sesion', 'DESC')->findAll();
    }

    /**
     * Retorna el detalle de una sesión con su lista de asistencia.
     *
     * @param  int                       $id ID de la sesión.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerPorId(int $id): ?array
    {
        $sesion = $this->sesionModel
            ->select('sesiones_fotograficas.*, promociones_escolares.nombre AS nombre_promocion')
            ->join('promociones_escolares',
                   'promociones_escolares.id_promocion = sesiones_fotograficas.id_promocion')
            ->where('sesiones_fotograficas.id_sesion', $id)
            ->first();

        if (!$sesion) {
            return null;
        }

        $sesion['asistencia'] = $this->asistenciaModel->listarConEstudiante($id);

        return $sesion;
    }

    /**
     * Calcula el límite de sesiones de un tipo dado para una promoción.
     *
     * El límite se determina por:
     * 1. La configuración base del paquete (PaquetesSesiones).
     * 2. Reglas de bonificación por cantidad (ReglasPaquetes, tipo='sesion_unica').
     *
     * @param  int    $idPromocion ID de la promoción.
     * @param  string $tipo        Tipo de sesión a evaluar.
     * @return array{tipo: string, permitidas: int, usadas: int, puede_crear: bool}
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     */
    public function calcularLimite(int $idPromocion, string $tipo): array
    {
        $promocion = $this->promocionModel->find($idPromocion);
        if (!$promocion) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $detalles   = $this->detalleModel->listarPaquetes((int) $promocion['id_cotizacion']);
        $permitidas = $this->_calcularPermitidas($detalles, $tipo);
        $usadas     = $this->_contarUsadas($idPromocion, $tipo);

        return [
            'tipo'        => $tipo,
            'permitidas'  => $permitidas,
            'usadas'      => $usadas,
            'puede_crear' => $usadas < $permitidas,
        ];
    }

    /**
     * Determina el total de sesiones permitidas del tipo dado a partir de los paquetes
     * de la cotización y las reglas de bonificación aplicables.
     *
     * @param  array<int, array<string, mixed>> $detalles Ítems de la cotización (tipo PAQUETE).
     * @param  string                           $tipo     Tipo de sesión.
     * @return int                                        Número de sesiones permitidas.
     */
    private function _calcularPermitidas(array $detalles, string $tipo): int
    {
        $idsPaquetes = array_column($detalles, 'id_referencia');
        if (empty($idsPaquetes)) {
            return 0;
        }

        $evaluacion = $this->reglasPaquetesModel->evaluarDetalles($detalles);

        // Si hay reducción por grupo pequeño, solo se permite la sesión de colegio.
        if (!empty($evaluacion['reducciones']) && $tipo !== 'colegio') {
            return 0;
        }

        $configs    = $this->paquetesSesionesModel->configuracionesPorTipo($idsPaquetes, $tipo);
        $permitidas = 0;
        foreach ($configs as $cfg) {
            $permitidas += (int) $cfg['num_sesiones'];
        }

        $bonus = 0;
        foreach ($evaluacion['activadas'] as $r) {
            if ($r['tipo_beneficio'] === 'sesion_unica') {
                $bonus++;
            }
        }

        return $permitidas + $bonus;
    }

    /**
     * Cuenta las sesiones no canceladas de un tipo dado en una promoción.
     *
     * @param  int    $idPromocion ID de la promoción.
     * @param  string $tipo        Tipo de sesión.
     * @return int                 Número de sesiones usadas.
     */
    private function _contarUsadas(int $idPromocion, string $tipo): int
    {
        return (int) $this->sesionModel
            ->where('id_promocion', $idPromocion)
            ->where('tipo', $tipo)
            ->where('estado !=', 'cancelado')
            ->countAllResults();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea una sesión validando que no se supere el límite del tipo.
     *
     * @param  array<string, mixed> $data Campos requeridos:
     *                                    id_promocion, fecha_hora_sesion, tipo, observaciones?
     * @return int                        ID de la sesión creada.
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     * @throws \RuntimeException 409 si se superó el límite de sesiones del tipo.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function crear(array $data): int
    {
        $idPromocion = (int) ($data['id_promocion'] ?? 0);
        $tipo        = $data['tipo'] ?? '';

        if (!$this->promocionModel->find($idPromocion)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $limite = $this->calcularLimite($idPromocion, $tipo);
        if (!$limite['puede_crear']) {
            throw new \RuntimeException(
                "Límite de sesiones tipo «{$tipo}» alcanzado ({$limite['usadas']}/{$limite['permitidas']}).",
                409
            );
        }

        $id = $this->sesionModel->insert([
            'id_promocion'      => $idPromocion,
            'fecha_hora_sesion' => $data['fecha_hora_sesion'],
            'tipo'              => $tipo,
            'observaciones'     => $data['observaciones'] ?? null,
            'estado'            => 'pendiente',
        ]);

        if ($id === false) {
            throw new \RuntimeException(json_encode($this->sesionModel->errors()), 422);
        }

        return $id;
    }

    /**
     * Actualiza la fecha u observaciones de una sesión no finalizada.
     *
     * @param  int                  $id   ID de la sesión.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si la sesión no existe.
     * @throws \RuntimeException 409 si la sesión ya está finalizada.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function actualizar(int $id, array $data): void
    {
        $sesion = $this->sesionModel->find($id);
        if (!$sesion) {
            throw new \RuntimeException('Sesión no encontrada', 404);
        }

        if ($sesion['estado'] === 'finalizado') {
            throw new \RuntimeException('No se puede editar una sesión finalizada', 409);
        }

        $update = array_filter([
            'fecha_hora_sesion' => $data['fecha_hora_sesion'] ?? null,
            'observaciones'     => $data['observaciones']     ?? null,
        ], fn($v) => $v !== null);

        if (!empty($update) && $this->sesionModel->update($id, $update) === false) {
            throw new \RuntimeException(json_encode($this->sesionModel->errors()), 422);
        }
    }

    /**
     * Cambia el estado de una sesión (pendiente → finalizado | cancelado).
     *
     * @param  int    $id     ID de la sesión.
     * @param  string $estado Nuevo estado.
     * @return void
     *
     * @throws \RuntimeException 404 si la sesión no existe.
     */
    public function cambiarEstado(int $id, string $estado): void
    {
        if (!$this->sesionModel->find($id)) {
            throw new \RuntimeException('Sesión no encontrada', 404);
        }
        $this->sesionModel->update($id, ['estado' => $estado]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ASISTENCIA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra a un estudiante en la lista de asistencia de una sesión.
     *
     * Valida que el estudiante pertenezca a la misma promoción de la sesión
     * y que no esté ya registrado.
     *
     * @param  int  $idSesion     ID de la sesión.
     * @param  int  $idEstudiante ID del estudiante.
     * @return void
     *
     * @throws \RuntimeException 404 si la sesión o el estudiante no existen.
     * @throws \RuntimeException 409 si el estudiante no pertenece a la promoción
     *                               o ya está registrado en la sesión.
     */
    public function agregarEstudiante(int $idSesion, int $idEstudiante): void
    {
        $sesion = $this->sesionModel->find($idSesion);
        if (!$sesion) {
            throw new \RuntimeException('Sesión no encontrada', 404);
        }

        $estudiante = $this->estudianteModel->find($idEstudiante);
        if (!$estudiante) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }

        if ((int) $estudiante['id_promocion'] !== (int) $sesion['id_promocion']) {
            throw new \RuntimeException(
                'El estudiante no pertenece a la promoción de esta sesión',
                409
            );
        }

        $existe = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if ($existe) {
            throw new \RuntimeException('El estudiante ya está registrado en esta sesión', 409);
        }

        $this->asistenciaModel->insert([
            'id_sesion'     => $idSesion,
            'id_estudiante' => $idEstudiante,
            'asistio'       => null,
        ]);
    }

    /**
     * Elimina a un estudiante de la lista de asistencia de una sesión.
     *
     * @param  int  $idSesion     ID de la sesión.
     * @param  int  $idEstudiante ID del estudiante.
     * @return void
     *
     * @throws \RuntimeException 404 si el registro de asistencia no existe.
     */
    public function quitarEstudiante(int $idSesion, int $idEstudiante): void
    {
        $registro = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if (!$registro) {
            throw new \RuntimeException('El estudiante no está en esta sesión', 404);
        }

        $this->asistenciaModel->delete($registro['id_asistencia']);
    }

    /**
     * Actualiza el estado de asistencia de un estudiante en una sesión.
     *
     * @param  int      $idSesion     ID de la sesión.
     * @param  int      $idEstudiante ID del estudiante.
     * @param  int|null $asistio      1 = asistió, 0 = faltó, null = sin marcar.
     * @return void
     *
     * @throws \RuntimeException 404 si el registro de asistencia no existe.
     */
    public function marcarAsistencia(int $idSesion, int $idEstudiante, ?int $asistio): void
    {
        $registro = $this->asistenciaModel
            ->where('id_sesion', $idSesion)
            ->where('id_estudiante', $idEstudiante)
            ->first();

        if (!$registro) {
            throw new \RuntimeException('El estudiante no está registrado en esta sesión', 404);
        }

        $this->asistenciaModel->update($registro['id_asistencia'], ['asistio' => $asistio]);
    }
}
