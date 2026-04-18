<?php

namespace App\Models\Views;

use CodeIgniter\Model;

class CotizacionesFull extends Model
{
    protected $table            = 'vw_cotizaciones_full';
    protected $primaryKey       = 'id_cotizacion';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];
}
