<?php

/**
 * @file    BaseApiController.php
 * @package App\Controllers
 *
 * Controlador base para todos los controladores de la capa API REST.
 * Extiende BaseController y añade el método centralizado de conversión
 * de excepciones de servicio a respuestas JSON con código HTTP correcto.
 *
 * Todos los controladores bajo App\Controllers\Api\ deben heredar de esta clase.
 */

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador abstracto base de la capa API.
 *
 * Centraliza la conversión de RuntimeException → respuesta JSON para que
 * cada controlador API no tenga que reimplementar el mismo bloque de código.
 *
 * Convención de códigos en RuntimeException (lanzadas por los Services):
 *  - 404 → recurso no encontrado.
 *  - 409 → conflicto de estado (ej. cotización no PENDIENTE).
 *  - 422 → error de validación; el mensaje es JSON con el array de errores del modelo.
 *  - 500 → error inesperado del servidor.
 */
abstract class BaseApiController extends BaseController
{
    /**
     * Convierte una RuntimeException lanzada por un Service en una respuesta
     * JSON con el código HTTP correspondiente.
     *
     * - Código 422: decodifica el mensaje como JSON de errores del modelo.
     * - Cualquier otro código: usa el mensaje de texto directamente.
     * - Código 0 o inválido: se trata como 500.
     *
     * @param  \RuntimeException $e Excepción lanzada por el servicio.
     * @return ResponseInterface
     */
    protected function serviceError(\RuntimeException $e): ResponseInterface
    {
        $code   = (int) $e->getCode() ?: 500;
        $errors = ($code === 422) ? json_decode($e->getMessage(), true) : null;

        $httpStatus = match ($code) {
            404     => ResponseInterface::HTTP_NOT_FOUND,
            409     => ResponseInterface::HTTP_CONFLICT,
            422     => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
            default => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
        };

        return $this->response
            ->setStatusCode($httpStatus)
            ->setJSON(is_array($errors)
                ? ['status' => 'error', 'errors'  => $errors]
                : ['status' => 'error', 'message' => $e->getMessage()]
            );
    }
}
