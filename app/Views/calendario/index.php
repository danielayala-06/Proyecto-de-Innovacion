<?= $header ?>

<main class="main-content" id="main-content">
  <div class="container">
    <p class="page-title">Calendario de Sesiones</p>

    <div class="row g-4">

      <!-- ── Columna principal ──────────────────────────────────────── -->
      <div class="col-12 col-xl-9">

        <!-- Controles -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <button class="cal-nav-btn" id="prevBtn"><i class="bi bi-chevron-left"></i></button>
            <span class="cal-month-label" id="monthLabel"></span>
            <button class="cal-nav-btn" id="nextBtn"><i class="bi bi-chevron-right"></i></button>
            <button class="cal-nav-btn" id="todayBtn" style="width:auto;padding:0 10px;font-size:0.73rem;">Hoy</button>
          </div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="cal-filters">
              <label><input type="checkbox" class="filter-cb" data-estado="pendiente"  checked style="accent-color:#856404;"> <span class="legend-dot ld-ses-pendiente"></span>  Pendiente</label>
              <label><input type="checkbox" class="filter-cb" data-estado="finalizado" checked style="accent-color:#1A5E2E;"> <span class="legend-dot ld-ses-finalizado"></span> Finalizado</label>
              <label><input type="checkbox" class="filter-cb" data-estado="cancelado"  checked style="accent-color:#842029;"> <span class="legend-dot ld-ses-cancelado"></span>  Cancelado</label>
            </div>
            <select class="filter-select" id="filterTipoCalendario" style="height:30px;font-size:.73rem;padding:0 8px;min-width:110px;">
              <option value="">Todos los tipos</option>
              <option value="colegio">Colegio</option>
              <option value="exteriores">Exteriores</option>
              <option value="estudio">Estudio</option>
              <option value="otro">Otro</option>
            </select>
            <div class="d-flex">
              <button class="view-btn active" id="btnMes"><i class="bi bi-grid-3x3"></i> Mes</button>
              <button class="view-btn"        id="btnLista"><i class="bi bi-list-ul"></i> Lista</button>
            </div>
          </div>
        </div>

        <!-- Vista mes -->
        <div id="viewMes">
          <div class="cal-grid mb-1" id="dayNames"></div>
          <div class="cal-grid"      id="calGrid"></div>
        </div>

        <!-- Vista lista -->
        <div id="viewLista" style="display:none;">
          <div id="listaContainer"></div>
        </div>

      </div>

      <!-- ── Panel lateral ──────────────────────────────────────────── -->
      <div class="col-12 col-xl-3">

        <div class="row g-2 mb-3">
          <div class="col-6">
            <div class="stat-mini">
              <div class="sm-label">Este mes</div>
              <div class="sm-value" id="statMes">—</div>
            </div>
          </div>
          <div class="col-6">
            <div class="stat-mini">
              <div class="sm-label">Pendientes</div>
              <div class="sm-value" id="statPend" style="color:var(--amber-text);">—</div>
            </div>
          </div>
        </div>

        <!-- Panel: sesiones del día / próximas -->
        <div class="side-card">
          <div class="side-card-title" id="panelTitulo">Próximas sesiones</div>
          <div id="panelContenido"></div>
        </div>

      </div>
    </div>

  </div>
</main>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/calendario/calendarioMain.js') ?>"></script>

<?= $footer ?>
