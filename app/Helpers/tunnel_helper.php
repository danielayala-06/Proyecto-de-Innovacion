<?php

/**
 * Devuelve una URL pública para compartir con usuarios externos.
 *
 * Cuando el túnel de Cloudflare está activo, tunnel.sh escribe la URL
 * pública (ej. https://abc.trycloudflare.com) en writable/tunnel_url.txt.
 * Esta función la usa como base en lugar de localhost.
 *
 * Si el archivo no existe (modo local normal), cae a base_url().
 */
if (!function_exists('public_url')) {
    function public_url(string $path = ''): string
    {
        static $base = null;

        if ($base === null) {
            $file = WRITEPATH . 'tunnel_url.txt';
            if (is_file($file)) {
                $content = trim((string) file_get_contents($file));
                $base = $content !== '' ? rtrim($content, '/') : '';
            } else {
                $base = '';
            }
        }

        if ($base !== '') {
            return $base . '/' . ltrim($path, '/');
        }

        return base_url($path);
    }
}
