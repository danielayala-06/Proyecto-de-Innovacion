<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
</head>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, sans-serif;
        font-size: 15px;
        color: #1a1a1a;
        line-height: 1.45;
    }

    .page { padding: 13mm 13mm 13mm 26mm; }

    /* ── HEADER ───────────────────────────────────────────────── */
    .header {
        display: table;
        width: 100%;
        padding-bottom: 14px;
        margin-bottom: 22px;
        border-bottom: 1px solid #1b2d6b;
    }
    .header::after { content:''; display:table; clear:both; }
    .h-left, .h-center, .h-right {
        display: table-cell;
        vertical-align: middle;
    }
    .h-center { text-align: center; }
    .h-right  { text-align: right; width: 140px; white-space: nowrap; }

    .logo-name {
        font-family: Georgia, serif;
        font-size: 18px; font-weight: bold; font-style: italic;
        color: #1b2d6b; line-height: 1.1;
    }
    .logo-sub  { font-size: 7.5px; letter-spacing: 2.5px; color: #1b2d6b; text-transform: lowercase; }
    .logo-info { font-size: 8px; color: #666; margin-top: 4px; line-height: 1.7; }

    .title-doc  {
        font-family: Georgia, serif;
        font-size: 21px; font-weight: 900;
        letter-spacing: 6px; color: #1b2d6b;
        text-transform: uppercase;
    }
    .title-sub  { font-size: 8px; color: #888; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 2px; }

    .num-label  { font-size: 7.5px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
    .num-value  { font-size: 19px; font-weight: 900; color: #c0392b; letter-spacing: 1px; }
    .num-date   { font-size: 8.5px; color: #888; margin-top: 3px; }

    /* ── SECTION TITLE ────────────────────────────────────────── */
    .sec {
        font-size: 8px; font-weight: 700; letter-spacing: 2px;
        text-transform: uppercase; color: #1b2d6b;
        border-bottom: 1px solid #1b2d6b;
        padding-bottom: 4px; margin: 24px 0 13px;
    }

    /* ── FIELD ROWS ───────────────────────────────────────────── */
    .row2 { display: table; width: 100%; margin-bottom: 5px; }
    .col2 { display: table-cell; width: 50%; }
    .col2:first-child { padding-right: 14px; }

    .field { display: table; width: 100%; border-bottom: 1px solid #bbb; margin-bottom: 7px; }
    .fl    { display: table-cell; font-weight: 700; font-size: 9px; white-space: nowrap; padding-right: 6px; color: #555; width: 1%; text-transform: uppercase; letter-spacing: .3px; }
    .fv    { display: table-cell; font-size: 11.5px; font-weight: 700; color: #1b2d6b; padding-bottom: 1px; }

    /* ── CHECKBOXES ───────────────────────────────────────────── */
    .svc-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .svc-table td { padding: 5px 5px; font-size: 11px; width: 25%; }
    .cb {
        display: inline-block; width: 14px; height: 14px;
        border: 1.5px solid #888; vertical-align: middle;
        margin-right: 4px;
        text-align: center; line-height: 14px;
    }
    .cb.on {
        border-color: #1b2d6b;
        font-family: Arial, sans-serif; font-size: 12px;
        font-weight: 900; color: #1b2d6b;
    }
    .svc-qty { font-weight: 700; color: #1b2d6b; }

    /* ── SESIONES ─────────────────────────────────────────────── */
    .ses-table { width: 100%; border-collapse: collapse; margin-bottom: 11px; }
    .ses-l { font-weight: 700; font-size: 11px; white-space: nowrap; padding-right: 8px; color: #444; width: 1%; }
    .ses-v { border-bottom: 1px solid #aaa; padding-bottom: 3px; }

    /* ── OBSERVACIONES ────────────────────────────────────────── */
    .obs-line { border-bottom: 1px solid #aaa; height: 20px; margin-bottom: 10px; }

    /* ── ITEMS TABLE ──────────────────────────────────────────── */
    .items { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 10.5px; }
    .items thead tr { background: #1b2d6b; color: #fff; }
    .items th { padding: 9px 8px; font-weight: 700; font-size: 8.5px; letter-spacing: .5px; text-transform: uppercase; }
    .items tbody tr:nth-child(even) { background: #f4f6fb; }
    .items td { padding: 10px 8px; border-bottom: 1px solid #e0e4ef; line-height: 1.5; }
    .items tfoot td { border-top: 1.5px solid #1b2d6b; font-weight: 900; font-size: 11px; padding: 9px 8px; color: #1b2d6b; }

    /* ── PRECIO BLOCK ─────────────────────────────────────────── */
    .price-grid { display: table; width: 100%; margin-top: 28px; }
    .price-left  { display: table-cell; vertical-align: top; padding-right: 14px; }
    .price-right { display: table-cell; vertical-align: middle; width: 185px; }

    .p-row { display: table; width: 100%; margin-bottom: 8px; }
    .p-lbl { display: table-cell; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #555; }
    .p-val { display: table-cell; text-align: right; font-size: 14px; font-weight: 900; color: #1b2d6b; }
    .p-sep { border: none; border-top: 1px solid #c5cde0; margin: 5px 0 12px; }
    .p-row.saldo .p-lbl { color: #1b2d6b; font-size: 10px; }
    .p-row.saldo .p-val { font-size: 18px; color: #1b2d6b; }

    /* ── CLAUSULAS ────────────────────────────────────────────── */
    .clausulas-box { border: 1px solid #c5cde0; border-radius: 5px; padding: 12px 14px; }
    .clausulas-title { font-size: 8.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #1b2d6b; margin-bottom: 8px; }
    .clausulas-box ol { padding-left: 13px; }
    .clausulas-box ol li { font-size: 8.5px; line-height: 2; color: #333; margin-bottom: 5px; }

    /* ── REGLAS ───────────────────────────────────────────────── */
    .reglas-box { border: 1px solid #c5cde0; border-radius: 5px; padding: 5px 9px; margin-bottom: 8px; background: #f8f9fd; }
    .reglas-title { font-size: 8px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #1b2d6b; margin-bottom: 4px; }
    .rr { display: table; width: 100%; margin-bottom: 3px; }
    .ri { display: table-cell; width: 12px; font-size: 10px; font-weight: 900; color: #1b2d6b; }
    .rt { display: table-cell; font-size: 9.5px; color: #333; line-height: 1.4; }
    .rb { display: inline-block; background: #dce3f3; color: #1b2d6b; font-size: 8px; font-weight: 700; border-radius: 8px; padding: 1px 5px; margin-left: 4px; vertical-align: middle; }
    .rb.lim { background: #f8d7da; color: #842029; }

    /* ── FIRMAS ───────────────────────────────────────────────── */
    .sigs { display: table; width: 100%; margin-top: 36px; }
    .sig  { display: table-cell; text-align: center; width: 50%; padding: 0 22px; vertical-align: bottom; }
    .sig-space { height: 65px; }
    .sig-line  { border-top: 1.5px solid #444; margin-bottom: 4px; }
    .sig-role  { font-size: 8px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #888; }
    .sig-name  { font-size: 11.5px; font-weight: 700; color: #1a1a1a; margin-top: 2px; }

    @page { margin: 0; }

    .binding-line {
        position: fixed;
        left: 21mm;
        top: 13mm;
        bottom: 13mm;
        width: 0;
        border-left: 8px solid #1b2d6b;
    }
</style>
<body>
<div class="binding-line"></div>
<?php
$promo    = $contrato['promociones'][0] ?? [];
$detalles = $contrato['detalles']       ?? [];

$reglasAplicadas = $contrato['reglas_aplicadas'] ?? ['violaciones' => [], 'activadas' => []];
$reglasActivadas = $reglasAplicadas['activadas']   ?? [];
$reglasLimitadas = $reglasAplicadas['violaciones'] ?? [];

// Segundo responsable: primero el cliente2 de BD, si no el campo libre del contrato
$cliente2Nombre    = !empty($contrato['cliente2_nombre'])   ? $contrato['cliente2_nombre']   : null;
$cliente2Telefono  = !empty($contrato['cliente2_telefono']) ? $contrato['cliente2_telefono'] : null;
$contacto2Nombre   = $cliente2Nombre   ?? ($contrato['contacto2_nombre']   ?? null);
$contacto2Telefono = $cliente2Telefono ?? ($contrato['contacto2_telefono'] ?? null);

$firmaNombreCliente = $promo ? $promo['nombre_promocion'] : $contrato['cliente'];

$categorias     = array_map(fn($c) => strtolower($c ?? ''), array_column($detalles, 'categoria'));
$tieneAnuarios  = !empty(array_filter($categorias, fn($c) => in_array($c, ['anuarios','anuario','paquetes'])));
$tieneCuadros   = !empty(array_filter($categorias, fn($c) => in_array($c, ['cuadros','cuadro'])));
$tienePhotobook = in_array('photobook', $categorias);
$tieneOtro      = !empty(array_filter($categorias, fn($c) => in_array($c, ['otro','otros','personalizado'])));

$mesesEs = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$numContrato = str_pad(date('y'), 2, '0', STR_PAD_LEFT) . '-' . str_pad($contrato['id_contrato'], 4, '0', STR_PAD_LEFT);
?>
<div class="page" id="contrato">

    <!-- HEADER -->
    <div class="header">
        <div class="h-left">
            <div class="logo-name">Quique Ronceros</div>
            <div class="logo-sub">el fotógrafo</div>
            <div class="logo-info">
                Cel. 983 488 541 &nbsp;·&nbsp; Telf. 056-612171<br>
                Quiqueronceros@gmail.com &nbsp;·&nbsp; Ica, Perú
            </div>
        </div>
        <div class="h-center">
            <div class="title-doc">Contrato</div>
            <div class="title-sub">Servicios fotográficos</div>
        </div>
        <div class="h-right">
            <div class="num-label">N° de contrato</div>
            <div class="num-value"><?= esc($numContrato) ?></div>
            <div class="num-date">
                <?php $fc = !empty($contrato['fecha_creacion']) ? $contrato['fecha_creacion'] : date('Y-m-d');
                      [$fY,$fM,$fD] = explode('-', substr($fc, 0, 10)); ?>
                <?= (int)$fD ?> de <?= $mesesEs[(int)$fM - 1] ?> de <?= $fY ?>
            </div>
        </div>
    </div>

    <?php if ($promo): ?>
    <div class="row2">
        <div class="col2">
            <div class="field">
                <div class="fl">Cliente:</div>
                <div class="fv"><?= esc($promo['nombre_colegio']) ?></div>
            </div>
        </div>
        <div class="col2">
            <div class="field">
                <div class="fl">Nivel:</div>
                <div class="fv">
                    <?php
                        $nivel = implode(' ', array_filter([
                            $promo['grado']           ?? '',
                            $promo['seccion']          ?? '',
                            $promo['num_estudiantes']  ? '(' . $promo['num_estudiantes'] . ' est.)' : '',
                        ]));
                        echo esc($nivel ?: '—');
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($promo['distrito']) || !empty($promo['provincia'])): ?>
    <div class="field">
        <div class="fl">Dirección:</div>
        <div class="fv"><?= esc(implode(', ', array_filter([$promo['distrito'] ?? '', $promo['provincia'] ?? '']))) ?></div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="row2">
        <div class="col2">
            <div class="field">
                <div class="fl">Contacto:</div>
                <div class="fv"><?= esc($contrato['cliente']) ?></div>
            </div>
        </div>
        <div class="col2">
            <div class="field">
                <div class="fl">Teléfono:</div>
                <div class="fv"><?= esc($contrato['telefono'] ?? '—') ?></div>
            </div>
        </div>
    </div>

    <?php if ($contacto2Nombre): ?>
    <div class="row2">
        <div class="col2">
            <div class="field">
                <div class="fl">Contacto adicional:</div>
                <div class="fv"><?= esc($contacto2Nombre) ?></div>
            </div>
        </div>
        <div class="col2">
            <div class="field">
                <div class="fl">Teléfono:</div>
                <div class="fv"><?= esc($contacto2Telefono ?? '—') ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- TIPO DE SERVICIO -->
    <div class="sec">Tipo de Servicio</div>
    <table class="svc-table">
        <tr>
            <td>
                <span class="cb <?= $tieneAnuarios ? 'on' : '' ?>"><?= $tieneAnuarios ? 'x' : '' ?></span> Anuarios
                <?php if ($tieneAnuarios):
                    $c = array_sum(array_column(array_filter($detalles, fn($d) => in_array(strtolower($d['categoria'] ?? ''), ['anuarios','anuario','paquetes'])), 'cantidad')); ?>
                    &nbsp;<span class="svc-qty"><?= $c ?></span>
                <?php endif; ?>
            </td>
            <td>
                <span class="cb <?= $tieneCuadros ? 'on' : '' ?>"><?= $tieneCuadros ? 'x' : '' ?></span> Cuadros
                <?php if ($tieneCuadros):
                    $c = array_sum(array_column(array_filter($detalles, fn($d) => in_array(strtolower($d['categoria'] ?? ''), ['cuadros','cuadro'])), 'cantidad')); ?>
                    &nbsp;<span class="svc-qty"><?= $c ?></span>
                <?php endif; ?>
            </td>
            <td>
                <span class="cb <?= $tienePhotobook ? 'on' : '' ?>"><?= $tienePhotobook ? 'x' : '' ?></span> Photobook
                <?php if ($tienePhotobook):
                    $c = array_sum(array_column(array_filter($detalles, fn($d) => strtolower($d['categoria'] ?? '') === 'photobook'), 'cantidad')); ?>
                    &nbsp;<span class="svc-qty"><?= $c ?></span>
                <?php endif; ?>
            </td>
            <td>
                <span class="cb <?= $tieneOtro ? 'on' : '' ?>"><?= $tieneOtro ? 'x' : '' ?></span> Otro
                <?php if ($tieneOtro):
                    $c = array_sum(array_column(array_filter($detalles, fn($d) => in_array(strtolower($d['categoria'] ?? ''), ['otro','otros','personalizado'])), 'cantidad')); ?>
                    &nbsp;<span class="svc-qty"><?= $c ?></span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php
    $sesiones  = $contrato['sesiones'] ?? [];
    $tipoLabel = ['estudio' => 'Estudio', 'colegio' => 'Colegio', 'exteriores' => 'Exteriores', 'otro' => 'Otro'];
    $sesLines  = [];
    foreach ($sesiones as $idx => $s) {
        if (!empty($s['fecha_hora_sesion'])) {
            $dt = new \DateTime($s['fecha_hora_sesion']);
            $sesLines[] = 'Sesión ' . ($idx + 1) . ': ' . $dt->format('d/m/Y') . ', ' . $dt->format('H:i')
                        . ' — ' . ($tipoLabel[$s['tipo']] ?? ucfirst($s['tipo']));
        }
    }
    $obsTexto       = trim($contrato['observaciones'] ?? '');
    $obsUsadas      = count($sesLines) + ($obsTexto ? 1 : 0);
    $obsLinesToShow = max(1, 2 - $obsUsadas);
    ?>

    <!-- OBSERVACIONES -->
    <div class="sec">Observaciones</div>
    <?php foreach ($sesLines as $line): ?>
    <div class="obs-line" style="line-height:20px; font-size:10px; color:#1b2d6b; font-weight:600;"><?= esc($line) ?></div>
    <?php endforeach; ?>
    <?php if ($obsTexto): ?>
    <div class="obs-line" style="line-height:20px; font-size:10px; color:#333;"><?= esc($obsTexto) ?></div>
    <?php endif; ?>
    <?php for ($i = 0; $i < $obsLinesToShow; $i++): ?>
    <div class="obs-line"></div>
    <?php endfor; ?>

    <!-- DETALLE -->
    <div class="sec">Detalle del Servicio</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width:7%;text-align:center;">Cant.</th>
                <th style="text-align:left;">Descripción</th>
                <th style="width:18%;text-align:right;">P. Unitario</th>
                <th style="width:18%;text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $item): ?>
            <?php if ($item['tipo_item'] === 'personalizado' && (float)$item['precio_unitario'] === 0.0) continue; ?>
            <tr>
                <td style="text-align:center;"><?= esc($item['cantidad']) ?></td>
                <td><?= esc($item['descripcion']) ?></td>
                <td style="text-align:right;">S/. <?= number_format($item['precio_unitario'], 2) ?></td>
                <td style="text-align:right;">S/. <?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($detalles)): ?>
            <tr><td colspan="4" style="text-align:center;color:#aaa;font-style:italic;padding:8px;">— Sin ítems —</td></tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($detalles)): ?>
        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td style="text-align:right;">Total</td>
                <td style="text-align:right;">S/. <?= number_format($contrato['total'], 2) ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!empty($reglasActivadas) || !empty($reglasLimitadas)): ?>
    <div class="sec">Condiciones Especiales</div>
    <div class="reglas-box">
        <?php foreach ($reglasActivadas as $r): ?>
        <div class="rr">
            <div class="ri">+</div>
            <div class="rt">
                <?= esc($r['descripcion']) ?>
                <?php $lb = !empty($r['nombre_producto_beneficio']) ? $r['nombre_producto_beneficio'] : ($r['valor_beneficio'] ?? ''); ?>
                <?php if ($lb): ?><span class="rb"><?= esc($lb) ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php foreach ($reglasLimitadas as $r): ?>
        <div class="rr">
            <div class="ri" style="color:#842029;">!</div>
            <div class="rt">
                <?= esc($r['descripcion']) ?>
                <span class="rb lim">Límite: <?= (int)$r['limite'] ?> uds.</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CLÁUSULAS + PRECIOS -->
    <div class="price-grid">
        <div class="price-left">
            <div class="clausulas-box">
                <div class="clausulas-title">Cláusulas del Contrato</div>
                <ol>
                    <li>El presente contrato es irrevocable.</li>
                    <li>En caso de resolución por parte del cliente, el monto entregado en adelanto no será devuelto, pero podrá aplicarse como crédito en otra fecha en un plazo máximo de 30 días.</li>
                    <li>En cada sesión se da un porcentaje del pago total.</li>
                    <li>El saldo final del 10 % se cancela cuando se entregan los anuarios.</li>
                    <li>El abono del dinero implica la aceptación y conformidad del cliente en todas sus cláusulas expuestas.</li>
                </ol>
            </div>
        </div>
        <div class="price-right">
            <div class="p-row">
                <div class="p-lbl">Total:</div>
                <div class="p-val">S/. <?= number_format($contrato['total'], 2) ?></div>
            </div>
            <hr class="p-sep">
            <div class="p-row">
                <div class="p-lbl">Adelanto:</div>
                <div class="p-val">S/. <?= number_format($contrato['adelanto'], 2) ?></div>
            </div>
            <hr class="p-sep">
            <div class="p-row saldo">
                <div class="p-lbl">Saldo:</div>
                <div class="p-val">S/. <?= number_format($contrato['total'] - $contrato['adelanto'], 2) ?></div>
            </div>
        </div>
    </div>

    <!-- FIRMAS -->
    <div class="sigs">
        <div class="sig"></div>
        <div class="sig">
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div class="sig-role">El Prestador</div>
            <div class="sig-name">Quique Ronceros · Fotografía</div>
        </div>
    </div>

</div>
</body>
</html>
