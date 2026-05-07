<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionesDetallesModel extends Model
{
    protected $table            = 'cotizaciones_detalles';
    protected $primaryKey       = 'id_detalle';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'tipo_item',        // paquete/producto
        'id_referencia',    // id_paquete/id_producto
        'descripcion',
        'cantidad',
        'precio_unitario',   // Sin subtotal pq va a ser generado
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
