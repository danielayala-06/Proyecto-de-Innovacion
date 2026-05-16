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
            'apellidos'        => $apData['apellidos']        ?? null,
            'telefono'         => $apData['telefono'],
            'correo'           => $apData['correo']           ?? null,
            'tipo_documento'   => $apData['tipo_documento'],
            'numero_documento' => $apData['numero_documento'],
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
        $idEstudiante = $this->estudianteModel->insert([
            'id_apoderado'     => $idApoderado,
            'id_promocion'     => $idPromocion,
            'nombres'          => $esData['nombres'],
            'apellidos'        => $esData['apellidos'],
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
