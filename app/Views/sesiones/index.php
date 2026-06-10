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

<!-- ═══════ MODAL NUEVA / EDITAR SESIÓN ════════════════════════════════════════ -->
<div class="modal fade" id="modalSesion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalSesionTitulo">Nueva sesión</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sfId">
                <input type="hidden" id="sfPromocion">

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label>Tipo de sesión</label>
                        <select class="form-select" id="sfTipo">
                            <option value="">Seleccionar...</option>
                            <option value="colegio">Colegio</option>
                            <option value="exteriores">Exteriores</option>
                            <option value="estudio">Estudio</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Fecha</label>
                        <input type="date" class="form-control" id="sfFecha">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Hora</label>
                    <input type="time" class="form-control" id="sfHora" value="08:00" min="07:00" max="20:00">
                </div>
                <div class="mb-3">
                    <label>Observaciones</label>
                    <textarea class="form-control" id="sfObservaciones" rows="2"
                        placeholder="Detalles del lugar, indicaciones, etc."></textarea>
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
                    <div class="col-12 col-md-6">
                        <label>Nombres *</label>
                        <input type="text" class="form-control" id="efNombres" maxlength="30">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Apellidos *</label>
                        <input type="text" class="form-control" id="efApellidos" maxlength="30">
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
                    <div class="col-12 col-md-6">
                        <label>Nombres *</label>
                        <input type="text" class="form-control" id="apNombres" maxlength="100">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Apellidos</label>
                        <input type="text" class="form-control" id="apApellidos" maxlength="100">
                    </div>
                    <div class="col-6 col-md-4">
                        <label>Tipo de documento *</label>
                        <select class="form-select" id="apDocTipo">
                            <option value="">--</option>
                            <option value="DNI">DNI</option>
                            <option value="CE">CE</option>
                            <option value="PASAPORTE">Pasaporte</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label>Nº documento *</label>
                        <input type="text" class="form-control" id="apDocNum" maxlength="20">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Teléfono * (9 dígitos)</label>
                        <input type="tel" class="form-control" id="apTelefono" maxlength="9" pattern="\d{9}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="apCorreo" maxlength="150">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Relación con el estudiante *</label>
                        <select class="form-select" id="apRelacion">
                            <option value="">--</option>
                            <option value="madre">Madre</option>
                            <option value="padre">Padre</option>
                            <option value="hermano">Hermano/a</option>
                            <option value="otro">Otro</option>
                        </select>
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
