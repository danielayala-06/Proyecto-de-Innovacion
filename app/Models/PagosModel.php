<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosModel extends Model
{
    protected $table            = 'pagos';
    protected $primaryKey       = 'id_pago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_contrato',
        'id_form_pago',
        'monto',
        'moneda',
        'voucher',
        'fecha',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_contrato' => 'required|is_natural_no_zero',
        'id_form_pago' => 'required|is_natural_no_zero',
        'monto' => 'required|decimal|greater_than[0]',
        'moneda' => 'required|in_list[PEN,USD]',
        'fecha' => 'required|valid_date',
        'estado' => 'required|in_list[PENDIENTE,PAGADO,ANULADO]'
    ];
    protected $validationMessages = [
        'monto' => [
            'greater_than' => 'El monto debe ser mayor a cero.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
