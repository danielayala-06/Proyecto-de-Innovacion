<?php

namespace App\Models;

use CodeIgniter\Model;

class PaquetesSesionesModel extends Model
{
    protected $table            = 'paquetes_sesiones';
    protected $primaryKey       = 'id_paquete_sesion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'tipo_sesion',
        'lugar_descripcion',
        'num_sesiones',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_paquete'   => 'required|is_natural_no_zero',
        'tipo_sesion'  => 'required|in_list[exteriores,colegio,estudio,otro]',
        'num_sesiones' => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'tipo_sesion' => [
            'in_list' => 'Tipo inválido. Use: exteriores, colegio, estudio u otro.',
        ],
        'num_sesiones' => [
            'is_natural_no_zero' => 'El número de sesiones debe ser al menos 1.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
