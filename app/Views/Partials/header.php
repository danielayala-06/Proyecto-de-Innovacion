<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ronceros Fotografía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<!-- HEADER -->
<header id="main-header">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-link p-1 text-white" id="toggleSidebar">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="brand">
            <i class="bi bi-camera"></i>
            <span>Ronceros</span>
        </div>
    </div>
    <div class="avatar-btn">QR</div>
</header>

<!-- SIDEBAR -->
<aside id="sidebar">
    <nav>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="<?=base_url('/')?>" class="nav-link active" id="url_home">
                    <i class="bi bi-house"></i> Inicio
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/cotizaciones')?>" class="nav-link" id="url_cotizacion">
                    <i class="bi bi-file-earmark-text"></i> Cotizacion
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/contratos')?>" class="nav-link" id="url_contrato">
                    <i class="bi bi-file-earmark-text"></i> Contratos
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/calendario')?>" class="nav-link" id="url_calendario">
                    <i class="bi bi-calendar3"></i> Calendario
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/paquetes')?>" class="nav-link" id="url_paquete">
                    <i class="bi bi-box-seam"></i> Paquetes
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/clientes')?>" class="nav-link" id="url_cliente">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main id="main-content">
