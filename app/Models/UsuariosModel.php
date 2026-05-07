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
    protected $validationRules = [
        'id_persona' => 'required|is_natural_no_zero',
        'id_rol' => 'required|is_natural_no_zero',
        'nom_user' => 'required|min_length[4]|max_length[50]|is_unique[usuarios.nom_user,id_usuario,{id_usuario}]',
        'password_hash' => 'required|min_length[8]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]'
    ];
    protected $validationMessages = [
        'nom_user' => [
            'required' => 'El nombre de usuario es obligatorio.',
            'is_unique' => 'El nombre de usuario ya existe.'
        ],
        'password_hash' => [
            'min_length' => 'La contraseña debe tener mínimo 8 caracteres.'
        ]
    ];
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
