<?= $header ?>
<link rel="stylesheet" href="<?= base_url('css/cotizaciones-ver.css') ?>">

<main id="main-content">
    <div class="container" style="max-width:1100px;">

        <button class="cv-back" onclick="history.length > 1 ? history.back() : (location.href = '<?= base_url('/cotizaciones') ?>')">
            <i class="bi bi-arrow-left"></i> Volver
        </button>

        <p class="page-title" style="margin-bottom:4px;">Detalle de cotización</p>
        <p id="cvSubtitle" class="fw-bold" style="font-size:.85rem;color:var(--text-muted);margin-bottom:22px;">Cargando…</p>

        <div class="cv-grid">

            <!-- ── COLUMNA IZQUIERDA ──────────────────────────────── -->
            <div class="d-flex flex-column gap-4">

                <!-- Cliente + colegio -->
                <div class="cv-card">
                    <div class="cv-card-title"><i class="bi bi-person-lines-fill"></i> Cliente</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div id="cvCliente">
                            <div class="placeholder-glow d-flex flex-column gap-2">
                                <span class="placeholder col-7 rounded"></span>
                                <span class="placeholder col-5 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ítems -->
                <div class="cv-card">
                    <div class="cv-card-title"><i class="bi bi-list-ul"></i> Ítems cotizados</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div id="cvItems">
                            <div class="placeholder-glow d-flex flex-column gap-2">
                                <span class="placeholder col-12 rounded"></span>
                                <span class="placeholder col-10 rounded"></span>
                                <span class="placeholder col-9 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promoción (si aplica) -->
                <div class="cv-card" id="cvPromCard" style="display:none;">
                    <div class="cv-card-title"><i class="bi bi-mortarboard"></i> Promoción escolar</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div class="cv-prom-card" id="cvProm"></div>
                    </div>
                </div>

            </div>

            <!-- ── COLUMNA DERECHA (sticky) ───────────────────────── -->
            <div class="cv-card d-flex flex-column gap-2" style="position:sticky;top:80px;">

                <!-- Estado -->
                <div class="cv-card-title" style="margin-bottom:8px;"><i class="bi bi-info-circle"></i> Estado</div>
                <div class="shadow p-3 bg-body-tertiary rounded d-flex flex-column gap-3">

                    <div>
                        <div id="cvEstado"></div>
                    </div>

                    <!-- Resumen económico -->
                    <div>
                        <div class="cv-section-title">Resumen</div>
                        <div class="cv-row"><span>Total cotización</span><strong id="cvTotal">—</strong></div>
                        <div class="cv-row"><span>Fecha</span><strong id="cvFecha">—</strong></div>
                        <div class="cv-row"><span>Registrado por</span><strong id="cvUsuario">—</strong></div>
                    </div>

                    <!-- Observaciones -->
                    <div id="cvObsWrap" style="display:none;">
                        <div class="cv-section-title">Observaciones</div>
                        <p id="cvObs" style="font-size:.8rem;color:var(--text-muted);margin:0;"></p>
                    </div>

                </div>

                <!-- Acciones (fuera del fondito para que resalten) -->
                <div id="cvAcciones" class="d-flex flex-column gap-2"></div>

            </div>

        </div>
    </div>
</main>

<script>const BASE_URL = "<?= base_url('') ?>";const COT_ID = <?= (int) $id_cotizacion ?>;</script>
<script type="module" src="<?= base_url('js/modules/cotizaciones/cotizacionVer.js') . '?v=' . filemtime(FCPATH . 'js/modules/cotizaciones/cotizacionVer.js') ?>"></script>

<?= $footer ?>
