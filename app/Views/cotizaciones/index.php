<<<<<<< HEAD
<?= $header ?>
<h1>Si funcionoooo</h1>
<?= $footer ?>
=======
<?= $header?>

<!-- SECCIÓN COTIZACIONES -->
<section class="section active" id="section-cotizaciones">
    <div class="d-flex justify-content-center align-items-center mb-4">
        <div>
            <h1 class="section-title mb-1">Cotizaciones</h1>
            <p class="section-subtitle text-muted">Gestiona tus presupuestos de fotografía</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2" id="btn-nueva-cotizacion">
            <i class="fa-solid fa-plus"></i>
            Nueva cotización
        </button>
    </div>

    <!-- Filtros -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <button class="filtro-btn btn btn-outline-secondary active" data-filtro="todas">Todas</button>
        <button class="filtro-btn btn btn-outline-success" data-filtro="vigente">
            <span class="dot dot-vigente"></span> Vigentes
        </button>
        <button class="filtro-btn btn btn-outline-danger" data-filtro="caducada">
            <span class="dot dot-caducada"></span> Caducadas
        </button>
    </div>



    <!-- Lista de Cotizaciones -->
    <div class="lista-cotizaciones" id="lista-cotizaciones">
        <?php foreach ($cotizaciones as $cotizacion): ?>
            <!-- Las tarjetas se agregarán aquí con JavaScript o manualmente -->
            <div class="row g-4">
            <!-- Ejemplo de tarjeta con Bootstrap -->
            <div class="col-12">
                <div class="card card-cotizacion shadow-sm border-0">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <small class="text-muted">DNI</small><br>
                                    <strong><?=$cotizacion['dni']?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <small class="text-muted">Cliente</small><br>
                                    <strong><?=$cotizacion['cliente']?></strong>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Paquete</small><br>
                                    <strong><?=$cotizacion['paquete']?></strong>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted">Creado</small><br>
                                    <strong><?=$cotizacion['created_at']?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="text-end" style="min-width: 170px;">
                            <h4 class="text-success fw-bold mb-1"><?=$cotizacion['monto_acordado']?></h4>
                            <span class="badge estado-vigente px-3 py-2"><?=$cotizacion['estado']?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?= $footer?>
>>>>>>> 0fafb9b635853e892959eca9cf09354dd25a735e
