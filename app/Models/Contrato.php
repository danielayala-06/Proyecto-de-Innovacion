<?php

namespace App\Models;

use CodeIgniter\Model;

class Contrato extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'fecha_contrato',
        'fecha_emision',
        'adelanto',
        'observaciones',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado',
        'total_final',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id_cotizacion' => 'int',
        'adelanto'      => 'float',
        'total_final'   => 'float',
    ];
    protected array $castHandlers = [];

    protected $validationRules = [
        'id_cotizacion'     => 'required|is_natural_no_zero',
        'fecha_contrato'    => 'required|valid_date',
        'fecha_emision'     => 'required|valid_date',
        'fecha_hora_inicio' => 'required|valid_date',
        'fecha_hora_fin'    => 'required|valid_date',
        'total_final'       => 'required|numeric|greater_than[0]',
    ];
    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    private function _baseJoin(): \CodeIgniter\Database\BaseBuilder
    {
        return $this->db->table('contratos c')
            ->select('c.*, cot.nombre_cotizacion, cot.total_estimado as total_cotizacion,
                      p.nombres, p.apellidos, p.telefono')
            ->join('cotizaciones cot', 'cot.id_cotizacion = c.id_cotizacion')
            ->join('clientes cl',      'cl.id_cliente = cot.id_cliente')
            ->join('personas p',       'p.id_persona = cl.id_persona');
    }

    public function contratosConCliente(): array
    {
        return $this->_baseJoin()
            ->orderBy('c.id_contrato', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function contratoConCliente(int $id): ?array
    {
        return $this->_baseJoin()
            ->where('c.id_contrato', $id)
            ->get()
            ->getRowArray() ?: null;
    }
}
