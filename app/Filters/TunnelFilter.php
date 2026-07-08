<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TunnelFilter — restringe el acceso externo vía Cloudflare Tunnel.
 *
 * Cloudflare agrega el header CF-Ray a todo request que pasa por su red
 * (incluido cloudflared). Si ese header está presente, la petición viene
 * del exterior y solo se permite acceder a /formulario/*.
 *
 * Requests desde la LAN (localhost / red local) no llevan CF-Ray
 * → acceso completo al sistema de gestión sin restricciones.
 *
 * Configuración: solo registrar este filtro globalmente en Filters.php.
 * No requiere variables de entorno adicionales.
 */
class TunnelFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Sin CF-Ray → petición local o de LAN → acceso completo
        if ($request->getHeaderLine('CF-Ray') === '') {
            return;
        }

        // CF-Ray presente → tráfico externo vía Cloudflare → solo /formulario/*
        $path = ltrim($request->getUri()->getPath(), '/');
        $path = ltrim(str_replace('index.php/', '', $path), '/');

        if (!str_starts_with($path, 'formulario/') && $path !== 'formulario') {
            return response()->setStatusCode(404)->setBody('');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void {}
}
