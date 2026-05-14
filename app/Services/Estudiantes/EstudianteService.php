<?php

namespace App\Services\Estudiantes;

use App\Models\EstudiantesModel;
use App\Models\PersonasModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\ApoderadosModel;

class EstudianteService
{
    protected EstudiantesModel          $estudianteModel;
    protected PersonasModel             $personaModel;
    protected PromocionesEscolaresModel $promocionModel;
    protected ApoderadosModel           $apoderadoModel;

    public function __construct()
    {
        $this->estudianteModel = new EstudiantesModel();
        $this->personaModel    = new PersonasModel();
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->apoderadoModel  = new ApoderadosModel();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Listar estudiantes de una promoción con datos del apoderado
    // ────────────────────────────────────────────────────────────────────────
    public function listarPorPromocion(int $idPromocion): array
    {
        return $this->estudianteModel->listarConApoderado($idPromocion);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Crear estudiante con su apoderado en una sola transacción
    // Payload esperado:
    //   estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? }
    //   apoderado:  { nombres, apellidos, telefono, tipo_relacion,
    //                 tipo_documento, numero_documento, correo? }
    //   id_promocion
    // ────────────────────────────────────────────────────────────────────────
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
            'apellidos'        => $apData['apellidos'] ?? null,
            'telefono'         => $apData['telefono'],
            'correo'           => $apData['correo'] ?? null,
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

    // ────────────────────────────────────────────────────────────────────────
    // Actualizar datos del estudiante (solo campos propios, no apoderado)
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // Eliminar un estudiante (elimina en cascada su asistencia por FK)
    // ────────────────────────────────────────────────────────────────────────
    public function eliminar(int $id): void
    {
        if (!$this->estudianteModel->find($id)) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }
        $this->estudianteModel->delete($id);
    }
}
