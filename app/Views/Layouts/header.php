<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ronceros Fotografía</title>
  <!-- Aplica el tema guardado antes de que la página renderice (evita parpadeo) -->
  <script>
    (function () {
      var t = localStorage.getItem('theme') ||
              (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
</head>
<body>

<!-- HEADER -->
  <header id="main-header">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-link p-1 text-white" id="toggleSidebar">
        <i class="bi bi-list fs-4"></i>
      </button>
      <a href="<?= base_url('/')?>" class="brand">
        <i class="bi bi-camera"></i>
        <span>Ronceros</span>
      </a>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-link p-1 text-white" id="btn-theme" title="Cambiar tema" style="font-size:1.2rem;line-height:1;">
        <i id="theme-icon" class="bi bi-moon-fill"></i>
      </button>
      <div class="avatar-btn">JR</div>
    </div>
  </header>

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
            <i class="bi bi-house"></i> Inicio
          </a>
        </li>
        <li>
          <a href="<?= base_url('/cotizaciones') ?>" class="<?= navActivo('cotizaciones', $segmento) ?>">
            <i class="bi bi-list-ul"></i> Cotizaciones
          </a>
        </li>
        <li>
          <a href="<?= base_url('/contratos') ?>" class="<?= navActivo('contratos', $segmento) ?>">
            <i class="bi bi-file-earmark-text"></i> Contratos
          </a>
        </li>
        <li>
          <a href="<?= base_url('/clientes') ?>" class="<?= navActivo('clientes', $segmento) ?>">
            <i class="bi bi-people"></i> Clientes
          </a>
        </li>
        <li>
          <a href="<?= base_url('/paquetes') ?>" class="<?= navActivo('paquetes', $segmento) ?>">
            <i class="bi bi-box-seam"></i> Paquetes
          </a>
        </li>
        <li>
          <a href="<?= base_url('/promociones') ?>" class="disabled <?= navActivo('promociones', $segmento) ?>">
            <i class="bi bi-tag"></i> Promociones
          </a>
        </li>
        <li>
          <a href="<?= base_url('/calendario') ?>" class="<?= navActivo('calendario', $segmento) ?>">
            <i class="bi bi-calendar3"></i> Calendario
          </a>
        </li>
      </ul>

      <div style="padding:0.75rem 0.5rem;border-top:1px solid rgba(255,255,255,0.07);">
        <a href="<?= base_url('/logout') ?>"
           style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:8px;
                  color:#C8BCA8;font-size:0.85rem;text-decoration:none;
                  transition:background 0.15s,color 0.15s;"
           onmouseover="this.style.background='#242018';this.style.color='#e07070';"
           onmouseout="this.style.background='transparent';this.style.color='#C8BCA8';">
          <i class="bi bi-box-arrow-left" style="font-size:1rem;"></i>
          Cerrar sesión
        </a>
      </div>
    </nav>
  </aside>

