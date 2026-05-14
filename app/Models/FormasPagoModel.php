<?php

namespace App\Models;

use CodeIgniter\Model;

class FormasPagoModel extends Model
{
    protected $table            = 'formas_pago';
    protected $primaryKey       = 'id_form_pago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre_forma_pago', 'tipo_pago'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function listarTodos(): array
    {
        return $this->orderBy('nombre_forma_pago', 'ASC')->findAll();
    }
}
