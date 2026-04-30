<?php

namespace App\Models;

use CodeIgniter\Model;

class Cliente extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'red_social',
        'id_persona',
        'id_empresa',
        'metodo_comunicacion',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id_persona'            => 'int',
        'id_empresa'            => '?int',
    ];
    protected array $castHandlers = [];

    // Validation
    protected $validationRules      = [
        'id_persona'            => 'required|is_natural_no_zero|max_length[8]',
        'id_empresa'            => 'is_natural_no_zero|max_length[8]',
        'red_social'            => 'max_length[100]|min_length[3]',
        'metodo_comunicacion'   => 'required|in_list[CORREO, WHATSAPP, LLAMADAS, OTRO]',
        'estado'                => 'required|in_list[ACTIVO, INACTIVO]',
    ];
    protected $validationMessages   = [
        'id_persona'        => [
            'required' => 'El id de la persona es obligatoria',
            'is_natural_no_zero' => 'El id tiene que ser un valor numerico',
            'max_length'=> 'El id es muy largo',
        ],
        'id_empresa'        => [
            'is_natural_no_zero' => 'El id debe ser un entero mayor que cero.',
            'max_length'=> 'El id es muy largo.',
        ],
        'red_social'          => [
            'max_length'=> 'Red Social muy largo.',
            'min_length'=> 'Red Social debe tener minimo 3 caracteres.',
        ],
        'metodo_comunicacion' => [
            'required' => 'El metodo de comunicacion es obligatorio',
            'in_list' => 'Metodo de comunicacion no valido',
        ],
        'estado'              => [
            'required' => 'El estado es obligatorio',
            'in_list' => 'Estado no valido',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function clientesWithPersona(int $id = null)
    {
        // Devolvemos todos los clientes con las personas
        $select = '
            c.*,
            p.nombres,
            p.apellidos,
            p.numero_documento,
            p.telefono,
            p.correo as persona_correo';

        if ($id === null) {
            return $this->db->table('clientes c')
                ->select($select)
                ->join('personas p', 'p.id_persona = c.id_persona')
                ->get()
                ->getResultArray();
        }

        return $this->db->table('clientes c')
            ->select($select)
            ->join('personas p', 'p.id_persona = c.id_persona')
            ->where('c.id_cliente', $id)
            ->get()
            ->getResultArray();
    }
}
