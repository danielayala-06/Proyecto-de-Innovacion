<?php

/**
 * @file    PaquetesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión del catálogo de paquetes fotográficos.
 * Delega la lógica de negocio en PaqueteService y formatea
 * las respuestas con PaqueteTransformer.
 *
 * Endpoints:
 *   GET    /api/paquetes                        → listar con filtros opcionales
 *   GET    /api/paquetes/{id}                   → detalle + productos + reglas
 *   POST   /api/paquetes                        → crear paquete
 *   PUT    /api/paquetes/{id}                   → actualizar datos
 *   PATCH  /api/paquetes/{id}/estado            → activar / desactivar
 *   POST   /api/paquetes/{id}/productos         → agregar producto al paquete
 *   DELETE /api/paquetes/{id}/productos/{pid}   → quitar producto del paquete
 *   POST   /api/paquetes/{id}/reglas            → crear regla de bonificación
 *   DELETE /api/paquetes/reglas/{rid}           → eliminar regla
 *   POST   /api/paquetes/{id}/imagen            → subir / reemplazar imagen
 *   DELETE /api/paquetes/{id}/imagen            → eliminar imagen
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\PaquetesModel;
use App\Models\ProductosModel;
use App\Services\Paquetes\PaqueteService;
use App\Transformers\PaqueteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Paquetes.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class PaquetesApi extends BaseApiController
{
    /** @var PaqueteService Servicio con la lógica de negocio de paquetes. */
    protected PaqueteService $paqueteService;

    /** @var PaqueteTransformer Formateador de respuestas JSON. */
    protected PaqueteTransformer $paqueteTransformer;

    /** @var ProductosModel Para el endpoint GET /api/productos. */
    protected ProductosModel $productosModel;

    /** @var PaquetesModel Para actualizar el campo imagen directamente. */
    protected PaquetesModel $paquetesModel;

    /** Directorio de almacenamiento de imágenes de paquetes (dentro de public/). */
    private const IMG_DIR = 'images/paquetes/';

    /** Dimensiones máximas de la imagen procesada. */
    private const IMG_MAX_W = 720;
    private const IMG_MAX_H = 480;

    public function __construct()
    {
        $this->paqueteService     = new PaqueteService();
        $this->paqueteTransformer = new PaqueteTransformer();
        $this->productosModel     = new ProductosModel();
        $this->paquetesModel      = new PaquetesModel();
    }

    /**
     * GET /api/paquetes[?nivel=secundaria&estado=ACTIVO]
     *
     * @return ResponseInterface 200 con la lista de paquetes.
     */
    public function index()
    {
        $filters = array_filter([
            'nivel'  => $this->request->getGet('nivel'),
            'estado' => $this->request->getGet('estado'),
        ], fn($v) => $v !== null);

        $conReglas = (bool) $this->request->getGet('con_reglas');

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->paqueteTransformer->transformMany(
                    $this->paqueteService->listar($filters, $conReglas)
                ),
            ]);
    }

    /**
     * GET /api/paquetes/{id}
     *
     * Incluye productos asociados y reglas de bonificación.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
    public function show($id)
    {
        $paquete = $this->paqueteService->obtenerPorId((int) $id);

        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->paqueteTransformer->transform($paquete)]);
    }

    /**
     * POST /api/paquetes
     *
     * Body: { nombre_paquete, nivel_disponible, precio, descripcion?,
     *         categoria?, imagen?, productos?: [{ id_producto, cantidad }] }
     *
     * @return ResponseInterface 201 con id_paquete | 422 si falla validación.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_paquete'   => 'required|max_length[150]',
            'nivel_disponible' => 'required|in_list[inicial,primaria,secundaria,postgrado,otro]',
            'precio'           => 'required|decimal|greater_than[0]',
            'categoria'        => 'permit_empty|in_list[Cuadros,Anuarios,Paquetes,otros]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $idPaquete = $this->paqueteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Paquete creado', 'id_paquete' => $idPaquete]);
    }

    /**
     * PUT /api/paquetes/{id}
     *
     * Body: { nombre_paquete?, nivel_disponible?, precio?, descripcion?, categoria? }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_paquete'   => 'permit_empty|max_length[150]',
            'nivel_disponible' => 'permit_empty|in_list[inicial,primaria,secundaria,postgrado,otro]',
            'precio'           => 'permit_empty|decimal|greater_than[0]',
            'categoria'        => 'permit_empty|in_list[Cuadros,Anuarios,Paquetes,otros]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $this->paqueteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Paquete actualizado']);
    }

    /**
     * PATCH /api/paquetes/{id}/estado
     *
     * Body: { estado: "ACTIVO" | "INACTIVO" }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = strtoupper($body['estado'] ?? '');

        if (!in_array($estado, ['ACTIVO', 'INACTIVO'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ACTIVO o INACTIVO']);
        }

        try {
            $this->paqueteService->cambiarEstado((int) $id, $estado);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Paquete {$estado}"]);
    }

    /**
     * POST /api/paquetes/{id}/productos
     *
     * Body: { id_producto, cantidad? }
     *
     * Si el producto ya está en el paquete, actualiza solo la cantidad.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 201 (creado) | 200 (cantidad actualizada) | 404 | 422.
     */
    public function agregarProducto($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (empty($body['id_producto'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_producto es requerido']);
        }

        try {
            $action = $this->paqueteService->agregarProducto((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode($action === 'created' ? ResponseInterface::HTTP_CREATED : ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => $action === 'created'
                    ? 'Producto agregado al paquete'
                    : 'Cantidad de producto actualizada',
            ]);
    }

    /**
     * DELETE /api/paquetes/{id}/productos/{pid}
     *
     * @param  mixed $id  ID del paquete.
     * @param  mixed $pid ID del registro en paquetes_productos.
     * @return ResponseInterface 200 | 404.
     */
    public function quitarProducto($id, $pid)
    {
        try {
            $this->paqueteService->quitarProducto((int) $id, (int) $pid);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Producto removido del paquete']);
    }

    /**
     * POST /api/paquetes/{id}/reglas
     *
     * Body: { tipo_condicion, valor_condicion, tipo_beneficio, valor_beneficio, descripcion }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 201 con id_regla | 404 | 422.
     */
    public function crearRegla($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'tipo_condicion'  => 'required|in_list[CANTIDAD_MIN,CANTIDAD_MAX,ELEGIBILIDAD_MIN]',
            'valor_condicion' => 'required|decimal',
            'tipo_beneficio'  => 'required|in_list[producto_gratis,sesion_unica,otro]',
            'descripcion'     => 'required|max_length[300]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $idRegla = $this->paqueteService->crearRegla((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Regla creada', 'id_regla' => $idRegla]);
    }

    /**
     * GET /api/productos[?estado=ACTIVO]
     *
     * Lista simplificada de productos para selectores en formularios.
     *
     * @return ResponseInterface 200 con la lista de productos.
     */
    public function indexProductos()
    {
        $productos = $this->productosModel->where('estado', 'ACTIVO')->orderBy('nombre_producto', 'ASC')->findAll();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => array_map(fn($p) => [
                    'id_producto'    => (int) $p['id_producto'],
                    'nombre_producto' => $p['nombre_producto'],
                    'categoria'      => $p['categoria'],
                ], $productos),
            ]);
    }

    /**
     * DELETE /api/paquetes/reglas/{rid}
     *
     * @param  mixed $rid ID de la regla.
     * @return ResponseInterface 200 | 404.
     */
    public function eliminarRegla($rid)
    {
        try {
            $this->paqueteService->eliminarRegla((int) $rid);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Regla eliminada']);
    }

    /**
     * POST /api/paquetes/{id}/imagen
     *
     * Recibe multipart/form-data con el campo `imagen`.
     * Redimensiona y convierte a WebP (máx. 720×480 px, calidad 82).
     * Almacena en public/images/paquetes/paq_{id}.webp.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 con imagen_url | 404 | 422.
     */
    public function subirImagen($id)
    {
        $paquete = $this->paquetesModel->find((int) $id);
        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        $archivo = $this->request->getFile('imagen');
        if (!$archivo || !$archivo->isValid() || $archivo->hasMoved()) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Archivo inválido o no recibido']);
        }

        $mime = $archivo->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Solo se aceptan imágenes JPG, PNG o WebP']);
        }

        if ($archivo->getSize() > 5 * 1024 * 1024) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'La imagen no puede superar los 5 MB']);
        }

        $gdDisponible  = function_exists('imagecreatefromjpeg');
        $ext           = $gdDisponible ? 'webp' : strtolower($archivo->getExtension() ?: 'jpg');
        $nombreArchivo = 'paq_' . (int) $id . '.' . $ext;
        $dirAbsoluto   = FCPATH . self::IMG_DIR;
        $destino       = $dirAbsoluto . $nombreArchivo;

        if (!is_dir($dirAbsoluto)) {
            mkdir($dirAbsoluto, 0755, true);
        }

        // Eliminar versión anterior con cualquier extensión
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $e) {
            $prev = $dirAbsoluto . 'paq_' . (int) $id . '.' . $e;
            if (is_file($prev) && $prev !== $destino) {
                unlink($prev);
            }
        }

        try {
            if ($gdDisponible) {
                $this->_procesarImagen($archivo->getTempName(), $destino, $mime);
            } else {
                $archivo->move($dirAbsoluto, $nombreArchivo);
            }
        } catch (\Throwable) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al procesar la imagen']);
        }

        $this->paquetesModel->update((int) $id, ['imagen' => $nombreArchivo]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Imagen guardada',
                'imagen_url' => base_url(self::IMG_DIR . $nombreArchivo . '?v=' . time()),
            ]);
    }

    /**
     * DELETE /api/paquetes/{id}/imagen
     *
     * Elimina la imagen del paquete del disco y limpia el campo en BD.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 | 404.
     */
    public function eliminarImagen($id)
    {
        $paquete = $this->paquetesModel->find((int) $id);
        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        if (!empty($paquete['imagen'])) {
            $ruta = FCPATH . self::IMG_DIR . $paquete['imagen'];
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }

        $this->paquetesModel->update((int) $id, ['imagen' => null]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Imagen eliminada']);
    }

    /**
     * Redimensiona y convierte una imagen a WebP usando la extensión GD.
     *
     * @param  string $origen   Ruta temporal del archivo subido.
     * @param  string $destino  Ruta absoluta donde se guardará el WebP.
     * @param  string $mime     MIME type del archivo original.
     * @throws \RuntimeException Si GD no está disponible o el procesamiento falla.
     */
    private function _procesarImagen(string $origen, string $destino, string $mime): void
    {
        if (!function_exists('imagecreatefromjpeg')) {
            throw new \RuntimeException('La extensión GD no está disponible');
        }

        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($origen),
            'image/png'  => imagecreatefrompng($origen),
            'image/webp' => imagecreatefromwebp($origen),
            default      => throw new \RuntimeException('Tipo de imagen no soportado'),
        };

        if (!$src) {
            throw new \RuntimeException('No se pudo leer la imagen');
        }

        $anchoOrig = imagesx($src);
        $altoOrig  = imagesy($src);

        $ratio  = min(self::IMG_MAX_W / $anchoOrig, self::IMG_MAX_H / $altoOrig, 1.0);
        $ancho  = (int) round($anchoOrig * $ratio);
        $alto   = (int) round($altoOrig  * $ratio);

        $dst = imagecreatetruecolor($ancho, $alto);

        // Relleno blanco para conservar transparencias PNG
        $blanco = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $blanco);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $ancho, $alto, $anchoOrig, $altoOrig);
        imagewebp($dst, $destino, 82);
    }

}
