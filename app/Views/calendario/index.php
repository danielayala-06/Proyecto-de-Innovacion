<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">
    <p class="section-label">Calendario</p>

    <div class="row g-4">

      <!-- ── Columna principal ─────────────────────────────────────── -->
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
              <label><input type="checkbox" class="filter-cb" data-type="cotizacion" checked style="accent-color:#4a6fa5;"> <span class="legend-dot ld-cotizacion"></span> Cotizaciones</label>
              <label><input type="checkbox" class="filter-cb" data-type="contrato"   checked style="accent-color:#2a6040;"> <span class="legend-dot ld-contrato"></span>   Contratos</label>
              <label><input type="checkbox" class="filter-cb" data-type="entrega"    checked style="accent-color:#6a3a8a;"> <span class="legend-dot ld-entrega"></span>    Entregas</label>
            </div>
            <div class="d-flex">
              <button class="view-btn active" id="btnMes"><i class="bi bi-grid-3x3"></i> Mes</button>
              <button class="view-btn"        id="btnLista"><i class="bi bi-list-ul"></i> Lista</button>
            </div>
            <button class="btn-nuevo" onclick="abrirNuevo()">
              <i class="bi bi-plus-circle"></i> Nuevo evento
            </button>
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

      <!-- ── Panel lateral ─────────────────────────────────────────── -->
      <div class="col-12 col-xl-3">

        <div class="row g-2">
          <div class="col-6">
            <div class="stat-mini">
              <div class="sm-label">Este mes</div>
              <div class="sm-value" id="statMes">0</div>
            </div>
          </div>
          <div class="col-6">
            <div class="stat-mini">
              <div class="sm-label">Pendientes</div>
              <div class="sm-value" id="statPend" style="color:#d4a017;">0</div>
            </div>
          </div>
        </div>

        <!-- Eventos del día / próximos -->
        <div class="side-card mb-3">
          <div class="side-card-title" id="panelTitulo">Próximos eventos</div>
          <div id="panelContenido"></div>
        </div>
      </div>
    </div>

</div>
</main>

<!-- MODAL EVENTO -->
  <div class="modal fade" id="modalEvento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="modalTitulo">Nuevo evento</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="eId">
          <div class="mb-3">
            <label>Título</label>
            <input type="text" class="form-control" id="eTitulo" placeholder="Ej: Sesión quinceañero Ana Flores">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label>Tipo</label>
              <select class="form-select" id="eTipo">
                <option value="cotizacion">Cotización</option>
                <option value="contrato">Contrato</option>
                <option value="entrega">Entrega</option>
              </select>
            </div>
            <div class="col-6">
              <label>Estado</label>
              <select class="form-select" id="eEstado">
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
                <option value="completado">Completado</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label>Fecha</label>
              <input type="date" class="form-control" id="eFecha">
            </div>
            <div class="col-6">
              <label>Hora</label>
              <input type="time" class="form-control" id="eHora">
            </div>
          </div>
          <div class="mb-3">
            <label>Cliente</label>
            <input type="text" class="form-control" id="eCliente" placeholder="Nombre del cliente">
          </div>
          <div class="mb-3">
            <label>Monto (S/)</label>
            <input type="number" class="form-control" id="eMonto" placeholder="0.00" min="0">
          </div>
          <div class="mb-1">
            <label>Notas</label>
            <textarea class="form-control" id="eNotas" rows="2" placeholder="Detalles adicionales..."></textarea>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button class="btn btn-outline-danger btn-sm" id="btnEliminar" style="display:none;" onclick="eliminarEvento()">
            <i class="bi bi-trash me-1"></i>Eliminar
          </button>
          <div class="ms-auto d-flex gap-2">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary btn-sm" onclick="guardarEvento()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>const BASE_URL = "<?= base_url('') ?>"</script>
<script type="module" src="<?= base_url('js/modules/calendario/calendarioMain.js') ?>"></script>





<?= $footer ?>
