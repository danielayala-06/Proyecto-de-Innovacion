<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CotizacionesController
 * Base URL: /api/cotizaciones
 *
 * GET    /api/cotizaciones              → listar con filtros
 * GET    /api/cotizaciones/{id}         → detalle completo + items
 * POST   /api/cotizaciones              → crear con detalles
 * PUT    /api/cotizaciones/{id}         → actualizar cabecera
 * PATCH  /api/cotizaciones/{id}/estado  → cambiar estado
 * DELETE /api/cotizaciones/{id}         → eliminar (solo si PENDIENTE)
 */
class CotizacionesController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/cotizaciones[?estado=PENDIENTE&cliente=3]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $builder = $this->db->table('cotizaciones ct')
            ->select('ct.id_cotizacion, ct.fecha_registro, ct.total_estimado,
                      ct.estado, ct.observaciones,
                      CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente,
                      p.telefono, p.correo,
                      CONCAT(pu.nombres, " ", COALESCE(pu.apellidos,"")) AS vendedor')
            ->join('clientes c',  'c.id_cliente = ct.id_cliente')
            ->join('personas p',  'p.id_persona = c.id_persona')
            ->join('usuarios u',  'u.id_usuario = ct.id_usuario')
            ->join('personas pu', 'pu.id_persona = u.id_persona')
            ->orderBy('ct.fecha_registro', 'DESC');

        if ($estado = $this->request->getGet('estado')) {
            $builder->where('ct.estado', strtoupper($estado));
        }
        if ($idCliente = $this->request->getGet('cliente')) {
            $builder->where('ct.id_cliente', $idCliente);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $builder->get()->getResultArray()]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/cotizaciones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $cotizacion = $this->db->table('cotizaciones ct')
            ->select('ct.*, CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente,
                      p.telefono, p.correo,
                      CONCAT(pu.nombres, " ", COALESCE(pu.apellidos,"")) AS vendedor')
            ->join('clientes c',  'c.id_cliente = ct.id_cliente')
            ->join('personas p',  'p.id_persona = c.id_persona')
            ->join('usuarios u',  'u.id_usuario = ct.id_usuario')
            ->join('personas pu', 'pu.id_persona = u.id_persona')
            ->where('ct.id_cotizacion', $id)
            ->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        // Detalles (ítems)
        $detalles = $this->db->table('cotizaciones_detalles')
            ->where('id_cotizacion', $id)
            ->get()->getResultArray();

        $cotizacion['detalles'] = $detalles;

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $cotizacion]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/cotizaciones
    // Body: { id_cliente, id_usuario, observaciones,
    //         detalles: [{ tipo_item, id_referencia, descripcion, cantidad, precio_unitario }] }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $rules = [
            'id_cliente'  => 'required|integer',
            'id_usuario'  => 'required|integer',
            'detalles'    => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $body     = $this->request->getJSON(true);
        $detalles = $body['detalles'] ?? [];

        if (empty($detalles)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Debe incluir al menos un detalle']);
        }

        // Calcular total
        $total = array_sum(array_map(
            fn($d) => ($d['cantidad'] ?? 1) * ($d['precio_unitario'] ?? 0),
            $detalles
        ));

        $this->db->transStart();

        $this->db->table('cotizaciones')->insert([
            'id_cliente'     => $body['id_cliente'],
            'id_usuario'     => $body['id_usuario'],
            'fecha_registro' => date('Y-m-d'),
            'observaciones'  => $body['observaciones'] ?? null,
            'total_estimado' => $total,
            'estado'         => 'PENDIENTE',
        ]);
        $idCotizacion = $this->db->insertID();

        foreach ($detalles as $d) {
            $this->db->table('cotizaciones_detalles')->insert([
                'id_cotizacion'  => $idCotizacion,
                'tipo_item'      => $d['tipo_item'] ?? 'paquete',
                'id_referencia'  => $d['id_referencia'],
                'descripcion'    => $d['descripcion'] ?? null,
                'cantidad'       => $d['cantidad'] ?? 1,
                'precio_unitario'=> $d['precio_unitario'],
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al crear cotización']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'        => 'success',
                'message'       => 'Cotización creada',
                'id_cotizacion' => $idCotizacion,
                'total'         => $total,
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/cotizaciones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $cotizacion = $this->db->table('cotizaciones')->where('id_cotizacion', $id)->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['estado'] !== 'PENDIENTE') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Solo se puede editar una cotización PENDIENTE']);
        }

        $body = $this->request->getJSON(true);

        $this->db->transStart();

        $this->db->table('cotizaciones')->where('id_cotizacion', $id)->update([
            'observaciones' => $body['observaciones'] ?? $cotizacion['observaciones'],
        ]);

        if (!empty($body['detalles'])) {
            // Reemplazar detalles
            $this->db->table('cotizaciones_detalles')->where('id_cotizacion', $id)->delete();

            $total = 0;
            foreach ($body['detalles'] as $d) {
                $subtotal = ($d['cantidad'] ?? 1) * ($d['precio_unitario'] ?? 0);
                $total   += $subtotal;
                $this->db->table('cotizaciones_detalles')->insert([
                    'id_cotizacion'  => $id,
                    'tipo_item'      => $d['tipo_item'] ?? 'paquete',
                    'id_referencia'  => $d['id_referencia'],
                    'descripcion'    => $d['descripcion'] ?? null,
                    'cantidad'       => $d['cantidad'] ?? 1,
                    'precio_unitario'=> $d['precio_unitario'],
                ]);
            }

            $this->db->table('cotizaciones')->where('id_cotizacion', $id)
                ->update(['total_estimado' => $total]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al actualizar cotización']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cotización actualizada']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/cotizaciones/{id}/estado
    // Body: { estado: "APROBADA" | "RECHAZADA" | "PENDIENTE" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true);
        $estado = strtoupper($body['estado'] ?? '');
        $validos = ['APROBADA', 'RECHAZADA', 'PENDIENTE'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        $cotizacion = $this->db->table('cotizaciones')->where('id_cotizacion', $id)->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        $this->db->table('cotizaciones')->where('id_cotizacion', $id)->update(['estado' => $estado]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado cambiado a {$estado}"]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/cotizaciones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $cotizacion = $this->db->table('cotizaciones')->where('id_cotizacion', $id)->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['estado'] !== 'PENDIENTE') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Solo se puede eliminar una cotización PENDIENTE']);
        }

        $this->db->transStart();
        $this->db->table('cotizaciones_detalles')->where('id_cotizacion', $id)->delete();
        $this->db->table('cotizaciones')->where('id_cotizacion', $id)->delete();
        $this->db->transComplete();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cotización eliminada']);
    }
}
