<?php

/**
 * @file    EstudiantesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de estudiantes dentro de una promoción escolar.
 * Delega la lógica de negocio en EstudianteService y formatea
 * las respuestas con EstudianteTransformer.
 *
 * Endpoints:
 *   GET    /api/estudiantes?id_promocion=X  → listar por promoción
 *   POST   /api/estudiantes                 → crear (incluye apoderado + persona en transacción)
 *   PUT    /api/estudiantes/{id}            → actualizar datos del estudiante
 *   DELETE /api/estudiantes/{id}            → eliminar
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Estudiantes\EstudianteService;
use App\Transformers\EstudianteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Estudiantes.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class EstudiantesApi extends BaseApiController
{
    /** @var EstudianteService Servicio con la lógica de negocio de estudiantes. */
    protected EstudianteService $estudianteService;

    /** @var EstudianteTransformer Formateador de respuestas JSON. */
    protected EstudianteTransformer $estudianteTransformer;

    public function __construct()
    {
        $this->estudianteService     = service('estudianteService');
        $this->estudianteTransformer = new EstudianteTransformer();
    }

    /**
     * GET /api/estudiantes/stock?id_promocion=X
     *
     * Devuelve el stock de productos del paquete para una promoción:
     * total contratado y disponible (no asignado aún a ningún estudiante).
     *
     * @return ResponseInterface 200 con array de productos | 422 si falta id_promocion.
     */
    public function stockPromocion()
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->estudianteService->stockPorPromocion($idPromocion),
            ]);
    }

    /**
     * GET /api/estudiantes/{id}
     *
     * Retorna el perfil completo: datos personales, apoderado,
     * productos contratados y historial de asistencia a sesiones.
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 con el perfil | 404 si no existe.
     */
    public function show($id)
    {
        $estudiante = $this->estudianteService->obtenerDetalle((int) $id);

        if (!$estudiante) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Estudiante no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => [
                    'id_estudiante'       => (int)   $estudiante['id_estudiante'],
                    'nombres'             =>          $estudiante['nombres'],
                    'apellidos'           =>          $estudiante['apellidos'],
                    'fecha_nacimiento'    =>          $estudiante['fecha_nacimiento']    ?? null,
                    'color_fav'           =>          $estudiante['color_fav']           ?? null,
                    'profesion_futura'    =>          $estudiante['profesion_futura']    ?? null,
                    'apoderado_nombres'   =>          $estudiante['apoderado_nombres']   ?? null,
                    'apoderado_apellidos' =>          $estudiante['apoderado_apellidos'] ?? null,
                    'apoderado_telefono'  =>          $estudiante['apoderado_telefono']  ?? null,
                    'tipo_relacion'       =>          $estudiante['tipo_relacion']       ?? null,
                    'productos'           =>          $estudiante['productos'],
                    'sesiones'            =>          $estudiante['sesiones'],
                ],
            ]);
    }

    /**
     * GET /api/estudiantes?id_promocion=X
     *
     * @return ResponseInterface 200 con la lista de estudiantes | 422 si falta id_promocion.
     */
    public function index()
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);

        if (!$idPromocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->estudianteTransformer->transformMany(
                    $this->estudianteService->listarPorPromocion($idPromocion)
                ),
            ]);
    }

    /**
     * POST /api/estudiantes
     *
     * Body: {
     *   id_promocion,
     *   estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? },
     *   apoderado:  { nombres, apellidos?, telefono, tipo_relacion,
     *                 tipo_documento, numero_documento, correo? }
     * }
     *
     * @return ResponseInterface 201 con id_estudiante | 404 si no existe la promoción | 422 si falla validación.
     */
    public function create()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $rules = [
            'id_promocion'            => 'required|integer|is_natural_no_zero',
            'estudiante.nombres'      => 'required|max_length[100]',
            'apoderado.nombres'       => 'required|max_length[100]',
            'apoderado.telefono'      => 'required|exact_length[9]|regex_match[/^\d{9}$/]',
            'apoderado.tipo_relacion' => 'required|in_list[padre,madre,hermano,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = $this->estudianteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante registrado', 'id_estudiante' => $id]);
    }

    /**
     * PUT /api/estudiantes/{id}
     *
     * Body: { nombres?, apellidos?, fecha_nacimiento?, color_fav?, profesion_futura? }
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        try {
            $this->estudianteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante actualizado']);
    }

    /**
     * DELETE /api/estudiantes/{id}
     *
     * La asistencia del estudiante se elimina en cascada por FK.
     *
     * @param  mixed $id ID del estudiante.
     * @return ResponseInterface 200 | 404.
     */
    public function delete($id)
    {
        try {
            $this->estudianteService->eliminar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante eliminado']);
    }

}
