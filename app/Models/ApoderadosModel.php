<?php

/**
 * @file    ApoderadosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `apoderados`.
 * Vincula un registro de `personas` con su rol de apoderado
 * y el tipo de relación con el estudiante.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Apoderados.
 *
 * Tabla: `apoderados` (PK: id_apoderado).
 * Campos permitidos: id_persona, tipo_relacion.
 */
class ApoderadosModel extends Model
{
    protected $table            = 'apoderados';
    protected $primaryKey       = 'id_apoderado';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_persona', 'tipo_relacion'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'tipo_relacion' => 'required|in_list[padre,madre,hermano,otro]',
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
