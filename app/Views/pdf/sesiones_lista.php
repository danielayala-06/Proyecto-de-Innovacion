<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
</head>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; }
    .page { padding: 16mm 16mm 16mm 16mm; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
    .brand { font-size: 18px; font-weight: 700; color: #0b2c63; }
    .brand-sub { font-size: 10px; color: #555; margin-top: 2px; }
    .title { text-transform: uppercase; font-size: 15px; font-weight: 700; color: #0b2c63; margin-bottom: 8px; }
    .field-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
    .field-box { flex: 1 1 220px; border: 1px solid #d1d7e0; border-radius: 6px; padding: 8px 10px; }
    .field-label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 4px; }
    .field-value { font-size: 12px; color: #111; }
    .section-title { margin: 20px 0 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0b2c63; border-bottom: 1px solid #0b2c63; padding-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th, td { border: 1px solid #c8d0de; padding: 6px 8px; text-align: left; }
    th { background: #eef2fb; font-size: 10px; text-transform: uppercase; color: #33415c; }
    td { font-size: 11px; vertical-align: top; }
    .text-center { text-align: center; }
    .small { font-size: 10px; color: #555; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; color: #fff; }
    .badge-pendiente { background: #f0ad4e; }
    .badge-finalizado { background: #28a745; }
    .badge-cancelado { background: #dc3545; }
    .footnote { font-size: 10px; color: #666; margin-top: 10px; }
    @page { margin: 0; }
</style>
<body>
<div class="page">
    <div class="header">
        <div>
            <div class="brand">Lista de estudiantes</div>
            <div class="brand-sub">Sesiones y asistencia</div>
        </div>
        <div style="text-align:right; font-size:10px; color:#555;">
            Contrato #<?= str_pad($contrato['id_contrato'], 4, '0', STR_PAD_LEFT) ?><br>
            Fecha: <?= date('d/m/Y') ?>
        </div>
    </div>

    <div class="section-title">Datos de la promoción</div>
    <div class="field-row">
        <div class="field-box">
            <div class="field-label">Colegio</div>
            <div class="field-value"><?= esc($promocion['nombre_colegio'] ?? '—') ?></div>
        </div>
        <div class="field-box">
            <div class="field-label">Promoción</div>
            <div class="field-value"><?= esc($promocion['nombre'] ?? '—') ?></div>
        </div>
        <div class="field-box">
            <div class="field-label">Grado / Sección</div>
            <div class="field-value"><?= esc(($promocion['grado'] ?? '') . ($promocion['seccion'] ? ' ' . $promocion['seccion'] : '')) ?></div>
        </div>
        <div class="field-box">
            <div class="field-label">Estudiantes esperados</div>
            <div class="field-value"><?= (int) ($promocion['num_estudiantes'] ?? count($estudiantes)) ?></div>
        </div>
    </div>

    <div class="section-title">Productos asociados</div>
    <table>
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th>Producto / paquete</th>
                <th style="width:14%;">Cantidad</th>
                <th style="width:20%;">Precio unitario</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $idx => $prod): ?>
            <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td><?= esc($prod['nombre'] ?? $prod['descripcion'] ?? '—') ?></td>
                <td class="text-center"><?= (int) ($prod['cantidad'] ?? 0) ?></td>
                <td class="text-center"><?= isset($prod['precio_unitario']) ? 'S/ ' . number_format((float) $prod['precio_unitario'], 2, '.', ',') : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($productos)): ?>
            <tr><td colspan="4" class="text-center small">No hay productos registrados para esta cotización.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Asistencia de estudiantes</div>
    <table>
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th>Estudiante</th>
                <?php foreach ($sesiones as $sesion): ?>
                    <?php $fecha = substr($sesion['fecha_hora_sesion'] ?? '', 0, 10); ?>
                    <th style="width:11%;">
                        <?= esc($fecha) ?><br>
                        <span class="small"><?= esc($sesion['tipo']) ?></span>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($estudiantes as $idx => $est): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><?= esc(trim(($est['apellidos'] ?? '') ? ($est['apellidos'] . ', ' . $est['nombres']) : $est['nombres'])) ?></td>
                    <?php foreach ($sesiones as $sesion): ?>
                        <?php $estado = $asistencia[$sesion['id_sesion']][$est['id_estudiante']] ?? null; ?>
                        <td class="text-center">
                            <?php if ($estado === '1' || $estado === 1): ?>X<?php elseif ($estado === '0' || $estado === 0): ?>—<?php else: ?>&nbsp;<?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($estudiantes)): ?>
                <tr><td colspan="<?= 2 + max(1, count($sesiones)) ?>" class="text-center small">No hay estudiantes registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Observaciones de sesiones</div>
    <table>
        <thead>
            <tr>
                <th style="width:12%;">Fecha</th>
                <th style="width:10%;">Tipo</th>
                <th>Observaciones</th>
                <th style="width:10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sesiones as $sesion): ?>
                <tr>
                    <td><?= esc(substr($sesion['fecha_hora_sesion'] ?? '', 0, 10)) ?></td>
                    <td><?= esc($sesion['tipo']) ?></td>
                    <td><?= esc($sesion['observaciones'] ?? '—') ?></td>
                    <td class="text-center">
                        <span class="badge badge-<?= $sesion['estado'] === 'finalizado' ? 'finalizado' : ($sesion['estado'] === 'cancelado' ? 'cancelado' : 'pendiente') ?>">
                            <?= esc(ucfirst($sesion['estado'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sesiones)): ?>
                <tr><td colspan="4" class="text-center small">No hay sesiones programadas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footnote">Datos generados desde el contrato y la promoción. El reporte incluye alumnos actuales, su asistencia y las observaciones de cada sesión.</div>
</div>
</body>
</html>
