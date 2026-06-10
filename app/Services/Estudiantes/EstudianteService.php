<?php

/**
 * @file    EstudianteService.php
 * @package App\Services\Estudiantes
 *
 * Capa de negocio para el registro y gestión de estudiantes.
 * Cada estudiante se crea en una transacción que involucra tres tablas:
 * personas (apoderado), apoderados y estudiantes.
 */

namespace App\Services\Estudiantes;

use App\Models\EstudiantesModel;
use App\Models\PersonasModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\ApoderadosModel;

/**
 * Servicio de Estudiantes.
 *
 * Responsabilidades:
 * - Listar estudiantes de una promoción con datos del apoderado.
 * - Crear un estudiante en una sola transacción (persona → apoderado → estudiante).
 * - Actualizar datos propios del estudiante (no del apoderado).
 * - Eliminar un estudiante (la asistencia se borra en cascada por FK).
 */
class EstudianteService
{
    /** @var EstudiantesModel Acceso a la tabla `estudiantes`. */
    protected EstudiantesModel $estudianteModel;

    /** @var PersonasModel Acceso a la tabla `personas` (datos del apoderado). */
    protected PersonasModel $personaModel;

    /** @var PromocionesEscolaresModel Acceso a la tabla `promociones_escolares`. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var ApoderadosModel Acceso a la tabla `apoderados`. */
    protected ApoderadosModel $apoderadoModel;

    public function __construct()
    {
        $this->estudianteModel = new EstudiantesModel();
        $this->personaModel    = new PersonasModel();
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->apoderadoModel  = new ApoderadosModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna los estudiantes de una promoción con datos de su apoderado.
     *
     * @param  int $idPromocion ID de la promoción.
     * @return array<int, array<string, mixed>>
     */
    public function listarPorPromocion(int $idPromocion): array
    {
        return $this->estudianteModel->listarConApoderado($idPromocion);
    }

    /**
     * Retorna el perfil completo de un estudiante:
     *   - Datos personales + apoderado.
     *   - Productos contratados en la cotización de su promoción.
     *   - Historial de asistencia a sesiones fotográficas.
     *
     * @param  int                       $id ID del estudiante.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerDetalle(int $id): ?array
    {
        $estudiante = $this->estudianteModel->obtenerConApoderado($id);
        if (!$estudiante) {
            return null;
        }

        $db          = \Config\Database::connect();
        $idPromocion = (int) $estudiante['id_promocion'];

        // Productos/paquetes de la cotización vinculada a la promoción
        $estudiante['productos'] = $db
            ->table('cotizaciones_detalles cd')
            ->select('cd.tipo_item, cd.descripcion, cd.cantidad, cd.precio_unitario')
            ->join('promociones_escolares pe', 'pe.id_cotizacion = cd.id_cotizacion')
            ->where('pe.id_promocion', $idPromocion)
            ->get()->getResultArray();

        // Historial de asistencia del estudiante
        $estudiante['sesiones'] = $db
            ->table('sesion_asistencia sa')
            ->select('sf.id_sesion, sf.tipo, sf.fecha_hora_sesion, sf.estado, sa.asistio')
            ->join('sesiones_fotograficas sf', 'sf.id_sesion = sa.id_sesion')
            ->where('sa.id_estudiante', $id)
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        return $estudiante;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra un estudiante junto a su apoderado en una sola transacción.
     *
     * Flujo de inserción:
     * 1. Crea la persona del apoderado en `personas`.
     * 2. Crea el registro en `apoderados` vinculado a la persona.
     * 3. Crea el estudiante vinculado al apoderado y a la promoción.
     *
     * Estructura esperada en $data:
     * - id_promocion  (int)
     * - estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? }
     * - apoderado:  { nombres, apellidos?, telefono, correo?, tipo_relacion,
     *                 tipo_documento, numero_documento }
     *
     * @param  array<string, mixed> $data Payload completo del formulario.
     * @return int                        ID del estudiante creado.
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     * @throws \RuntimeException 422 si faltan datos o falla validación del modelo.
     * @throws \RuntimeException 500 si la transacción no se confirma.
     */
    public function crear(array $data): int
    {
        $idPromocion = (int) ($data['id_promocion'] ?? 0);

        if (!$this->promocionModel->find($idPromocion)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        $apData = $data['apoderado'] ?? [];
        $esData = $data['estudiante'] ?? [];

        if (empty($apData) || empty($esData)) {
            throw new \RuntimeException('Datos del apoderado y del estudiante son requeridos', 422);
        }

        $db = $this->personaModel->db;
        $db->transStart();

        // 1. Persona del apoderado
        $idPersona = $this->personaModel->insert([
            'nombres'          => $apData['nombres'],
            'apellidos'        => '',
            'telefono'         => $apData['telefono'],
            'correo'           => $apData['correo'] ?? null,
        ]);

        if ($idPersona === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        // 2. Apoderado
        $idApoderado = $this->apoderadoModel->insert([
            'id_persona'    => $idPersona,
            'tipo_relacion' => $apData['tipo_relacion'] ?? 'otro',
        ]);

        if ($idApoderado === false) {
            $db->transRollback();
            throw new \RuntimeException('Error al crear el apoderado', 500);
        }

        // 3. Estudiante
        if (!empty($esData['fecha_nacimiento'])) {
            $fn = \DateTime::createFromFormat('Y-m-d', $esData['fecha_nacimiento']);
            if (!$fn) {
                $db->transRollback();
                throw new \RuntimeException('Formato de fecha de nacimiento inválido (Y-m-d).', 422);
            }
            $hoy = new \DateTime('today');
            $min = (new \DateTime('today'))->modify('-30 years');
            if ($fn > $hoy) {
                $db->transRollback();
                throw new \RuntimeException('La fecha de nacimiento no puede ser en el futuro.', 422);
            }
            if ($fn < $min) {
                $db->transRollback();
                throw new \RuntimeException('El estudiante no puede tener más de 30 años.', 422);
            }
        }

        $idEstudiante = $this->estudianteModel->insert([
            'id_apoderado'     => $idApoderado,
            'id_promocion'     => $idPromocion,
            'nombres'          => $esData['nombres'],
            'apellidos'        => null,
            'fecha_nacimiento' => $esData['fecha_nacimiento'] ?? null,
            'color_fav'        => $esData['color_fav']        ?? null,
            'profesion_futura' => $esData['profesion_futura'] ?? null,
        ]);

        if ($idEstudiante === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->estudianteModel->errors()), 422);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al registrar el estudiante', 500);
        }

        return $idEstudiante;
    }

    /**
     * Actualiza los datos propios del estudiante (no del apoderado).
     *
     * Solo modifica los campos presentes en $data.
     *
     * @param  int                  $id   ID del estudiante.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si el estudiante no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function actualizar(int $id, array $data): void
    {
        if (!$this->estudianteModel->find($id)) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }

        $update = array_filter([
            'nombres'          => $data['nombres']          ?? null,
            'apellidos'        => $data['apellidos']        ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'color_fav'        => $data['color_fav']        ?? null,
            'profesion_futura' => $data['profesion_futura'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($update) && $this->estudianteModel->update($id, $update) === false) {
            throw new \RuntimeException(json_encode($this->estudianteModel->errors()), 422);
        }
    }

    /**
     * Elimina un estudiante. La asistencia asociada se elimina en cascada por FK.
     *
     * @param  int  $id ID del estudiante.
     * @return void
     *
     * @throws \RuntimeException 404 si el estudiante no existe.
     */
    public function eliminar(int $id): void
    {
        if (!$this->estudianteModel->find($id)) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }
        $this->estudianteModel->delete($id);
    }
}
