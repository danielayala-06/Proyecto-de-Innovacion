<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class DocsApi extends BaseCommand
{
    protected $group       = 'Docs';
    protected $name        = 'docs:api';
    protected $description = 'Genera documentación HTML de los endpoints REST';
    protected $usage       = 'docs:api';

    private array $methodColors = [
        'GET'    => ['label' => 'GET',    'class' => 'get'],
        'POST'   => ['label' => 'POST',   'class' => 'post'],
        'PUT'    => ['label' => 'PUT',    'class' => 'put'],
        'PATCH'  => ['label' => 'PATCH',  'class' => 'patch'],
        'DELETE' => ['label' => 'DELETE', 'class' => 'delete'],
    ];

    public function run(array $params): void
    {
        CLI::write('Leyendo rutas...', 'cyan');

        $routes  = $this->collectApiRoutes();
        $grouped = $this->groupByResource($routes);
        $html    = $this->renderHtml($grouped, count($routes));

        $outDir  = ROOTPATH . 'build/docs/';
        $outFile = $outDir . 'api.html';

        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        file_put_contents($outFile, $html);

        CLI::write('');
        CLI::write('Documentación generada:', 'green');
        CLI::write('  ' . $outFile);
        CLI::write('  ' . count($routes) . ' endpoints documentados en ' . count($grouped) . ' recursos.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function collectApiRoutes(): array
    {
        $collection = Services::routes(true);
        $result     = [];

        foreach (array_keys($this->methodColors) as $method) {
            foreach ($collection->getRoutes($method) as $pattern => $handler) {
                $clean = $this->cleanPattern($pattern);
                if (!str_starts_with($clean, '/api/')) {
                    continue;
                }

                $result[] = [
                    'method'     => $method,
                    'path'       => $clean,
                    'controller' => $this->formatHandler($handler),
                    'resource'   => $this->extractResource($clean),
                ];
            }
        }

        usort($result, fn($a, $b) =>
            $a['resource'] <=> $b['resource'] ?: $a['path'] <=> $b['path']
        );

        return $result;
    }

    private function cleanPattern(string $pattern): string
    {
        $pattern = preg_replace('#\(\[0\-9\]\+\)#', '{id}', $pattern);
        $pattern = preg_replace('#\(\[\^/\]\+\)#', '{segment}', $pattern);
        $pattern = preg_replace('#\(\.\+\)#', '{any}', $pattern);
        $pattern = '/' . ltrim($pattern, '/');
        $pattern = str_replace('index\.php/', '', $pattern);

        return $pattern;
    }

    private function formatHandler(mixed $handler): string
    {
        if (!is_string($handler)) {
            return 'Closure';
        }
        // Remove namespace prefix, keep ControllerName::method
        return preg_replace('#^\\\\?App\\\\Controllers\\\\(Api\\\\)?#', '', $handler);
    }

    private function extractResource(string $path): string
    {
        // /api/cotizaciones/... → Cotizaciones
        $segments = explode('/', trim($path, '/'));
        return isset($segments[1]) ? ucfirst($segments[1]) : 'General';
    }

    private function groupByResource(array $routes): array
    {
        $grouped = [];
        foreach ($routes as $route) {
            $grouped[$route['resource']][] = $route;
        }
        ksort($grouped);
        return $grouped;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function renderHtml(array $grouped, int $total): string
    {
        $generated = date('d/m/Y H:i');
        $rows      = '';

        foreach ($grouped as $resource => $routes) {
            $rows .= "<tr class=\"group-row\"><td colspan=\"3\">{$resource}</td></tr>\n";
            foreach ($routes as $r) {
                $m    = $r['method'];
                $cls  = strtolower($m);
                $path = htmlspecialchars($r['path']);
                $ctrl = htmlspecialchars($r['controller']);
                $rows .= "<tr>
                    <td><span class=\"badge {$cls}\">{$m}</span></td>
                    <td class=\"path\">{$path}</td>
                    <td class=\"ctrl\">{$ctrl}</td>
                </tr>\n";
            }
        }

        $navItems = '';
        foreach (array_keys($grouped) as $resource) {
            $anchor    = strtolower($resource);
            $navItems .= "<a href=\"#{$anchor}\" class=\"nav-link\">{$resource}</a>\n";
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>API Docs — Ronceros Fotografía</title>
        <style>
        :root {
            --bg: #f4f5fb; --surface: #fff; --border: #dde1ee;
            --text: #1a1d2e; --muted: #5c6180; --code: #1c1f2e;
            --get: #1a7f4e; --get-bg: #eaf7f1; --get-border: #b0deca;
            --post: #1d5fb8; --post-bg: #eaf1fd; --post-border: #b8cfee;
            --put: #b07010; --put-bg: #fdf5e8; --put-border: #f0d5a0;
            --patch: #6b40c0; --patch-bg: #f3edfb; --patch-border: #d0b8f0;
            --delete: #c03030; --delete-bg: #fdf0f0; --delete-border: #f5c5c5;
            --shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg:#0f1117; --surface:#1c1f2e; --border:#2e3248;
                --text:#dde1f4; --muted:#8085a8; --code:#11131d;
                --get:#3daf7f; --get-bg:#0d1e17; --get-border:#1a4030;
                --post:#4a8ef0; --post-bg:#0d1526; --post-border:#1e3460;
                --put:#d4891a; --put-bg:#1e1708; --put-border:#4a360c;
                --patch:#a070e0; --patch-bg:#1a1028; --patch-border:#3a2060;
                --delete:#e05a5a; --delete-bg:#1f1217; --delete-border:#4a2020;
            }
        }
        :root[data-theme="light"]{--bg:#f4f5fb;--surface:#fff;--border:#dde1ee;--text:#1a1d2e;--muted:#5c6180;}
        :root[data-theme="dark"]{--bg:#0f1117;--surface:#1c1f2e;--border:#2e3248;--text:#dde1f4;--muted:#8085a8;}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{font-size:14px;scroll-behavior:smooth}
        body{background:var(--bg);color:var(--text);font-family:ui-sans-serif,system-ui,sans-serif;display:grid;grid-template-columns:220px 1fr;min-height:100vh}

        /* sidebar */
        .sidebar{background:var(--surface);border-right:1px solid var(--border);padding:1.5rem 1rem;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;gap:1rem}
        .sidebar-title{font-size:.7rem;font-family:ui-monospace,monospace;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
        .nav-link{display:block;padding:.35rem .6rem;border-radius:5px;color:var(--text);text-decoration:none;font-size:.82rem;transition:background .15s}
        .nav-link:hover{background:var(--border)}

        /* main */
        main{padding:2rem 2.5rem;max-width:900px}
        .page-header{margin-bottom:2rem}
        .page-title{font-size:1.4rem;font-weight:700}
        .page-meta{font-size:.78rem;color:var(--muted);margin-top:.3rem;font-family:ui-monospace,monospace}

        /* search */
        .search-wrap{margin-bottom:1.5rem}
        #search{width:100%;padding:.55rem .9rem;border:1px solid var(--border);border-radius:7px;background:var(--surface);color:var(--text);font-size:.85rem;outline:none}
        #search:focus{border-color:var(--post)}

        /* table */
        .resource-section{margin-bottom:2rem}
        .resource-title{font-size:.68rem;font-family:ui-monospace,monospace;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.6rem;display:flex;align-items:center;gap:.6rem}
        .resource-title::after{content:'';flex:1;height:1px;background:var(--border)}
        .table-wrap{overflow-x:auto;border-radius:8px;border:1px solid var(--border);box-shadow:var(--shadow)}
        table{width:100%;border-collapse:collapse}
        th{font-family:ui-monospace,monospace;font-size:.65rem;letter-spacing:.07em;text-transform:uppercase;color:var(--muted);padding:.55rem .9rem;text-align:left;background:var(--surface);border-bottom:1px solid var(--border)}
        td{padding:.6rem .9rem;border-bottom:1px solid var(--border);vertical-align:middle;background:var(--surface)}
        tr:last-child td{border-bottom:none}
        tr:hover td{filter:brightness(.97)}

        /* badges */
        .badge{display:inline-block;font-family:ui-monospace,monospace;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:4px;border:1px solid;letter-spacing:.06em;white-space:nowrap}
        .get   {color:var(--get);   background:var(--get-bg);   border-color:var(--get-border)}
        .post  {color:var(--post);  background:var(--post-bg);  border-color:var(--post-border)}
        .put   {color:var(--put);   background:var(--put-bg);   border-color:var(--put-border)}
        .patch {color:var(--patch); background:var(--patch-bg); border-color:var(--patch-border)}
        .delete{color:var(--delete);background:var(--delete-bg);border-color:var(--delete-border)}

        .path{font-family:ui-monospace,monospace;font-size:.82rem}
        .ctrl{font-family:ui-monospace,monospace;font-size:.78rem;color:var(--muted)}

        .auth-tag{display:inline-block;font-size:.6rem;font-family:ui-monospace,monospace;font-weight:600;padding:.1rem .4rem;border-radius:3px;background:var(--post-bg);color:var(--post);border:1px solid var(--post-border);letter-spacing:.05em;margin-left:.4rem}

        .empty{text-align:center;padding:2rem;color:var(--muted);font-size:.85rem}

        /* theme toggle */
        .theme-btn{margin-top:auto;padding:.4rem .7rem;border:1px solid var(--border);border-radius:6px;background:transparent;color:var(--muted);cursor:pointer;font-size:.75rem;text-align:center}
        .theme-btn:hover{background:var(--border)}

        @media(max-width:640px){
            body{grid-template-columns:1fr}
            .sidebar{display:none}
            main{padding:1rem}
        }
        </style>
        </head>
        <body>

        <aside class="sidebar">
            <div class="sidebar-title">Recursos</div>
            {$navItems}
            <button class="theme-btn" onclick="toggleTheme()">Cambiar tema</button>
        </aside>

        <main>
            <div class="page-header">
                <div class="page-title">API Docs — Ronceros Fotografía</div>
                <div class="page-meta">
                    {$total} endpoints &nbsp;·&nbsp; Generado el {$generated} &nbsp;·&nbsp;
                    <span class="auth-tag">🔒 AUTH</span> requerido en todos los endpoints
                </div>
            </div>

            <div class="search-wrap">
                <input type="text" id="search" placeholder="Filtrar por ruta o método..." oninput="filterRoutes(this.value)">
            </div>

            <div id="content">
        HTML . $this->renderGroups($grouped) . <<<HTML
            </div>
            <div id="empty" class="empty" style="display:none">Sin resultados para esa búsqueda.</div>
        </main>

        <script>
        function toggleTheme() {
            const r = document.documentElement;
            const current = r.getAttribute('data-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            r.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
        }

        function filterRoutes(query) {
            const q = query.toLowerCase().trim();
            let anyVisible = false;
            document.querySelectorAll('.resource-section').forEach(section => {
                let sectionVisible = false;
                section.querySelectorAll('tr[data-row]').forEach(row => {
                    const text = row.getAttribute('data-row').toLowerCase();
                    const match = !q || text.includes(q);
                    row.style.display = match ? '' : 'none';
                    if (match) sectionVisible = true;
                });
                section.style.display = sectionVisible ? '' : 'none';
                if (sectionVisible) anyVisible = true;
            });
            document.getElementById('empty').style.display = anyVisible ? 'none' : '';
            document.getElementById('content').style.display = anyVisible ? '' : 'none';
        }
        </script>
        </body>
        </html>
        HTML;
    }

    private function renderGroups(array $grouped): string
    {
        $html = '';
        foreach ($grouped as $resource => $routes) {
            $anchor = htmlspecialchars(strtolower($resource));
            $res    = htmlspecialchars($resource);
            $html  .= "<div class=\"resource-section\" id=\"{$anchor}\">\n";
            $html  .= "<div class=\"resource-title\">{$res}</div>\n";
            $html  .= "<div class=\"table-wrap\"><table>\n";
            $html  .= "<thead><tr><th style=\"width:90px\">Método</th><th>Endpoint</th><th>Controlador</th></tr></thead>\n";
            $html  .= "<tbody>\n";
            foreach ($routes as $r) {
                $m    = $r['method'];
                $cls  = strtolower($m);
                $path = htmlspecialchars($r['path']);
                $ctrl = htmlspecialchars($r['controller']);
                $data = htmlspecialchars($m . ' ' . $r['path'] . ' ' . $r['controller']);
                $html .= "<tr data-row=\"{$data}\"><td><span class=\"badge {$cls}\">{$m}</span></td><td class=\"path\">{$path}</td><td class=\"ctrl\">{$ctrl}</td></tr>\n";
            }
            $html .= "</tbody></table></div>\n</div>\n";
        }
        return $html;
    }
}
