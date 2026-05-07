<?php

namespace App\Models;

use CodeIgniter\Model;

class PromocionesEscolaresModel extends Model
{
    protected $table            = 'promociones_escolares';
    protected $primaryKey       = 'id_promocion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'grado',
        'seccion',
        'n_estudiantes',
        'anio',
        'is_active',
        'id_colegio',
        'id_cotizacion'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
