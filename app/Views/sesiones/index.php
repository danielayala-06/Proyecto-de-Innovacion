<?= $header ?>
<main id="main-content">
    <div class="container">

        <!-- BREADCRUMB + HEADER -->
        <div class="sesiones-header">
            <a href="<?= base_url('/contratos') ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Contratos
            </a>
            <div>
                <p class="section-label">Gestión de Sesiones</p>
                <div class="sesiones-contrato-info">
                    <span><i class="bi bi-file-earmark-text"></i> Contrato #<?= str_pad($contrato['id_contrato'], 4, '0', STR_PAD_LEFT) ?></span>
                    <span class="text-muted">·</span>
                    <span><?= esc($contrato['cliente']) ?></span>
                    <span class="text-muted">·</span>
                    <span class="<?= $contrato['estado'] === 'ACTIVO' ? 'badge-aprobada' : 'badge-rechazada' ?>">
                        <?= esc($contrato['estado']) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (empty($promociones)): ?>
        <div class="empty-state" style="margin-top:3rem;">
            <i class="bi bi-mortarboard" style="font-size:2.5rem;"></i>
            Este contrato no tiene promociones vinculadas.
        </div>
        <?php else: ?>

        <!-- LAYOUT DOS COLUMNAS -->
        <div class="sesiones-layout">

            <!-- COLUMNA IZQUIERDA: tabs promociones + estudiantes -->
            <div class="sesiones-sidebar">
                <div class="sesiones-sidebar-section">
                    <div class="sesiones-sidebar-title">Promociones</div>
                    <div id="promocionesTabs" class="promo-tabs-list"></div>
                </div>
                <div class="sesiones-sidebar-section" style="flex:1;overflow:hidden;display:flex;flex-direction:column;">
                    <div class="sesiones-sidebar-title">Estudiantes</div>
                    <div id="estudiantesContainer" style="overflow-y:auto;flex:1;padding-right:4px;"></div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: sesiones -->
            <div class="sesiones-main">
                <div id="sesionesContainer"></div>
            </div>

        </div>

        <?php endif; ?>
    </div>
</main>

<style>
.sf-layout{display:flex;min-height:340px;}
.sf-cal{width:270px;min-width:250px;flex-shrink:0;padding:1rem;border-right:1px solid var(--border);background:var(--bg-page);}
.sf-form{flex:1;padding:1.25rem;min-width:0;}
.sf-cal-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;}
.sf-cal-lbl{font-size:.82rem;font-weight:700;color:var(--text-primary);}
.sf-cal-nav{background:none;border:1px solid var(--border);border-radius:6px;cursor:pointer;
            color:var(--text-secondary);padding:.18rem .45rem;line-height:1;font-size:.8rem;}
.sf-cal-nav:hover{background:var(--bg-hover);}
.sf-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
.mca-name{text-align:center;font-size:.65rem;font-weight:700;color:var(--text-muted);padding:.2rem 0;}
.mca-cell{display:flex;flex-direction:column;align-items:center;border-radius:5px;
          padding:.12rem 0 .22rem;cursor:pointer;transition:background .12s;}
.mca-cell.empty{cursor:default;}
.mca-cell.mca-past{opacity:.32;cursor:not-allowed;pointer-events:none;}
.mca-cell:not(.mca-past):not(.empty):hover{background:var(--bg-hover);}
.mca-cell.mca-hoy .mca-num{background:var(--accent);color:#fff;border-radius:50%;
    width:20px;height:20px;display:flex;align-items:center;justify-content:center;}
.mca-cell.mca-sel{background:var(--bg-hover);outline:2px solid var(--accent);outline-offset:-2px;}
.mca-num{font-size:.74rem;font-weight:500;line-height:1.3;}
.mca-dots{display:flex;gap:2px;justify-content:center;min-height:5px;}
.mca-dot{width:5px;height:5px;border-radius:50%;display:inline-block;}
.mca-dot.ses-pendiente{background:#B8963E;}
.mca-dot.ses-finalizado{background:#22863a;}
.mca-dot.ses-cancelado{background:#dc3545;}
.sf-cal-legend{margin-top:.6rem;font-size:.68rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;}
@media(max-width:600px){.sf-layout{flex-direction:column;}.sf-cal{width:100%;border-right:none;border-bottom:1px solid var(--border);}}
.mca-day-info{margin-top:.75rem;padding-top:.65rem;border-top:1px solid var(--border);max-height:160px;overflow-y:auto;}
.mca-day-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.4rem;}
.mca-ses-row{display:flex;align-items:flex-start;gap:.45rem;padding:.25rem 0;border-bottom:1px solid var(--border);}
.mca-ses-row:last-child{border-bottom:none;}
.mca-ses-hora{font-size:.75rem;font-weight:700;color:var(--accent);min-width:36px;flex-shrink:0;padding-top:1px;}
.mca-ses-info{flex:1;min-width:0;}
.mca-ses-name{font-size:.74rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mca-ses-meta{font-size:.67rem;color:var(--text-muted);}
.mca-day-empty{font-size:.73rem;color:var(--text-muted);text-align:center;padding:.4rem 0;}
</style>

<!-- ═══════ MODAL NUEVA / EDITAR SESIÓN ════════════════════════════════════════ -->
<div class="modal fade" id="modalSesion" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalSesionTitulo">Nueva sesión</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="sf-layout">

                    <!-- ── Mini calendario ─────────────────────────────── -->
                    <div class="sf-cal">
                        <div class="sf-cal-hdr">
                            <button class="sf-cal-nav" onclick="miniCalPrev()"><i class="bi bi-chevron-left"></i></button>
                            <span id="miniCalLabel" class="sf-cal-lbl"></span>
                            <button class="sf-cal-nav" onclick="miniCalNext()"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div id="miniCalGrid" class="sf-cal-grid"></div>
                        <div class="sf-cal-legend">
                            <span class="mca-dot ses-pendiente"></span> Pendiente &nbsp;
                            <span class="mca-dot ses-finalizado"></span> Finalizado
                        </div>
                        <div id="miniCalDayInfo" class="mca-day-info" style="display:none;"></div>
                    </div>

                    <!-- ── Formulario ──────────────────────────────────── -->
                    <div class="sf-form">
                        <input type="hidden" id="sfId">
                        <input type="hidden" id="sfPromocion">

                        <div class="mb-3">
                            <label>Tipo de sesión</label>
                            <select class="form-select form-select-sm" id="sfTipo">
                                <option value="">Seleccionar...</option>
                                <option value="colegio">Colegio</option>
                                <option value="exteriores">Exteriores</option>
                                <option value="estudio">Estudio</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Fecha <small class="text-muted">(elige en el calendario)</small></label>
                            <input type="date" class="form-control form-control-sm" id="sfFecha">
                        </div>
                        <div class="mb-3">
                            <label>Hora</label>
                            <input type="time" class="form-control form-control-sm" id="sfHora" value="09:00" min="09:00" max="20:00">
                        </div>
                        <div class="mb-3">
                            <label>Observaciones</label>
                            <textarea class="form-control form-control-sm" id="sfObservaciones" rows="3"
                                placeholder="Detalles del lugar, indicaciones, etc."></textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarSesion()">Guardar sesión</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ MODAL NUEVO ESTUDIANTE ════════════════════════════════════════════ -->
<div class="modal fade" id="modalEstudiante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Registrar estudiante</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Datos del estudiante -->
                <p class="form-section-label"><i class="bi bi-person-badge"></i> Datos del estudiante</p>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label>Nombre completo *</label>
                        <input type="text" class="form-control" id="efNombreCompleto" maxlength="100" placeholder="Ej: Tony Quispe Mamani">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Fecha de nacimiento</label>
                        <input type="date" class="form-control" id="efNacimiento">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Color favorito</label>
                        <input type="text" class="form-control" id="efColor" maxlength="30">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Profesión futura</label>
                        <input type="text" class="form-control" id="efProfesion" maxlength="40">
                    </div>
                </div>

                <!-- Datos del apoderado -->
                <p class="form-section-label"><i class="bi bi-person-fill"></i> Datos del apoderado</p>
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label>Nombre completo *</label>
                        <input type="text" class="form-control" id="apNombreCompleto" maxlength="100" placeholder="Ej: Mirian Tasayco Murillo">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Teléfono * (9 dígitos)</label>
                        <input type="tel" class="form-control" id="apTelefono" maxlength="9" pattern="\d{9}"
                               oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Relación con el estudiante *</label>
                        <select class="form-select" id="apRelacion">
                            <option value="">--</option>
                            <option value="madre">Madre</option>
                            <option value="padre">Padre</option>
                            <option value="hermano">Hermano/a</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="apCorreo" maxlength="150">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarEstudiante()">Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ MODAL PERFIL ESTUDIANTE ══════════════════════════════════════════ -->
<div class="modal fade" id="modalPerfilEstudiante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-person-badge me-2" style="color:var(--accent);"></i>
                    <span id="perfilNombre">Perfil del estudiante</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="perfilBody">
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="bi bi-arrow-repeat"></i> Cargando...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ OFFCANVAS ASISTENCIA ══════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAsistencia"
     style="width:380px;">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title" id="asistenciaTitulo">Asistencia</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3" id="asistenciaContainer"></div>
</div>

<!-- Datos para el JS -->
<script>
    const BASE_URL      = "<?= base_url('') ?>";
    const ID_CONTRATO   = <?= (int) $contrato['id_contrato'] ?>;
    const PROMOCIONES   = <?= json_encode(array_map(fn($p) => [
        'id_promocion' => (int) $p['id_promocion'],
        'nombre'       => $p['nombre'],
        'grado'        => $p['grado'],
        'seccion'      => $p['seccion'],
        'nombre_colegio' => $p['nombre_colegio'],
    ], $promociones)) ?>;
    const CONFIG_SESIONES = <?= json_encode(array_map(fn($c) => [
        'tipo_sesion'       => $c['tipo_sesion'],
        'num_sesiones'      => (int) $c['num_sesiones'],
        'lugar_descripcion' => $c['lugar_descripcion'],
        'nombre_paquete'    => $c['nombre_paquete'],
    ], $sesionesConfig)) ?>;
</script>
<script type="module" src="<?= base_url('js/modules/sesiones/sesionesMain.js') . '?v=' . filemtime(FCPATH . 'js/modules/sesiones/sesionesMain.js') ?>"></script>

<?= $footer ?>
