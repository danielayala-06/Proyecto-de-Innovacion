<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TunnelFilter — restringe el acceso externo vía Cloudflare Tunnel.
 *
 * Cloudflare inyecta el header X-Tunnel-Token en cada request que pasa por
 * el túnel. Si ese header está presente, solo se permite acceder a rutas
 * bajo /formulario/. Todo lo demás devuelve 404.
 *
 * Requests desde la LAN no llevan el header → acceso completo sin cambios.
 *
 * Configuración:
 *   - TUNNEL_SECRET en .env (debe coincidir con el valor en cloudflared config.yml)
 *   - Registrar este filtro como 'tunnel' en app/Config/Filters.php y aplicarlo globalmente.
 */
class TunnelFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $secret = env('TUNNEL_SECRET', '');

        // Si no hay secreto configurado el filtro no hace nada (desarrollo local)
        if ($secret === '') {
            return;
        }

        $headerToken = $request->getHeaderLine('X-Tunnel-Token');

        // Request sin el header → viene de la LAN → acceso normal
        if ($headerToken === '') {
            return;
        }

        // Header presente pero valor incorrecto → posible spoofing → bloquear
        if (!hash_equals($secret, $headerToken)) {
            return response()->setStatusCode(404)->setBody('');
        }

        // Header correcto → viene del túnel → solo se permite /formulario/*
        $path = ltrim($request->getUri()->getPath(), '/');
        if (!str_starts_with($path, 'formulario/') && $path !== 'formulario') {
            return response()->setStatusCode(404)->setBody('');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
