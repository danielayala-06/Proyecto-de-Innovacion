<?php

namespace App\Models;

use CodeIgniter\Model;

class PaquetesProductosModel extends Model
{
    protected $table            = 'paquetes_productos';
    protected $primaryKey       = 'id_paquete_prod';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cantidad',
        'id_paquete',
        'id_producto'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
