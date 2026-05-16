<?php

/**
 * @file    SesionAsistenciaModel.php
 * @package App\Models
 *
 * Modelo para la tabla `sesion_asistencia`.
 * Registra qué estudiantes están inscritos en una sesión fotográfica
 * y si efectivamente asistieron (null = sin marcar, 1 = asistió, 0 = faltó).
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Asistencia a Sesiones.
 *
 * Tabla: `sesion_asistencia` (PK: id_asistencia).
 * Relaciones: id_sesion → sesiones_fotograficas, id_estudiante → estudiantes.
 * Campo `asistio`: null (sin marcar) | 1 (asistió) | 0 (faltó).
 */
class SesionAsistenciaModel extends Model
{
    protected $table            = 'sesion_asistencia';
    protected $primaryKey       = 'id_asistencia';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_sesion',
        'id_estudiante',
        'asistio',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_sesion'     => 'required|is_natural_no_zero',
        'id_estudiante' => 'required|is_natural_no_zero',
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista los registros de asistencia de una sesión con los nombres del estudiante.
     *
     * @param  int                      $idSesion ID de la sesión.
     * @return array<int, array<string, mixed>>
     */
    public function listarConEstudiante(int $idSesion): array
    {
        return $this
            ->select('sesion_asistencia.id_asistencia, sesion_asistencia.id_estudiante,
                      sesion_asistencia.asistio, e.nombres, e.apellidos')
            ->join('estudiantes e', 'e.id_estudiante = sesion_asistencia.id_estudiante')
            ->where('sesion_asistencia.id_sesion', $idSesion)
            ->orderBy('e.apellidos', 'ASC')
            ->findAll();
    }
}
