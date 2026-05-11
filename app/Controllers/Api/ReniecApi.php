<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ReniecApi — proxy seguro hacia Decolecta
 * GET /api/reniec/dni?numero=XXXXXXXX
 */
class ReniecApi extends BaseController
{
    public function dni()
    {
        $numero = $this->request->getGet('numero');

        if (!$numero || !preg_match('/^\d{8}$/', $numero)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Número de DNI inválido.']);
        }

        $key    = env('DECOLECTA.KEY');
        $client = \Config\Services::curlrequest();

        try {
            $resp = $client->request('GET', 'https://api.decolecta.com/v1/reniec/dni', [
                'headers'     => [
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ],
                'query'       => ['numero' => $numero],
                'http_errors' => false,
                'timeout'     => 8,
            ]);

            $code = $resp->getStatusCode();
            $body = json_decode($resp->getBody(), true);

            if ($code !== 200 || empty($body)) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON(['status' => 'error', 'message' => 'DNI no encontrado en RENIEC.']);
            }

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'status' => 'success',
                    'data'   => [
                        'nombres'   => $body['first_name']       ?? '',
                        'apellidos' => trim(
                            ($body['first_last_name']  ?? '') . ' ' .
                            ($body['second_last_name'] ?? '')
                        ),
                        'numero_documento' => $body['document_number'] ?? $numero,
                    ],
                ]);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_SERVICE_UNAVAILABLE)
                ->setJSON(['status' => 'error', 'message' => 'No se pudo conectar con RENIEC.']);
        }
    }
}
