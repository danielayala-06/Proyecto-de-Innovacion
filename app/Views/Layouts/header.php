<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ronceros Fotografía</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('images/icon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Aplica el tema guardado antes de que la página renderice (evita parpadeo) -->
    <script>
        (function () {
            var t = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/forms.css') ?>">
    <script>const SERVER_TODAY = "<?= (new DateTime('now', new DateTimeZone('America/Lima')))->format('Y-m-d') ?>";</script>
</head>
<body>

<!-- HEADER -->
<header id="main-header">
    <div class="d-flex align-items-center gap-3">
        <button class="header-icon-btn" id="toggleSidebar" aria-label="Alternar menú lateral" title="Alternar menú lateral">
            <i class="bi bi-list fs-4"></i>
        </button>
        <a href="<?= base_url('/')?>" class="brand">
            <i class="bi bi-aperture"></i>
            <span>Ronceros</span>
        </a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="header-icon-btn" id="btn-theme" aria-label="Cambiar tema" title="Cambiar tema">
            <i id="theme-icon" class="bi bi-moon-fill"></i>
        </button>
        <?php
            $initials = 'U';
            if (session()->get('nombres')) {
                $parts    = array_filter(explode(' ', trim(session()->get('nombres') . ' ' . session()->get('apellidos'))));
                $initials = '';
                foreach (array_slice($parts, 0, 2) as $p) {
                    $initials .= mb_strtoupper(mb_substr($p, 0, 1));
                }
            }
        ?>
        <div class="avatar-btn" title="<?= esc(session()->get('nombres') . ' ' . session()->get('apellidos')) ?> — <?= esc(session()->get('rol') ?? '') ?>">
            <?= esc($initials) ?>
        </div>
    </div>
</header>

<!-- SIDEBAR BACKDROP (mobile) -->
<div id="sidebar-backdrop"></div>

<!-- SIDEBAR -->
<?php
// Detecta el primer segmento de la URI para marcar el ítem activo
$segmento = service('request')->getUri()->getSegment(1) ?: 'inicio';
function navActivo(string $ruta, string $segmento): string {
    return $segmento === $ruta ? 'nav-link active' : 'nav-link';
}
?>
<aside id="sidebar">
    <nav style="display:flex;flex-direction:column;height:100%;">
        <ul class="nav flex-column gap-1" style="flex:1;">
            <li>
                <a href="<?= base_url('') ?>" class="<?= navActivo('inicio', $segmento) ?>">
                    <i class="bi bi-house"></i> <span class="nav-label">Inicio</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('/cotizaciones') ?>" class="<?= navActivo('cotizaciones', $segmento) ?>">
                    <i class="bi bi-list-ul"></i> <span class="nav-label">Cotizaciones</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('/contratos') ?>" class="<?= navActivo('contratos', $segmento) ?>">
                    <i class="bi bi-file-earmark-text"></i> <span class="nav-label">Contratos</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('/clientes') ?>" class="<?= navActivo('clientes', $segmento) ?>">
                    <i class="bi bi-building"></i> <span class="nav-label">Colegios</span>
                </a>
            </li>
            <!-- <li>
                <a href="<?= base_url('/promociones') ?>" class="<?= navActivo('promociones', $segmento) ?>">
                    <i class="bi bi-mortarboard"></i> <span class="nav-label">Promociones</span>
                </a>
            </li> -->
            <li>
                <a href="<?= base_url('/catalogo') ?>" class="<?= navActivo('catalogo', $segmento) ?>">
                    <i class="bi bi-book-half"></i> <span class="nav-label">Catálogo</span>
                </a>
            </li>

            <li>
                <a href="<?= base_url('/calendario') ?>" class="<?= navActivo('calendario', $segmento) ?>">
                    <i class="bi bi-calendar3"></i> <span class="nav-label">Calendario</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="<?= base_url('/logout') ?>" class="sidebar-logout" aria-label="Cerrar sesión" title="Cerrar sesión">
                <i class="bi bi-box-arrow-left"></i>
                <span class="nav-label">Cerrar sesión</span>
            </a>
        </div>
    </nav>
</aside>
