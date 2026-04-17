 <div class="modal fade" id="modalNuevaCotizacion" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Nueva CotizaciÃ³n
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- AquÃ­ va tu formulario -->
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">DNI del cliente</label>
                            <input type="text" class="form-control" id="dni" maxlength="8" placeholder="Ej: 45235856">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombres y apellidos</label>
                            <input type="text" class="form-control" id="nombres" placeholder="Ej: Diggy Tony F.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha del evento</label>
                            <input type="date" class="form-control" id="fecha-evento">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora del evento</label>
                            <input type="time" class="form-control" id="hora-evento">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Precio (S/)</label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control" id="precio" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar">Guardar CotizaciÃ³n</button>
                </div>
            </div>
        </div>
    </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="<?= base_url('/public/js/func.js')?>"></script>
</body>
</html>