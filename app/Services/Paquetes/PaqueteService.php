<?php

namespace App\Services\Paquetes;

use App\Models\PaquetesModel;
use App\Models\PaquetesProductosModel;

class PaqueteService
{
    protected PaquetesModel         $paqueteModel;
    protected PaquetesProductosModel $paqueteProductoModel;

    public function __construct()
    {
        $this->paqueteModel         = new PaquetesModel();
        $this->paqueteProductoModel = new PaquetesProductosModel();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Listar paquetes con conteo de productos (sin N+1)
    // ────────────────────────────────────────────────────────────────────────
    public function listar(array $filters = []): array
    {
        $query = $this->paqueteModel->orderBy('precio', 'ASC');

        if (!empty($filters['nivel'])) {
            $query->where('nivel_disponible', strtolower($filters['nivel']));
        }
        if (!empty($filters['estado'])) {
            $query->where('estado', strtoupper($filters['estado']));
        }

        $paquetes = $query->findAll();

        if (empty($paquetes)) {
            return [];
        }

        $ids    = array_column($paquetes, 'id_paquete');
        $counts = $this->paqueteModel->db
            ->table('paquetes_productos')
            ->select('id_paquete, COUNT(*) AS num_productos')
            ->whereIn('id_paquete', $ids)
            ->groupBy('id_paquete')
            ->get()->getResultArray();

        $countMap = array_column($counts, 'num_productos', 'id_paquete');

        foreach ($paquetes as &$p) {
            $p['num_productos'] = (int) ($countMap[$p['id_paquete']] ?? 0);
        }

        return $paquetes;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Detalle de un paquete con sus productos y reglas
    // ────────────────────────────────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array
    {
        $paquete = $this->paqueteModel->find($id);

        if (!$paquete) {
            return null;
        }

        $db = $this->paqueteModel->db;

        $paquete['productos'] = $db->table('paquetes_productos pp')
            ->select('pp.id_paquete_prod, pp.cantidad, pr.id_producto,
                      pr.nombre_producto, pr.categoria, pr.tamanio, pr.estado')
            ->join('productos pr', 'pr.id_producto = pp.id_producto')
            ->where('pp.id_paquete', $id)
            ->get()->getResultArray();

        $paquete['reglas'] = $db->table('reglas_paquetes')
            ->where('id_paquete', $id)
            ->get()->getResultArray();

        return $paquete;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Crear paquete con sus productos iniciales
    // ────────────────────────────────────────────────────────────────────────
    public function crear(array $data): int
    {
        $db = $this->paqueteModel->db;
        $db->transStart();

        $idPaquete = $this->paqueteModel->insert([
            'nombre_paquete'   => $data['nombre_paquete'],
            'nivel_disponible' => $data['nivel_disponible'],
            'descripcion'      => $data['descripcion'] ?? null,
            'imagen'           => $data['imagen'] ?? null,
            'precio'           => $data['precio'],
            'categoria'        => $data['categoria'] ?? null,
            'estado'           => 'ACTIVO',
        ]);

        if ($idPaquete === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->paqueteModel->errors()), 422);
        }

        if (!empty($data['productos'])) {
            $items = array_map(fn($p) => [
                'id_paquete'  => $idPaquete,
                'id_producto' => $p['id_producto'],
                'cantidad'    => $p['cantidad'] ?? 1,
            ], $data['productos']);

            if ($this->paqueteProductoModel->insertBatch($items) === false) {
                $db->transRollback();
                throw new \RuntimeException('Error al asociar productos al paquete', 500);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al crear el paquete', 500);
        }

        return $idPaquete;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Actualizar datos del paquete
    // ────────────────────────────────────────────────────────────────────────
    public function actualizar(int $id, array $data): void
    {
        if (!$this->paqueteModel->find($id)) {
            throw new \RuntimeException('Paquete no encontrado', 404);
        }

        $updateData = array_filter([
            'nombre_paquete'   => $data['nombre_paquete']   ?? null,
            'nivel_disponible' => $data['nivel_disponible'] ?? null,
            'descripcion'      => $data['descripcion']      ?? null,
            'precio'           => $data['precio']           ?? null,
            'categoria'        => $data['categoria']        ?? null,
        ], fn($v) => $v !== null);

        if (!empty($updateData) && $this->paqueteModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->paqueteModel->errors()), 422);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Activar / desactivar paquete
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado(int $id, string $estado): void
    {
        if (!$this->paqueteModel->find($id)) {
            throw new \RuntimeException('Paquete no encontrado', 404);
        }

        $this->paqueteModel->update($id, ['estado' => $estado]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Agregar producto al paquete (o actualizar cantidad si ya existe)
    // Retorna 'created' | 'updated' para que el controlador use el HTTP status correcto
    // ────────────────────────────────────────────────────────────────────────
    public function agregarProducto(int $idPaquete, array $data): string
    {
        if (!$this->paqueteModel->find($idPaquete)) {
            throw new \RuntimeException('Paquete no encontrado', 404);
        }

        $existente = $this->paqueteProductoModel
            ->where('id_paquete', $idPaquete)
            ->where('id_producto', $data['id_producto'])
            ->first();

        if ($existente) {
            $this->paqueteProductoModel->update($existente['id_paquete_prod'], [
                'cantidad' => $data['cantidad'] ?? 1,
            ]);
            return 'updated';
        }

        $this->paqueteProductoModel->insert([
            'id_paquete'  => $idPaquete,
            'id_producto' => $data['id_producto'],
            'cantidad'    => $data['cantidad'] ?? 1,
        ]);

        return 'created';
    }

    // ────────────────────────────────────────────────────────────────────────
    // Quitar producto del paquete
    // ────────────────────────────────────────────────────────────────────────
    public function quitarProducto(int $idPaquete, int $idPaqueteProd): void
    {
        $rel = $this->paqueteProductoModel
            ->where('id_paquete', $idPaquete)
            ->where('id_paquete_prod', $idPaqueteProd)
            ->first();

        if (!$rel) {
            throw new \RuntimeException('Relación paquete-producto no encontrada', 404);
        }

        $this->paqueteProductoModel->delete($idPaqueteProd);
    }
}
