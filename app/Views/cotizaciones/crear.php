<?= $header ?>
<main>
    <p class="section-label">Cotizaciones</p>
    <div class="row g-4">

        <div class="col-12 col-xl-8">
            <div class="form-card">
                <div class="card-heading">Nueva cotización</div>

                <div class="section-divider">Datos del Cliente</div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-7">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchCliente" placeholder="Buscar cliente...">
                            <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <input type="text" class="form-control" id="nombreCliente" placeholder="Cliente">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" class="form-control" id="dniCliente" placeholder="DNI">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" class="form-control" id="telefonoCliente" placeholder="Teléfono">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="email" class="form-control" id="emailCliente" placeholder="Correo electrónico">
                    </div>
                </div>

                <div class="row g-4">
                    <!-- PAQUETES -->
                    <div class="col-12 col-md-5">
                        <div class="section-divider">Paquetes</div>
                        <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>
                        <button class="btn-paquete mt-2" onclick="abrirModalPaquete()">
                            <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                        </button>
                    </div>

                    <!-- SERVICIOS -->
                    <div class="col-12 col-md-7">
                        <div class="section-divider">Servicios</div>

                        <!-- Toggles predefinidos -->
                        <div class="servicio-preset-btn" id="btn-dron" onclick="togglePreset('dron')">
                            <div class="sp-left">
                                <div class="sp-icon"><i class="bi bi-joystick"></i></div>
                                <span class="sp-name">Toma con Dron</span>
                            </div>
                            <span class="sp-price">S/ 150.00</span>
                            <i class="bi bi-check-circle-fill sp-check"></i>
                        </div>

                        <div class="servicio-preset-btn" id="btn-video" onclick="togglePreset('video')">
                            <div class="sp-left">
                                <div class="sp-icon"><i class="bi bi-camera-video"></i></div>
                                <span class="sp-name">Video resumen (3 min)</span>
                            </div>
                            <span class="sp-price">S/ 100.00</span>
                            <i class="bi bi-check-circle-fill sp-check"></i>
                        </div>

                        <div class="servicio-preset-btn" id="btn-foto" onclick="togglePreset('foto')">
                            <div class="sp-left">
                                <div class="sp-icon"><i class="bi bi-image"></i></div>
                                <span class="sp-name">Foto</span>
                            </div>
                            <input class="foto-precio-input" id="fotoPrecioInput" type="number" value="" min="0"
                                placeholder="S/ 0.00" onclick="event.stopPropagation()" oninput="onFotoPrecioInput()">
                            <i class="bi bi-check-circle-fill sp-check"></i>
                        </div>

                        <!-- Lista con botón agregar servicio personalizado -->
                        <div class="servicios-list mt-3">
                            <div class="servicio-add" onclick="abrirModalServicio()">
                                <i class="bi bi-plus-circle"></i> Agregar servicio +
                            </div>
                            <div id="serviciosList">
                                <div class="servicios-empty" id="serviciosEmpty">Sin servicios adicionales</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-6">
                        <label>Fecha del evento</label>
                        <input type="date" class="form-control" id="fechaEvento">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>Tipo de evento</label>
                        <select class="form-select" id="tipoEvento">
                            <option value="">Seleccionar...</option>
                            <option>Matrimonio</option>
                            <option>Quinceañero</option>
                            <option>Retrato</option>
                            <option>Corporativo</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label>Notas adicionales</label>
                        <textarea class="form-control" id="notas" rows="3" placeholder="Observaciones o detalles del contrato..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN -->
        <div class="col-12 col-xl-4">
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
            <button class="btn-guardar" onclick="guardarCotizacion()">
                <i class="bi bi-check-circle me-2"></i>Guardar cotización
            </button>
        </div>
    </div>

</main>
<?= $footer ?>
