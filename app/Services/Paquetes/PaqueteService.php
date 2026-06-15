<?php

/**
 * @file    PaqueteService.php
 * @package App\Services\Paquetes
 *
 * Capa de negocio para la gestión del catálogo de paquetes fotográficos.
 * Controla la creación con productos iniciales, actualización de datos,
 * cambio de estado y la gestión de los productos que componen cada paquete.
 */

namespace App\Services\Paquetes;

use App\Models\PaquetesModel;
use App\Models\PaquetesProductosModel;
use App\Models\PaquetesSesionesModel;
use App\Models\ProductosModel;
use App\Models\ReglasPaquetesModel;
use App\Models\ReglasItemsModel;

/**
 * Servicio de Paquetes.
 *
 * Responsabilidades:
 * - Listar paquetes con conteo de productos (sin N+1) y filtros opcionales.
 * - Obtener el detalle de un paquete con sus productos y reglas de bonificación.
 * - Crear un paquete con su lista inicial de productos en transacción.
 * - Actualizar datos editables del paquete (nombre, nivel, precio, etc.).
 * - Activar o desactivar un paquete cambiando su campo `estado`.
 * - Agregar un producto al paquete o actualizar su cantidad si ya existe.
 * - Quitar un producto del paquete.
 */
class PaqueteService
{
    /** @var PaquetesModel Acceso a la tabla `paquetes`. */
    protected PaquetesModel $paqueteModel;

    /** @var PaquetesProductosModel Acceso a la tabla `paquetes_productos`. */
    protected PaquetesProductosModel $paqueteProductoModel;

    /** @var ReglasPaquetesModel Acceso a reglas de bonificación. */
    protected ReglasPaquetesModel $reglasPaquetesModel;

    /** @var PaquetesSesionesModel Acceso a la configuración de sesiones por paquete. */
    protected PaquetesSesionesModel $paquetesSesionesModel;

    /** @var ReglasItemsModel Acceso a la tabla intermedia reglas↔paquetes/productos. */
    protected ReglasItemsModel $reglasItemsModel;

    /** @var ProductosModel Acceso a la tabla `productos` (para validar beneficios). */
    protected ProductosModel $productoModel;

    public function __construct()
    {
        $this->paqueteModel            = model(PaquetesModel::class);
        $this->paqueteProductoModel    = model(PaquetesProductosModel::class);
        $this->paquetesSesionesModel   = model(PaquetesSesionesModel::class);
        $this->reglasPaquetesModel     = model(ReglasPaquetesModel::class);
        $this->reglasItemsModel        = model(ReglasItemsModel::class);
        $this->productoModel           = model(ProductosModel::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista paquetes con el conteo de productos incluido.
     *
     * Filtros admitidos (pasados directamente al model):
     * - estado:            ACTIVO | INACTIVO.
     * - nivel_disponible:  inicial | primaria | secundaria | postgrado | otro.
     *
     * @param  array<string, mixed>     $filters
     * @return array<int, array<string, mixed>>
     */
    public function listar(array $filters = [], bool $conReglas = false): array
    {
        $paquetes = $this->paqueteModel->listarConConteo($filters);

        if ($conReglas && !empty($paquetes)) {
            $ids              = array_column($paquetes, 'id_paquete');
            $reglasPorPaquete = $this->reglasPaquetesModel->reglasParaPaquetesBatch($ids);
            foreach ($paquetes as &$p) {
                $p['reglas'] = $reglasPorPaquete[$p['id_paquete']] ?? [];
            }
            unset($p);
        }

        return $paquetes;
    }

    /**
     * Retorna el detalle completo de un paquete con sus productos y reglas.
     *
     * @param  int                       $id ID del paquete.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerPorId(int $id): ?array
    {
        $paquete = $this->paqueteModel->find($id);

        if (!$paquete) {
            return null;
        }

        $paquete['productos'] = $this->paqueteProductoModel->listarConProductos($id);
        $paquete['reglas']    = $this->reglasPaquetesModel->reglasParaPaquete($id);

        return $paquete;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea un paquete con su lista inicial de productos en una sola transacción.
     *
     * Estructura esperada en $data:
     * - nombre_paquete, nivel_disponible, precio (requeridos)
     * - descripcion?, imagen?, categoria?
     * - productos[]: { id_producto, cantidad? }
     *
     * @param  array<string, mixed> $data Payload del formulario de creación.
     * @return int                        ID del paquete creado.
     *
     * @throws \RuntimeException 422 si falla la validación del modelo de paquete.
     * @throws \RuntimeException 500 si falla la asociación de productos o la transacción.
     */
    public function crear(array $data): int
    {
        $db = $this->paqueteModel->db;
        $db->transStart();

        $idPaquete = $this->paqueteModel->insert([
            'nombre_paquete'   => $data['nombre_paquete'],
            'nivel_disponible' => $data['nivel_disponible'],
            'descripcion'      => $data['descripcion'] ?? null,
            'imagen'           => $data['imagen']       ?? null,
            'precio'           => $data['precio'],
            'categoria'        => $data['categoria']    ?? null,
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

        if (!empty($data['sesiones'])) {
            $tiposValidos = ['colegio', 'exteriores', 'estudio', 'otro'];
            $sesRows = [];
            foreach ($data['sesiones'] as $s) {
                $tipo = $s['tipo_sesion'] ?? '';
                if (!in_array($tipo, $tiposValidos, true)) continue;
                $sesRows[] = [
                    'id_paquete'         => $idPaquete,
                    'tipo_sesion'        => $tipo,
                    'num_sesiones'       => max(1, (int) ($s['num_sesiones'] ?? 1)),
                    'lugar_descripcion'  => ($s['lugar_descripcion'] ?? '') ?: null,
                ];
            }
            if (!empty($sesRows) && $this->paquetesSesionesModel->insertBatch($sesRows) === false) {
                $db->transRollback();
                throw new \RuntimeException('Error al configurar sesiones del paquete', 500);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al crear el paquete', 500);
        }

        return $idPaquete;
    }

    /**
     * Actualiza campos editables de un paquete (actualización parcial).
     *
     * Solo modifica los campos presentes en $data (se ignoran los null).
     *
     * @param  int                  $id   ID del paquete.
     * @param  array<string, mixed> $data Campos a actualizar: nombre_paquete, nivel_disponible,
     *                                    descripcion, precio, categoria.
     * @return void
     *
     * @throws \RuntimeException 404 si el paquete no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
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

    /**
     * Cambia el estado de visibilidad de un paquete (ACTIVO | INACTIVO).
     *
     * @param  int    $id     ID del paquete.
     * @param  string $estado Nuevo estado: 'ACTIVO' | 'INACTIVO'.
     * @return void
     *
     * @throws \RuntimeException 404 si el paquete no existe.
     */
    public function cambiarEstado(int $id, string $estado): void
    {
        if (!$this->paqueteModel->find($id)) {
            throw new \RuntimeException('Paquete no encontrado', 404);
        }

        $this->paqueteModel->update($id, ['estado' => $estado]);
    }

    /**
     * Agrega un producto al paquete o actualiza su cantidad si ya existe.
     *
     * @param  int                  $idPaquete ID del paquete.
     * @param  array<string, mixed> $data      Campos: id_producto, cantidad?.
     * @return string                          'created' si se insertó, 'updated' si se actualizó.
     *
     * @throws \RuntimeException 404 si el paquete no existe.
     */
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

    /**
     * Elimina la relación entre un paquete y uno de sus productos.
     *
     * @param  int  $idPaquete     ID del paquete.
     * @param  int  $idPaqueteProd ID del registro en `paquetes_productos`.
     * @return void
     *
     * @throws \RuntimeException 404 si la relación paquete-producto no existe.
     */
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

    /**
     * Crea una regla de bonificación asociada a un paquete.
     *
     * @param  int                  $idPaquete ID del paquete.
     * @param  array<string, mixed> $data      tipo_condicion, valor_condicion, tipo_beneficio,
     *                                         valor_beneficio, descripcion.
     * @return int                             ID de la regla creada.
     *
     * @throws \RuntimeException 404 si el paquete no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function crearRegla(int $idPaquete, array $data): int
    {
        if (!$this->paqueteModel->find($idPaquete)) {
            throw new \RuntimeException('Paquete no encontrado', 404);
        }

        $data = $this->_resolverBeneficio($data);

        $db = $this->reglasPaquetesModel->db;
        $db->transStart();

        $idRegla = $this->reglasPaquetesModel->insert([
            'descripcion'           => $data['descripcion'],
            'tipo_condicion'        => $data['tipo_condicion'],
            'valor_condicion'       => $data['valor_condicion'],
            'tipo_beneficio'        => $data['tipo_beneficio'],
            'id_producto_beneficio' => $data['id_producto_beneficio'],
            'valor_beneficio'       => $data['valor_beneficio'],
        ]);

        if ($idRegla === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->reglasPaquetesModel->errors()), 422);
        }

        // Enlazar el paquete actual
        $this->reglasItemsModel->insert([
            'id_regla'      => $idRegla,
            'tipo_item'     => 'paquete',
            'id_referencia' => $idPaquete,
        ]);

        // Enlazar items adicionales opcionales [{tipo_item, id_referencia}, ...]
        foreach ($data['items_adicionales'] ?? [] as $item) {
            $tipo = $item['tipo_item']     ?? '';
            $ref  = (int) ($item['id_referencia'] ?? 0);
            if (!in_array($tipo, ['paquete', 'producto'], true) || $ref === 0) continue;
            // Evitar duplicar el paquete actual
            if ($tipo === 'paquete' && $ref === $idPaquete) continue;
            $this->reglasItemsModel->insert([
                'id_regla'      => $idRegla,
                'tipo_item'     => $tipo,
                'id_referencia' => $ref,
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al guardar la regla', 500);
        }

        return $idRegla;
    }

    /**
     * Valida y completa los campos del beneficio según su tipo.
     *
     * - producto_gratis: requiere id_producto_beneficio y auto-rellena valor_beneficio
     *   con el nombre real del producto desde la BD.
     * - otros tipos: requiere valor_beneficio como texto libre; anula id_producto_beneficio.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed> $data completado.
     *
     * @throws \RuntimeException 422 si falta id_producto_beneficio o valor_beneficio.
     * @throws \RuntimeException 404 si el producto beneficio no existe.
     */
    private function _resolverBeneficio(array $data): array
    {
        if ($data['tipo_beneficio'] === 'producto_gratis') {
            $idProd = (int) ($data['id_producto_beneficio'] ?? 0);
            if ($idProd === 0) {
                throw new \RuntimeException('id_producto_beneficio es requerido para beneficios de tipo producto_gratis', 422);
            }

            $producto = $this->productoModel->find($idProd);
            if (!$producto) {
                throw new \RuntimeException('Producto de beneficio no encontrado', 404);
            }

            $data['id_producto_beneficio'] = $idProd;
            $data['valor_beneficio']       = $producto['nombre_producto'];
        } else {
            if (empty(trim($data['valor_beneficio'] ?? ''))) {
                throw new \RuntimeException('valor_beneficio es requerido', 422);
            }
            $data['id_producto_beneficio'] = null;
        }

        return $data;
    }

    /**
     * Elimina una regla de bonificación de un paquete.
     *
     * @param  int  $idRegla ID del registro en `reglas_paquetes`.
     * @return void
     *
     * @throws \RuntimeException 404 si la regla no existe.
     */
    public function eliminarRegla(int $idRegla): void
    {
        if (!$this->reglasPaquetesModel->find($idRegla)) {
            throw new \RuntimeException('Regla no encontrada', 404);
        }

        $this->reglasPaquetesModel->delete($idRegla);
    }
}
