<?= $header ?>
<main class="main-content" id="main-content">
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
<?= $footer ?>
