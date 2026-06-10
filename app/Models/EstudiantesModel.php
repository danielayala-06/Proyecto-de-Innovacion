<?php

/**
 * @file    EstudiantesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `estudiantes`.
 * Cada estudiante pertenece a una promoción escolar y tiene un apoderado asignado.
 * Campos opcionales enriquecen el perfil para álbumes escolares (color favorito, profesión futura).
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Estudiantes.
 *
 * Tabla: `estudiantes` (PK: id_estudiante).
 * Relaciones: id_apoderado → apoderados, id_promocion → promociones_escolares.
 */
class EstudiantesModel extends Model
{
    protected $table            = 'estudiantes';
    protected $primaryKey       = 'id_estudiante';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'color_fav',
        'profesion_futura',
        'id_apoderado',
        'id_promocion',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'nombres'          => 'required|max_length[100]',
        'apellidos'        => 'permit_empty|max_length[100]',
        'fecha_nacimiento' => 'permit_empty|valid_date',
        'id_apoderado'     => 'required|is_natural_no_zero',
        'id_promocion'     => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'nombres' => [
            'required' => 'Los nombres del estudiante son obligatorios.',
        ],
        'id_apoderado' => [
            'required' => 'El estudiante debe tener un apoderado.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna un estudiante con los datos de su apoderado por ID.
     *
     * @param  int                       $id ID del estudiante.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerConApoderado(int $id): ?array
    {
        return $this
            ->select('estudiantes.*, p.nombres AS apoderado_nombres, p.apellidos AS apoderado_apellidos,
                      p.telefono AS apoderado_telefono, a.tipo_relacion')
            ->join('apoderados a', 'a.id_apoderado = estudiantes.id_apoderado')
            ->join('personas p',   'p.id_persona = a.id_persona')
            ->where('estudiantes.id_estudiante', $id)
            ->first() ?: null;
    }

    /**
     * Lista los estudiantes de una promoción con los datos de su apoderado.
     *
     * @param  int                      $idPromocion ID de la promoción.
     * @return array<int, array<string, mixed>>
     */
    public function listarConApoderado(int $idPromocion): array
    {
        return $this
            ->select('estudiantes.id_estudiante, estudiantes.nombres, estudiantes.apellidos,
                      estudiantes.fecha_nacimiento, estudiantes.color_fav,
                      estudiantes.profesion_futura, estudiantes.id_apoderado,
                      p.nombres AS apoderado_nombres, p.apellidos AS apoderado_apellidos,
                      p.telefono AS apoderado_telefono, a.tipo_relacion')
            ->join('apoderados a', 'a.id_apoderado = estudiantes.id_apoderado')
            ->join('personas p',   'p.id_persona = a.id_persona')
            ->where('estudiantes.id_promocion', $idPromocion)
            ->orderBy('estudiantes.apellidos', 'ASC')
            ->findAll();
    }
}
