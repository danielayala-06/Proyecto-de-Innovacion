<?php

namespace App\Models\Views;

use CodeIgniter\Model;

class CotizacionClienteModel extends Model
{
    protected $table            = 'cotizacionclientes';
    protected $primaryKey       = 'id_cotizacion';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [];
}
