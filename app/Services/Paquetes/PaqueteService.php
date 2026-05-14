<?php

namespace App\Services\Paquetes;

use App\Models\PaquetesModel;
use App\Models\PaquetesProductosModel;
use App\Models\ReglasPaquetesModel;

class PaqueteService
{
    protected PaquetesModel          $paqueteModel;
    protected PaquetesProductosModel $paqueteProductoModel;
    protected ReglasPaquetesModel    $reglasPaquetesModel;

    public function __construct()
    {
        $this->paqueteModel         = new PaquetesModel();
        $this->paqueteProductoModel = new PaquetesProductosModel();
        $this->reglasPaquetesModel  = new ReglasPaquetesModel();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Listar paquetes con conteo de productos (sin N+1)
    // ────────────────────────────────────────────────────────────────────────
    public function listar(array $filters = []): array
    {
        return $this->paqueteModel->listarConConteo($filters);
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

        $paquete['productos'] = $this->paqueteProductoModel->listarConProductos($id);
        $paquete['reglas']    = $this->reglasPaquetesModel->where('id_paquete', $id)->findAll();

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
