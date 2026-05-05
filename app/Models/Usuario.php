<?php

namespace App\Models;

use CodeIgniter\Model;

class Usuario extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_persona', 'id_rol', 'nombre_user', 'password', 'tipo_usuario', 'estado',
    ];

    public function findByUsername(string $nombreUser): ?array
    {
        return $this->where('nombre_user', $nombreUser)
                    ->where('estado', 'ACTIVO')
                    ->first();
    }
}
