<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ronceros Fotografía</title>
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
      <a href="index.html" class="brand">
        <i class="bi bi-camera"></i>
        <span>Ronceros</span>
      </a>
    </div>
    <div class="avatar-btn">JR</div>
  </header>

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <nav>
      <!-- NAVIGATION -->
      <ul class="nav flex-column gap-1">
        <li>
          <a href="<?= base_url('/index') ?>"        class="nav-link">
            <i class="bi bi-house"></i> Inicio
          </a>
        </li>
        <li>
          <a href="<?= base_url('/cotizaciones') ?>" class="nav-link active">
            <i class="bi bi-list-ul"></i> Cotizaciones
          </a>
        </li>
        <li>
          <a href="<?= base_url('/contratos') ?>"    class="nav-link">
            <i class="bi bi-file-earmark-text"></i> Contratos
          </a>
        </li>
        <li>
          <a href="<?= base_url('/calendario') ?>"   class="nav-link">
            <i class="bi bi-calendar3"></i> Calendario
          </a>
        </li>
        <li>
          <a href="<?= base_url('/paquetes') ?>"     class="nav-link">
            <i class="bi bi-box-seam"></i> Paquetes
          </a>
        </li>
        <li>
          <a href="<?= base_url('/clientes') ?>"     class="nav-link">
            <i class="bi bi-people"></i> Clientes
          </a>
        </li>
        <li>
          <a href="<?= base_url('/promociones') ?>"  class="nav-link">
            <i class="bi bi-tag"></i> Promociones
          </a>
        </li>
      </ul>
      <!-- FIN ELEMENTOS NAVIGATION -->

    </nav>
  </aside>

<!-- MAIN CONTENT -->
  <main id="main-content">
