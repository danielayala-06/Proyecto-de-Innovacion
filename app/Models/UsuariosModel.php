<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuariosModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id_usuario';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_persona',
        'id_rol',
        'nom_user',
        'password_hash',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected function hashPassword(array $data)
    {
        if (! isset($data['data']['password_hash'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash(
            $data['data']['password_hash'],
            PASSWORD_DEFAULT
        );

        return $data;
    }
}
