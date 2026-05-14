<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Estudiantes\EstudianteService;
use App\Transformers\EstudianteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * EstudiantesApi
 * Base URL: /api/estudiantes
 *
 * GET    /api/estudiantes?id_promocion=X  → listar por promoción
 * POST   /api/estudiantes                 → crear (incluye apoderado + persona)
 * PUT    /api/estudiantes/{id}            → actualizar datos del estudiante
 * DELETE /api/estudiantes/{id}            → eliminar
 */
class EstudiantesApi extends BaseController
{
    protected EstudianteService     $estudianteService;
    protected EstudianteTransformer $estudianteTransformer;

    public function __construct()
    {
        $this->estudianteService     = new EstudianteService();
        $this->estudianteTransformer = new EstudianteTransformer();
    }

    // GET /api/estudiantes?id_promocion=X
    public function index()
    {
        $idPromocion = (int) ($this->request->getGet('id_promocion') ?? 0);
        if (!$idPromocion) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_promocion es requerido']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status' => 'success',
            'data'   => $this->estudianteTransformer->transformMany(
                $this->estudianteService->listarPorPromocion($idPromocion)
            ),
        ]);
    }

    // POST /api/estudiantes
    // Body: {
    //   id_promocion,
    //   estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? },
    //   apoderado:  { nombres, apellidos, telefono, tipo_relacion,
    //                 tipo_documento, numero_documento, correo? }
    // }
    public function create()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $rules = [
            'id_promocion'                   => 'required|integer|is_natural_no_zero',
            'estudiante.nombres'             => 'required|max_length[30]',
            'estudiante.apellidos'           => 'required|max_length[30]',
            'apoderado.nombres'              => 'required|max_length[100]',
            'apoderado.telefono'             => 'required|exact_length[9]',
            'apoderado.tipo_documento'       => 'required|in_list[DNI,CE,PASAPORTE]',
            'apoderado.numero_documento'     => 'required|min_length[6]|max_length[20]',
            'apoderado.tipo_relacion'        => 'required|in_list[padre,madre,hermano,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = $this->estudianteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante registrado', 'id_estudiante' => $id]);
    }

    // PUT /api/estudiantes/{id}
    // Body: { nombres?, apellidos?, fecha_nacimiento?, color_fav?, profesion_futura? }
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];
        try {
            $this->estudianteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante actualizado']);
    }

    // DELETE /api/estudiantes/{id}
    public function delete($id)
    {
        try {
            $this->estudianteService->eliminar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante eliminado']);
    }

    private function _serviceError(\RuntimeException $e): ResponseInterface
    {
        $code   = (int) $e->getCode() ?: 500;
        $errors = ($code === 422) ? json_decode($e->getMessage(), true) : null;

        $httpStatus = match ($code) {
            404     => ResponseInterface::HTTP_NOT_FOUND,
            409     => ResponseInterface::HTTP_CONFLICT,
            422     => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
            default => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
        };

        return $this->response->setStatusCode($httpStatus)->setJSON(
            is_array($errors)
                ? ['status' => 'error', 'errors'  => $errors]
                : ['status' => 'error', 'message' => $e->getMessage()]
        );
    }
}
