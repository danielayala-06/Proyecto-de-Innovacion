<?php

namespace App\Filters;

use App\Services\Auth\RememberTokenService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — verifica que el usuario tenga sesión activa.
 *
 * Orden de comprobación:
 *  1. Sesión activa → pasar.
 *  2. Cookie "remember_token" válida → iniciar sesión automáticamente y pasar.
 *  3. Sin credenciales → 401 JSON para API, redirección a /login para web.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('logged_in')) {
            return;
        }

        // Auto-login por cookie "Recuérdame"
        /** @var IncomingRequest $request */
        $cookieToken = $request->getCookie('remember_token');
        if ($cookieToken) {
            $service = new RememberTokenService();
            $usuario = $service->validarYObtenerUsuario($cookieToken);

            if ($usuario) {
                RememberTokenService::iniciarSesion($usuario);
                return; // sesión establecida, continuar con la petición
            }

            // Token inválido o expirado: la cookie se limpiará en el próximo logout;
            // no la borramos aquí porque no podemos emitir Set-Cookie desde un filtro
            // de forma fiable en todas las rutas (API vs web).
        }

        // Sin sesión ni token válido → rechazar
        $path = ltrim($request->getUri()->getPath(), '/');
        if (str_starts_with($path, 'api/') || str_starts_with($path, 'index.php/api/')) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        return redirect()->to(base_url('/login'))
            ->with('warning', 'Debes iniciar sesión para continuar.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
