<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">
    <p class="section-label">Cotizaciones</p>

        <!-- FORMULARIO -->
        <form id="form-cotizacion" class="col-12 ">
            <input type="hidden" name="id_cliente" id="idCliente">
            <div class="d-flex justify-content-center gap-4">
                <div class="form-card">

                    <header class="card-heading">
                        <h2 class="h5 m-0">Nueva cotización</h2>
                    </header>

                    <!-- ================= CLIENTE ================= -->
                    <fieldset class="mb-4">
                        <legend class="section-divider">Datos del Cliente</legend>

                        <!-- Buscador -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-7">
                                <div class="input-group">
                                    <label for="searchCliente" class="visually-hidden">Buscar cliente</label>
                                    <input type="text" class="form-control" id="searchCliente"
                                           placeholder="DNI, teléfono o nombre...">
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="searchFeedback" class="form-text mt-1"></div>
                            </div>
                        </div>

                        <!-- Datos -->
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nombresCliente" class="form-label">Nombres*</label>
                                <input type="text" class="form-control" id="nombresCliente" name="nombres_cliente">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="apellidosCliente" class="form-label">Apellidos*</label>
                                <input type="text" class="form-control" id="apellidosCliente" name="apellidos_cliente">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="dniCliente" class="form-label">DNI*</label>
                                <input type="text" class="form-control" id="dniCliente" name="dni">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="telefonoCliente" class="form-label">Teléfono*</label>
                                <input type="tel" class="form-control" id="telefonoCliente" name="telefono"
                                       maxlength="9" placeholder="9XXXXXXXX">
                                <div id="telefonoFeedback" class="form-text text-danger mt-1" style="display:none;"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="emailCliente" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="emailCliente" name="correo">
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= SERVICIOS Y PAQUETES ================= -->
                    <div class="row g-4">

                        <!-- PAQUETES -->
                        <fieldset class="col-12 col-md-5">
                            <legend class="section-divider">Paquetes</legend>

                            <button type="button" class="btn-paquete mt-2" id="btn-modal-paquete">
                                <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                            </button>
                            <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset>

                        <!-- SERVICIOS -->
                        <fieldset class="col-12 col-md-7">
                            <legend class="section-divider">Servicios</legend>
                            <button type="button" class="btn-paquete mt-2" id="btn-modal-servicio">
                                <i class="bi bi-plus-circle me-1"></i> Agregar servicio
                            </button>
                            <div id="serviciosContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset>
                    </div>

                    <!-- ================= EVENTO ================= -->
                    <fieldset class="mt-4">
                        <legend class="section-divider">Detalles del Evento</legend>

                        <div class="row g-3">
                            <!-- Nombre del evento -->
                            <div class="col-12">
                                <label for="nombre" class="form-label">Nombre del evento*</label>
                                <input type="text" class="form-control" required minlength="5" maxlength="50" id="nombre" name="nombre">
                            </div>

                            <!-- Fecha hora inicio del evento-->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Fecha del evento</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control" id="fechaInicio-date">
                                    <input type="time" class="form-control" id="fechaInicio-time" step="1800" style="flex:0 0 auto;width:120px;">
                                </div>
                                <input type="hidden" name="fechaInicio" id="fechaInicio">
                            </div>

                            <!-- Fecha hora fin del evento-->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Fecha fin del evento</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control" id="fechaFin-date">
                                    <input type="time" class="form-control" id="fechaFin-time" step="1800" style="flex:0 0 auto;width:120px;">
                                </div>
                                <input type="hidden" name="fechaFin" id="fechaFin">
                            </div>

                            <!-- Direccion del evento-->
                            <div class="col-12">
                                <label for="direccion" class="form-label">Direccion del evento</label>
                                <input type="text" name="direccion" class="form-control" id="direccion">
                            </div>

                            <!-- Referencia del sitio -->
                            <div class="col-12">
                                <label for="referencia" class="form-label">Referencia del evento</label>
                                <input type="text" name="referencia" class="form-control" id="referencia">
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="notas" class="form-label">Notas adicionales</label>
                                <textarea
                                        class="form-control"
                                        id="notas"
                                        name="observaciones"
                                        rows="3"
                                        placeholder="Observaciones o detalles del contrato..."
                                ></textarea>
                            </div>
                        </div>
                    </fieldset>

                </div>

                <!-- RESUMEN -->
                <div class="col-md-3">
                    <div class="resumen-card mb-3">
                        <div class="resumen-title">Resumen</div>
                        <div id="resumenItems">
                            <div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>
                        </div>
                        <div class="resumen-row total mt-2">
                            <span>Total</span>
                            <span id="totalResumen">S/ 0.00</span>
                        </div>
                    </div>
                    <div class="resumen-card mb-3">
                        <div class="resumen-title">Cliente seleccionado</div>
                        <div id="clienteSeleccionado" style="font-size:0.82rem;color:#666;">Ningún cliente seleccionado</div>
                    </div>
                    <button class="btn-guardar" type="submit">
                        <i class="bi bi-check-circle me-2"></i>Guardar cotización
                    </button>
                </div>
        <!--FIN DEL FORMULARIO-->
            </div>
        </form>

</div>
</main>

<!-- MODAL SERVICIOS -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Seleccionar servicio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="panel-servicios" class="overflow-auto" style="max-height: 20rem;">
                    <!-- Servicios cargados dinámicamente por JS -->
                </div>
                <div class="mt-3">
                    <label for="servicioModalPrecio" class="form-label">Precio (S/)</label>
                    <input type="number" class="form-control" id="servicioModalPrecio" placeholder="0.00" min="0" step="0.01">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btn-confirmar-servicio">Agregar servicio</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAQUETES POR CATEGORÍA -->
<div class="modal fade" id="modalPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Seleccionar paquete</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- CATEGORIAS DE LOS PAQUETES-->
                <div class="cat-tabs">
                    <button class="cat-tab active" onclick="cambiarCategoria('quinceaneros', this)">Quinceañeros</button>
                    <button class="cat-tab" onclick="cambiarCategoria('cuadros', this)">Cuadros</button>
                    <button class="cat-tab" onclick="cambiarCategoria('anuarios', this)">Anuarios</button>
                </div>
                <!-- -->
                <div class="cat-panel active overflow-auto" id="panel-quinceaneros" style="max-height: 20rem;">

                    <!-- PAQUETES GENERADOS DE MANERA ASYNC POR JS  -->
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Básico','Sesión + 50 fotos editadas',350)">
                        <div class="po-left">
                            <div class="po-name">Paquete Básico</div>
                            <div class="po-desc">Sesión + 50 fotos editadas</div>
                        </div>

                        <span class="po-price">S/ 350.00</span>
                        <i class="bi bi-check-circle-fill po-check"></i>

                    </div>

                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Plus','Sesión + 100 fotos + álbum 20×30',550)">
                        <div class="po-left"><div class="po-name">Paquete Plus</div><div class="po-desc">Sesión + 100 fotos + álbum 20×30</div></div>
                        <span class="po-price">S/ 550.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>

                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Premium','Sesión + 150 fotos + álbum + video',850)">
                        <div class="po-left"><div class="po-name">Paquete Premium</div><div class="po-desc">Sesión + 150 fotos + álbum + video</div></div>
                        <span class="po-price">S/ 850.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

                <div class="cat-panel" id="panel-cuadros">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 30×40 cm','Impresión en canvas, 1 foto',80)">
                        <div class="po-left"><div class="po-name">Cuadro 30×40 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 80.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 50×70 cm','Impresión en canvas, 1 foto',130)">
                        <div class="po-left"><div class="po-name">Cuadro 50×70 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 130.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Pack Cuadros ×3','3 cuadros 20×30 a elección',200)">
                        <div class="po-left"><div class="po-name">Pack Cuadros ×3</div><div class="po-desc">3 cuadros 20×30 a elección</div></div>
                        <span class="po-price">S/ 200.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

                <div class="cat-panel" id="panel-anuarios">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Básico','20 páginas, tapa blanda, 30 fotos',120)">
                        <div class="po-left"><div class="po-name">Anuario Básico</div><div class="po-desc">20 páginas, tapa blanda, 30 fotos</div></div>
                        <span class="po-price">S/ 120.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Estándar','40 páginas, tapa dura, 60 fotos',200)">
                        <div class="po-left"><div class="po-name">Anuario Estándar</div><div class="po-desc">40 páginas, tapa dura, 60 fotos</div></div>
                        <span class="po-price">S/ 200.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Premium','60 páginas, tapa dura, fotos ilimitadas + USB',320)">
                        <div class="po-left"><div class="po-name">Anuario Premium</div><div class="po-desc">60 páginas, tapa dura, fotos ilimitadas + USB</div></div>
                        <span class="po-price">S/ 320.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btn-confirmar-paquetes">Agregar paquete</button>
            </div>
        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>"</script>
<script type="module" src="<?= base_url('js/main.js')?>"></script>
<!--<script src="<?php /*= base_url('js/cotizaciones/cotizaciones.js')*/?>"></script>
<script src="<?php /*= base_url('js/cotizaciones/SearchClient.js')*/?>"></script>
<script src="<?php /*= base_url('js/cotizaciones/FetchServicios.js')*/?>"></script>
<script src="<?php /*= base_url('js/cotizaciones/FetchProductos.js')*/?>"></script>-->

<?= $footer ?>
