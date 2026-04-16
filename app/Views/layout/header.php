<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ronceros Fotografia - Cotizaciones</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Tu CSS personalizado -->
    <link rel="stylesheet" href="<?= base_url('/styles/style.css'); ?>">

</head>
<body>

    <header>
        <div class="left">
            <div class="menu-container">
                <div class="menu" id="menu">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <div class="brand">
                <i class="fa-solid fa-camera logo"></i>
                <span class="name">Ronceros</span>
            </div>
        </div>
        <div class="right">
            <a href="#">
                <i class="fa-solid fa-user user-icon"></i>
            </a>
        </div>
    </header>

    <div class="sidebar" id="sidebar">
        <nav>
            <ul>
                <li><a href="#" class="nav-link" data-section="inicio"><i class="fa-solid fa-house nav-icon"></i><span>Inicio</span></a></li>
                <li><a href="#" class="nav-link" data-section="calendario"><i class="fa-solid fa-calendar nav-icon"></i><span>Calendario</span></a></li>
                <li><a href="#" class="nav-link" data-section="contratos"><i class="fa-solid fa-file-contract nav-icon"></i><span>Contratos</span></a></li>
                <li><a href="#" class="nav-link" data-section="productos"><i class="fa-solid fa-camera nav-icon"></i><span>Productos</span></a></li>
                <li><a href="#" class="nav-link selected" data-section="cotizaciones"><i class="fa-solid fa-file-invoice-dollar nav-icon"></i><span>Cotizaciones</span></a></li>
                <li><a href="#" class="nav-link" data-section="clientes"><i class="fa-solid fa-users nav-icon"></i><span>Clientes</span></a></li>
            </ul>
        </nav>
    </div>