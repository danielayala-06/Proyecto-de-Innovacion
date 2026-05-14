<?php

namespace App\Models;

use CodeIgniter\Model;

class ColegiosModel extends Model
{
    protected $table            = 'colegios';
    protected $primaryKey       = 'id_colegio';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_colegio',
        'distrito',
        'provincia',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre_colegio' => 'required|max_length[100]',
        'distrito'       => 'permit_empty|max_length[100]',
        'provincia'      => 'permit_empty|max_length[100]',
        'estado'         => 'permit_empty|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages = [
        'nombre_colegio' => ['required' => 'El nombre del colegio es obligatorio.'],
        'distrito'       => ['required' => 'El distrito es obligatorio.'],
        'provincia'      => ['required' => 'La provincia es obligatoria.'],
        'estado'         => ['in_list'  => 'Estado inválido. Use: ACTIVO o INACTIVO.'],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
