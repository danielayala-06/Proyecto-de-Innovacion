<?php

namespace App\Models;

use CodeIgniter\Model;

class ApoderadosModel extends Model
{
    protected $table            = 'apoderados';
    protected $primaryKey       = 'id_apoderado';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_persona', 'tipo_relacion'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'tipo_relacion' => 'required|in_list[padre,madre,hermano,otro]',
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
