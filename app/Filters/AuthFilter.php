<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — verifica que el usuario tenga sesión activa.
 * Rutas API devuelven 401 JSON; rutas web redirigen a /login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('logged_in')) {
            return;
        }

        // Detectar si la petición es a la API (espera JSON).
        // getPath() puede incluir el segmento 'index.php' en dev, por eso
        // se busca el segmento 'api' en cualquier posición del path.
        $path = ltrim($request->getUri()->getPath(), '/');
        if (str_starts_with($path, 'api/') || str_starts_with($path, 'index.php/api/')) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'No autorizado. Inicia sesión.']);
        }

        return redirect()->to(base_url('/login'))
            ->with('warning', 'Debes iniciar sesión para continuar.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
