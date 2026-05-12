<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PaquetesApi
 * Base URL: /api/paquetes
 *
 * GET    /api/paquetes                      → listar paquetes con sus productos
 * GET    /api/paquetes/{id}                 → detalle + productos + reglas
 * POST   /api/paquetes                      → crear paquete
 * PUT    /api/paquetes/{id}                 → actualizar paquete
 * PATCH  /api/paquetes/{id}/estado          → activar / desactivar
 * POST   /api/paquetes/{id}/productos       → agregar producto al paquete
 * DELETE /api/paquetes/{id}/productos/{pid} → quitar producto del paquete
 */
class PaquetesApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/paquetes[?nivel=secundaria&estado=ACTIVO]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $builder = $this->db->table('paquetes')->orderBy('precio', 'ASC');

        if ($nivel = $this->request->getGet('nivel')) {
            $builder->where('nivel_disponible', strtolower($nivel));
        }
        if ($estado = $this->request->getGet('estado')) {
            $builder->where('estado', strtoupper($estado));
        }

        $paquetes = $builder->get()->getResultArray();

        // Agregar conteo de productos a cada paquete
        foreach ($paquetes as &$p) {
            $p['num_productos'] = $this->db->table('paquetes_productos')
                ->where('id_paquete', $p['id_paquete'])
                ->countAllResults();
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $paquetes]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/paquetes/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $paquete = $this->db->table('paquetes')->where('id_paquete', $id)->get()->getRowArray();

        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        // Productos incluidos
        $productos = $this->db->table('paquetes_productos pp')
            ->select('pp.id_paquete_prod, pp.cantidad, pr.id_producto,
                      pr.nombre_producto, pr.categoria, pr.tamanio, pr.estado')
            ->join('productos pr', 'pr.id_producto = pp.id_producto')
            ->where('pp.id_paquete', $id)
            ->get()->getResultArray();

        // Reglas
        $reglas = $this->db->table('reglas_paquetes')
            ->where('id_paquete', $id)
            ->get()->getResultArray();

        $paquete['productos'] = $productos;
        $paquete['reglas']    = $reglas;

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $paquete]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/paquetes
    // Body: { nombre_paquete, nivel_disponible, descripcion, precio,
    //         productos: [{ id_producto, cantidad }] }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_paquete'   => 'required|max_length[150]',
            'nivel_disponible' => 'required|in_list[inicial-primaria,secundaria,postgrado,otro]',
            'precio'           => 'required|decimal',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $this->db->transStart();

        $this->db->table('paquetes')->insert([
            'nombre_paquete'   => $body['nombre_paquete'],
            'nivel_disponible' => $body['nivel_disponible'],
            'descripcion'      => $body['descripcion'] ?? null,
            'imagen'           => $body['imagen'] ?? null,
            'precio'           => $body['precio'],
            'categoria'        => $body['categoria'] ?? null,
            'estado'           => 'ACTIVO',
        ]);
        $idPaquete = $this->db->insertID();

        if (!empty($body['productos'])) {
            foreach ($body['productos'] as $prod) {
                $this->db->table('paquetes_productos')->insert([
                    'id_paquete'  => $idPaquete,
                    'id_producto' => $prod['id_producto'],
                    'cantidad'    => $prod['cantidad'] ?? 1,
                ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al crear el paquete']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Paquete creado', 'id_paquete' => $idPaquete]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/paquetes/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $paquete = $this->db->table('paquetes')->where('id_paquete', $id)->get()->getRowArray();

        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        $body = $this->request->getJSON(true);

        $nivelesValidos = ['inicial-primaria', 'secundaria', 'postgrado', 'otro'];

        if (isset($body['nivel_disponible']) && !in_array($body['nivel_disponible'], $nivelesValidos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Nivel disponible inválido. Valores permitidos: ' . implode(', ', $nivelesValidos)]);
        }

        $updateData = array_filter([
            'nombre_paquete'   => $body['nombre_paquete'] ?? null,
            'nivel_disponible' => $body['nivel_disponible'] ?? null,
            'descripcion'      => $body['descripcion'] ?? null,
            'precio'           => $body['precio'] ?? null,
            'categoria'        => $body['categoria'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($updateData)) {
            $this->db->table('paquetes')->where('id_paquete', $id)->update($updateData);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Paquete actualizado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/paquetes/{id}/estado
    // Body: { estado: "ACTIVO" | "INACTIVO" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true);
        $estado = strtoupper($body['estado'] ?? '');

        if (!in_array($estado, ['ACTIVO', 'INACTIVO'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ACTIVO o INACTIVO']);
        }

        $paquete = $this->db->table('paquetes')->where('id_paquete', $id)->get()->getRowArray();

        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        $this->db->table('paquetes')->where('id_paquete', $id)->update(['estado' => $estado]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Paquete {$estado}"]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/paquetes/{id}/productos
    // Body: { id_producto, cantidad }
    // ────────────────────────────────────────────────────────────────────────
    public function agregarProducto($id)
    {
        $body = $this->request->getJSON(true);

        if (empty($body['id_producto'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_producto es requerido']);
        }

        // Verificar duplicado
        $existe = $this->db->table('paquetes_productos')
            ->where('id_paquete', $id)
            ->where('id_producto', $body['id_producto'])
            ->get()->getRowArray();

        if ($existe) {
            // Actualizar cantidad
            $this->db->table('paquetes_productos')
                ->where('id_paquete_prod', $existe['id_paquete_prod'])
                ->update(['cantidad' => $body['cantidad'] ?? 1]);

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON(['status' => 'success', 'message' => 'Cantidad de producto actualizada']);
        }

        $this->db->table('paquetes_productos')->insert([
            'id_paquete'  => $id,
            'id_producto' => $body['id_producto'],
            'cantidad'    => $body['cantidad'] ?? 1,
        ]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Producto agregado al paquete']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/paquetes/{id}/productos/{pid}
    // ────────────────────────────────────────────────────────────────────────
    public function quitarProducto($id, $pid)
    {
        $rel = $this->db->table('paquetes_productos')
            ->where('id_paquete', $id)
            ->where('id_paquete_prod', $pid)
            ->get()->getRowArray();

        if (!$rel) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Relación paquete-producto no encontrada']);
        }

        $this->db->table('paquetes_productos')->where('id_paquete_prod', $pid)->delete();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Producto removido del paquete']);
    }
}
