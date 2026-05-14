<?php

namespace App\Models;

use CodeIgniter\Model;

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
