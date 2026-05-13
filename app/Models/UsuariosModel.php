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
        'nombre_user',
        'password_hash',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected $validationRules = [
        'id_persona'    => 'required|is_natural_no_zero',
        'id_rol'        => 'required|is_natural_no_zero',
        'nombre_user'   => 'required|min_length[4]|max_length[50]|is_unique[usuarios.nombre_user,id_usuario,{id_usuario}]',
        'password_hash' => 'required|min_length[8]',
        'estado'        => 'required|in_list[ACTIVO,INACTIVO]',
    ];

    protected $validationMessages = [
        'nombre_user' => [
            'required'    => 'El nombre de usuario es obligatorio.',
            'is_unique'   => 'El nombre de usuario ya existe.',
            'min_length'  => 'El usuario debe tener mínimo 4 caracteres.',
            'max_length'  => 'El usuario no puede superar 50 caracteres.',
        ],
        'password_hash' => [
            'min_length' => 'La contraseña debe tener mínimo 8 caracteres.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Busca un usuario por nombre_user haciendo join con personas y roles.
     * Retorna null si no existe.
     */
    public function findByUsername(string $username): ?array
    {
        return $this
            ->select('usuarios.id_usuario, usuarios.nombre_user, usuarios.password_hash, usuarios.estado, usuarios.id_rol, personas.nombres, personas.apellidos, roles.rol')
            ->join('personas', 'personas.id_persona = usuarios.id_persona')
            ->join('roles',    'roles.id_rol = usuarios.id_rol')
            ->where('usuarios.nombre_user', $username)
            ->first();
    }

    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password_hash'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash(
            $data['data']['password_hash'],
            PASSWORD_DEFAULT
        );

        return $data;
    }
}
