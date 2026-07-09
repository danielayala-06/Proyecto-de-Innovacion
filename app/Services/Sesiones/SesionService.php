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

    /** @var CotizacionesDetallesModel Acceso a los ítems de la cotización. */
    protected CotizacionesDetallesModel $detalleModel;

    /** @var PaquetesSesionesModel Acceso a la configuración de sesiones por paquete. */
    protected PaquetesSesionesModel $paquetesSesionesModel;

    public function __construct()
    {
        $this->sesionModel           = model(SesionesFotograficasModel::class);
        $this->asistenciaModel       = model(SesionAsistenciaModel::class);
        $this->promocionModel        = model(PromocionesEscolaresModel::class);
        $this->estudianteModel       = model(EstudiantesModel::class);
        $this->detalleModel          = model(CotizacionesDetallesModel::class);
        $this->paquetesSesionesModel = model(PaquetesSesionesModel::class);
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
                      promociones_escolares.seccion,
                      promociones_escolares.num_estudiantes,
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

        $configs    = $this->paquetesSesionesModel->configuracionesPorTipo($idsPaquetes, $tipo);
        $permitidas = 0;
        foreach ($configs as $cfg) {
            $permitidas += (int) $cfg['num_sesiones'];
        }

        return $permitidas;
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
    // DATOS PARA VISTAS WEB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve las promociones y la configuración de sesiones de un contrato.
     * Usado por SesionController::index() para hidratar la vista de gestión.
     *
     * @return array{promociones: array, sesionesConfig: array}
     */
    public function datosPorContrato(int $idCotizacion): array
    {
        $db = \Config\Database::connect();

        $promociones = $db->table('promociones_escolares pe')
            ->select('pe.*, c.nombre_colegio')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio')
            ->where('pe.id_cotizacion', $idCotizacion)
            ->orderBy('pe.nombre', 'ASC')
            ->get()->getResultArray();

        $idsPaquetes = $db->table('cotizaciones_detalles')
            ->select('id_referencia')
            ->where('id_cotizacion', $idCotizacion)
            ->where('tipo_item', 'paquete')
            ->get()->getResultArray();

        $idsPaquetes    = array_column($idsPaquetes, 'id_referencia');
        $sesionesConfig = [];

        if (!empty($idsPaquetes)) {
            $sesionesConfig = $db->table('paquetes_sesiones ps')
                ->select('ps.*, p.nombre_paquete')
                ->join('paquetes p', 'p.id_paquete = ps.id_paquete')
                ->whereIn('ps.id_paquete', $idsPaquetes)
                ->get()->getResultArray();
        }

        return ['promociones' => $promociones, 'sesionesConfig' => $sesionesConfig];
    }

    /**
     * Reúne todos los datos necesarios para generar el PDF de lista de estudiantes.
     * Devuelve array vacío si la promoción no pertenece a esa cotización.
     *
     * @return array{promocion: array, estudiantes: array, sesiones: array, asistencia: array, productos: array}|array{}
     */
    public function datosListaPdf(int $idPromocion, int $idCotizacion): array
    {
        $db = \Config\Database::connect();

        $promocion = $db->table('promociones_escolares pe')
            ->select('pe.*, c.nombre_colegio, c.distrito, c.provincia, cotizaciones.id_cotizacion')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio', 'left')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = pe.id_cotizacion', 'left')
            ->where('pe.id_promocion', $idPromocion)
            ->where('pe.id_cotizacion', $idCotizacion)
            ->get()->getRowArray();

        if (!$promocion) {
            return [];
        }

        $estudiantes = $db->table('estudiantes e')
            ->select('e.id_estudiante, e.nombres, e.apellidos')
            ->where('e.id_promocion', $idPromocion)
            ->orderBy('e.apellidos', 'ASC')
            ->get()->getResultArray();

        $sesiones = $db->table('sesiones_fotograficas sf')
            ->select('sf.id_sesion, sf.fecha_hora_sesion, sf.tipo, sf.estado, sf.observaciones')
            ->where('sf.id_promocion', $idPromocion)
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        $asistencia = [];
        if (!empty($sesiones)) {
            $sesionIds = array_column($sesiones, 'id_sesion');
            $rows      = $db->table('sesion_asistencia sa')
                ->select('sa.id_sesion, sa.id_estudiante, sa.asistio')
                ->whereIn('sa.id_sesion', $sesionIds)
                ->get()->getResultArray();
            foreach ($rows as $row) {
                $asistencia[$row['id_sesion']][$row['id_estudiante']] = $row['asistio'];
            }
        }

        $productos = $db->table('cotizaciones_detalles cd')
            ->select([
                'cd.tipo_item', 'cd.descripcion', 'cd.cantidad', 'cd.precio_unitario',
                'COALESCE(p.nombre_paquete, pr.nombre_producto, cd.descripcion) AS nombre',
            ])
            ->join('paquetes p',   'p.id_paquete  = cd.id_referencia AND cd.tipo_item = "paquete"',  'left')
            ->join('productos pr', 'pr.id_producto = cd.id_referencia AND cd.tipo_item = "producto"', 'left')
            ->where('cd.id_cotizacion', $idCotizacion)
            ->get()->getResultArray();

        return [
            'promocion'   => $promocion,
            'estudiantes' => $estudiantes,
            'sesiones'    => $sesiones,
            'asistencia'  => $asistencia,
            'productos'   => $productos,
        ];
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

        // Límite total de 3 sesiones (no canceladas) por promoción
        $totalActivas = (int) $this->sesionModel
            ->where('id_promocion', $idPromocion)
            ->where('estado !=', 'cancelado')
            ->countAllResults();

        if ($totalActivas >= 3) {
            throw new \RuntimeException('Se alcanzó el límite máximo de 3 sesiones por contrato.', 409);
        }

        $fechaHora = $data['fecha_hora_sesion'] ?? '';
        $this->_validarFecha($fechaHora);

        // Regla 1: no puede ser anterior a la primera sesión ya programada
        // de la misma promoción
        $primeraExistente = $this->sesionModel
            ->where('id_promocion', $idPromocion)
            ->where('estado !=', 'cancelado')
            ->orderBy('fecha_hora_sesion', 'ASC')
            ->first();

        if ($primeraExistente) {
            $dtNueva    = new \DateTime($fechaHora);
            $dtNueva->setTime(0, 0, 0);
            $dtPrimera  = new \DateTime($primeraExistente['fecha_hora_sesion']);
            $dtPrimera->setTime(0, 0, 0);
            if ($dtNueva < $dtPrimera) {
                $fechaMin = (new \DateTime($primeraExistente['fecha_hora_sesion']))->format('d/m/Y');
                throw new \RuntimeException(
                    "La sesión no puede ser anterior a la primera ya programada ({$fechaMin}).", 422
                );
            }
        }

        // Regla 2: máximo 2 sesiones no canceladas en la misma hora del día (globalmente)
        $dt       = \DateTime::createFromFormat('Y-m-d H:i:s', $fechaHora);
        $dia      = $dt->format('Y-m-d');
        $hora     = $dt->format('H');
        $db       = $this->sesionModel->db;
        $cntHora  = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM sesiones_fotograficas
             WHERE estado != 'cancelado'
               AND DATE(fecha_hora_sesion) = ?
               AND HOUR(fecha_hora_sesion) = ?",
            [$dia, $hora]
        )->getRow()->cnt;

        if ($cntHora >= 2) {
            throw new \RuntimeException(
                'Ya hay 2 sesiones programadas en ese horario. El máximo permitido es 2 sesiones por hora.', 409
            );
        }

        $id = $this->sesionModel->insert([
            'id_promocion'      => $idPromocion,
            'fecha_hora_sesion' => $fechaHora,
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
     * Inserta las sesiones iniciales enviadas al firmar un contrato.
     *
     * Se ejecuta dentro de la transacción del llamante; si alguna validación
     * falla lanza RuntimeException y el llamante debe hacer el rollback.
     *
     * @param  array<int, array<string, mixed>> $sesionesInput Hasta 3 elementos con
     *                                                         'fecha_hora_sesion' y 'tipo'.
     * @param  int                              $idPromocion   ID de la promoción destino.
     * @return void
     *
     * @throws \RuntimeException 422 si la fecha/hora no cumple las reglas de negocio.
     */
    public function crearDesdeContrato(array $sesionesInput, int $idPromocion): void
    {
        $tiposValidos = ['exteriores', 'colegio', 'estudio', 'otro'];

        foreach (array_slice($sesionesInput, 0, 3) as $s) {
            $fecha = trim($s['fecha_hora_sesion'] ?? '');
            $tipo  = $s['tipo'] ?? 'otro';

            if (!$fecha || !in_array($tipo, $tiposValidos, true)) {
                continue;
            }

            $this->_validarFecha($fecha);

            $id = $this->sesionModel->insert([
                'id_promocion'      => $idPromocion,
                'fecha_hora_sesion' => $fecha,
                'tipo'              => $tipo,
                'estado'            => 'pendiente',
            ]);

            if ($id === false) {
                throw new \RuntimeException(json_encode($this->sesionModel->errors()), 422);
            }
        }
    }

    /**
     * Valida que la fecha/hora de sesión cumpla las reglas de negocio:
     * no puede ser en el pasado, no más de 10 meses de anticipación,
     * y el horario debe estar entre las 7:00 y las 22:00.
     *
     * @throws \RuntimeException 422 si alguna regla no se cumple.
     */
    private function _validarFecha(string $fecha): void
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
        if (!$dt) {
            throw new \RuntimeException('Formato de fecha inválido (Y-m-d H:i:s).', 422);
        }

        $hoy = new \DateTime('today');
        $max = (new \DateTime('today'))->modify('+10 months');

        if ($dt < $hoy) {
            throw new \RuntimeException('La fecha de la sesión no puede ser en el pasado.', 422);
        }
        if ($dt > $max) {
            throw new \RuntimeException('La sesión no puede programarse con más de 10 meses de anticipación.', 422);
        }

        $hora = (int) $dt->format('G');
        if ($hora < 7 || $hora >= 22) {
            throw new \RuntimeException('El horario debe ser entre las 07:00 y las 22:00.', 422);
        }
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

        $update = [];

        if (!empty($data['fecha_hora_sesion'])) {
            $fechaHora = $data['fecha_hora_sesion'];
            $this->_validarFecha($fechaHora);

            // Regla 1: no puede ser anterior a la primera sesión de la misma promoción
            $idPromocion = (int) $sesion['id_promocion'];
            $primeraExistente = $this->sesionModel
                ->where('id_promocion', $idPromocion)
                ->where('estado !=', 'cancelado')
                ->where('id_sesion !=', $id)
                ->orderBy('fecha_hora_sesion', 'ASC')
                ->first();

            if ($primeraExistente) {
                $dtNueva   = new \DateTime($fechaHora);
                $dtNueva->setTime(0, 0, 0);
                $dtPrimera = new \DateTime($primeraExistente['fecha_hora_sesion']);
                $dtPrimera->setTime(0, 0, 0);
                if ($dtNueva < $dtPrimera) {
                    $fechaMin = (new \DateTime($primeraExistente['fecha_hora_sesion']))->format('d/m/Y');
                    throw new \RuntimeException(
                        "La sesión no puede ser anterior a la primera ya programada ({$fechaMin}).", 422
                    );
                }
            }

            // Regla 2: máximo 2 sesiones en la misma hora (excluyendo la propia)
            $dt      = \DateTime::createFromFormat('Y-m-d H:i:s', $fechaHora);
            $dia     = $dt->format('Y-m-d');
            $hora    = $dt->format('H');
            $db      = $this->sesionModel->db;
            $cntHora = (int) $db->query(
                "SELECT COUNT(*) AS cnt FROM sesiones_fotograficas
                 WHERE estado != 'cancelado'
                   AND id_sesion != ?
                   AND DATE(fecha_hora_sesion) = ?
                   AND HOUR(fecha_hora_sesion) = ?",
                [$id, $dia, $hora]
            )->getRow()->cnt;

            if ($cntHora >= 2) {
                throw new \RuntimeException(
                    'Ya hay 2 sesiones programadas en ese horario. El máximo permitido es 2 sesiones por hora.', 409
                );
            }

            $update['fecha_hora_sesion'] = $fechaHora;
        }
        if (array_key_exists('observaciones', $data)) {
            $update['observaciones'] = $data['observaciones'];
        }

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
