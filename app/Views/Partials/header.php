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
                <a href="<?=base_url('/')?>" class="nav-link active" onclick="setActive(this); return false;">
                    <i class="bi bi-house"></i> Inicio
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/cotizacion')?>" class="nav-link" onclick="setActive(this); return false;">
                    <i class="bi bi-file-earmark-text"></i> Cotizacion
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/contratos')?>" class="nav-link" onclick="setActive(this); return false;">
                    <i class="bi bi-file-earmark-text"></i> Contratos
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/calendario')?>" class="nav-link" onclick="setActive(this); return false;">
                    <i class="bi bi-calendar3"></i> Calendario
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/paquetes')?>" class="nav-link" onclick="setActive(this); return false;">
                    <i class="bi bi-box-seam"></i> Paquetes
                </a>
            </li>
            <li class="nav-item">
                <a href="<?=base_url('/clientes')?>" class="nav-link" onclick="setActive(this); return false;">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main id="main-content">
    <h3 class="fw-lighter">Inicio</h3>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Sesiones este mes</div>
                <div class="stat-value">12</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Contratos activos</div>
                <div class="stat-value">5</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Clientes totales</div>
                <div class="stat-value">38</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Ingresos (S/)</div>
                <div class="stat-value">4,200</div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="card-title">Próximas sesiones</div>
        <table class="table table-borderless">
            <thead>
            <tr>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Fecha</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Daniel Ayala</td>
                <td>Matrimonio</td>
                <td>16 abr</td>
            </tr>
            <tr>
                <td>Morgan Bondioli</td>
                <td>Retrato</td>
                <td>18 abr</td>
            </tr>
            <tr>
                <td>Diggy Félix</td>
                <td>Quinceañero</td>
                <td>22 abr</td>
            </tr>
            </tbody>
        </table>
    </div>
</main>
