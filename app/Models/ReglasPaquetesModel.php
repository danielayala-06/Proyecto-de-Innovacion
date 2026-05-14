<?php

namespace App\Models;

use CodeIgniter\Model;

class ReglasPaquetesModel extends Model
{
    protected $table            = 'reglas_paquetes';
    protected $primaryKey       = 'id_regla';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'descripcion',
        'tipo_condicion',
        'valor_condicion',
        'tipo_beneficio',
        'valor_beneficio',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function porPaqueteTipo(int $idPaquete, string $tipoBeneficio): array
    {
        return $this->where('id_paquete', $idPaquete)->where('tipo_beneficio', $tipoBeneficio)->findAll();
    }
}
