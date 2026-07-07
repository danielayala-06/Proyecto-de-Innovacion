<?php

namespace Config;

use App\Filters\AuthFilter;
use App\Filters\TunnelFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * Aliases de filtros disponibles.
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        // ── Autenticación ──────────────────────────────────────────────────────
        'auth'          => AuthFilter::class,
        // ── Acceso externo vía Cloudflare Tunnel ───────────────────────────────
        'tunnel'        => TunnelFilter::class,
    ];

    /**
     * Filtros requeridos (siempre se ejecutan).
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Forzar HTTPS en producción
            'pagecache',  // Caché de páginas
        ],
        'after' => [
            'pagecache',   // Caché de páginas
            'performance', // Métricas de rendimiento
            'toolbar',     // Barra de depuración (solo en development)
        ],
    ];

    /**
     * Filtros globales (aplican a TODAS las rutas).
     *
     * CSRF: protege formularios contra ataques Cross-Site Request Forgery.
     * Se excluyen las rutas de API porque usan JSON + ses iones propias
     * y no necesitan el token en el cuerpo del request.
     *
     * @var array{
     *     before: array<string, array{except: list<string>|string}>|list<string>,
     *     after: array<string, array{except: list<string>|string}>|list<string>
     * }
     */
    public array $globals = [
        'before' => [
            'tunnel', // bloquea tráfico externo (túnel) fuera de /formulario/*
            'csrf' => ['except' => ['api/*', 'api', 'formulario/guardar', 'formulario/grupo/guardar', 'formulario/stock/*']],
            // 'honeypot',
            // 'invalidchars',
        ],
        'after' => [
            // 'secureheaders',
        ],
    ];

    /**
     * Filtros por método HTTP.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * Filtros por patrón de URI.
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
