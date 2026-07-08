<?php

namespace App\Models;

use CodeIgniter\Model;

class PromAlumnoModel extends Model
{
    protected $table            = 'prom_alumnos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'promocion_id', 'nombre', 'token', 'completado', 'id_estudiante', 'enviado', 'created_at',
    ];

    protected $useTimestamps = false;

    public function porToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    public function porPromocion(int $promocion_id): array
    {
        return $this->where('promocion_id', $promocion_id)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    public function generarTokens(int $promocion_id): int
    {
        $alumnos = $this->where('promocion_id', $promocion_id)
                        ->where('token IS NULL OR token', '')
                        ->findAll();

        if (empty($alumnos)) {
            return 0;
        }

        $batch = array_map(fn($a) => [
            'id'    => $a['id'],
            'token' => bin2hex(random_bytes(32)),
        ], $alumnos);

        $this->updateBatch($batch, 'id');

        return count($batch);
    }

    public function crearConToken(int $promocion_id, string $nombre): array
    {
        $token = bin2hex(random_bytes(32));
        $id    = $this->insert([
            'promocion_id' => $promocion_id,
            'nombre'       => $nombre,
            'token'        => $token,
            'completado'   => 0,
            'enviado'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ], true);

        return ['id' => $id, 'token' => $token];
    }
}
