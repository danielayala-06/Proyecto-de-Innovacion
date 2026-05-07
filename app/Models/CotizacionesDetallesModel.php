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
    protected $validationRules = [
        'tipo_item' => 'required|in_list[PRODUCTO,PAQUETE,PERSONALIZADO]',
        'cantidad' => 'required|integer|greater_than[0]',
        'precio_unitario' => 'required|decimal|greater_than_equal_to[0]',
        'id_cotizacion' => 'required|is_natural_no_zero',
        'id_referencia' => 'required|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'tipo_item' => [
            'required' => 'Debe indicar el tipo de item.'
        ],
        'id_referencia' => [
            'required' => 'Se debe indicar el ID del item.'
        ],
        'cantidad' => [
            'greater_than' => 'La cantidad debe ser mayor a cero.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
