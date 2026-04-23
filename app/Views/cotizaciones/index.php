<?= $header ?>
<p class="section-label">Panel principal</p>

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

    <div class="table-card">
      <div class="card-title-bar"><i class="bi bi-calendar3"></i> Próximas sesiones</div>
      <table>
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Daniel Ayala</td>
            <td>Matrimonio</td>
            <td>16 abr</td>
            <td><span class="badge-activo">Confirmado</span></td>
          </tr>
          <tr>
            <td>Morgan Bondioli</td>
            <td>Retrato</td>
            <td>18 abr</td>
            <td><span class="badge-activo">Confirmado</span></td>
          </tr>
          <tr>
            <td>Diggy Félix</td>
            <td>Quinceañero</td>
            <td>22 abr</td>
            <td><span class="badge-pendiente">Pendiente</span></td>
          </tr>
        </tbody>
      </table>
    </div>

<?= $footer ?>