<?php

namespace App\Models;

use CodeIgniter\Model;

class PaquetesModel extends Model
{
    protected $table            = 'paquetes';
    protected $primaryKey       = 'id_paquete';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_paquete',
        'nivel_disponible',
        'descripcion',
        'imagen',
        'precio',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
