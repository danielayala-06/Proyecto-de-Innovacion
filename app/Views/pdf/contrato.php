<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo?></title>
</head>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #1a1a1a;
    }

    .page {
        width: 75%;
        margin: 20px auto;
        background: #fff;
        padding: 8mm 15mm;
        height: 90%;
    }

    /* ═══ HEADER ═══ */
    .header {
        display: table;
        width: 100%;
        border-bottom: 2.5px solid #1b2d6b;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .header-left, .header-center, .header-right {
        display: table-cell;
        vertical-align: middle;
    }
    .header-center { text-align: center; }
    .header-right  { text-align: right; white-space: nowrap; width: 136px; }

    .logo-text {
        font-size: 21px; color: #1b2d6b;
        font-style: italic; font-weight: bold;
        font-family: Georgia, serif; line-height: 1.1;
    }
    .logo-sub {
        font-size: 8px; letter-spacing: 2px;
        text-transform: lowercase; color: #1b2d6b;
    }

    .title-contrato {
        font-size: 30px; font-weight: 900;
        letter-spacing: 5px; color: #1b2d6b;
        text-transform: uppercase;
    }
    .header-contact {
        font-size: 8.5px; color: #444;
        margin-top: 4px; line-height: 1.6;
    }

    .date-table { border-collapse: collapse; width: 126px; margin-bottom: 5px; }
    .date-table th {
        background: #1b2d6b; color: #fff;
        font-size: 8px; padding: 3px 6px;
        border: 1px solid #1b2d6b; font-weight: 700;
    }
    .date-table td {
        border: 1px solid #1b2d6b; padding: 4px 6px;
        font-size: 13px; font-weight: 700;
        text-align: center; color: #1b2d6b;
    }
    .numero-contrato {
        font-size: 22px; font-weight: 900;
        color: #c0392b; letter-spacing: 1px;
    }

    /* ═══ CAMPOS ═══ */
    .field-row {
        display: table; width: 100%;
        border-bottom: 1px solid #777;
        margin-bottom: 12px; padding-bottom: 1px;
    }
    .field-label {
        display: table-cell; font-weight: 700;
        font-size: 10.5px; white-space: nowrap;
        padding-right: 6px; width: 1%;
    }
    .field-value {
        display: table-cell; font-size: 11px;
        color: #1b2d6b; font-weight: 700;
    }
    .two-col { display: table; width: 100%; margin-bottom: 7px; }
    .two-col .col { display: table-cell; width: 50%; }
    .two-col .col:first-child { padding-right: 14px; }
    .two-col .col .field-row { margin-bottom: 0; }

    /* ═══ TIPO DE SERVICIO ═══ */
    .section-title {
        font-weight: 700; font-size: 11px;
        text-transform: uppercase; letter-spacing: .5px;
        margin-bottom: 7px; margin-top: 4px;
    }
    .services-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .services-table td { padding: 3px 4px; font-size: 10.5px; vertical-align: middle; }

    .cb {
        display: inline-block; width: 12px; height: 12px;
        border: 1.5px solid #555; vertical-align: middle;
        position: relative; margin-right: 4px;
    }
    .cb.checked { border-color: #1b2d6b; }
    .cb.checked::after {
        content: 'X'; position: absolute;
        top: -2px; left: 2px; font-size: 12px;
        color: #1b2d6b; font-weight: bold;
    }
    .svc-line {
        display: inline-block; border-bottom: 1px solid #888;
        min-width: 110px; vertical-align: bottom;
        color: #1b2d6b; font-weight: 700;
    }

    .sesion-row { display: table; width: 100%; margin-bottom: 12px; }
    .sesion-label { display: table-cell; font-weight: 700; font-size: 10.5px; white-space: nowrap; padding-right: 6px; }
    .sesion-value { display: table-cell; border-bottom: 1px solid #777; color: #1b2d6b; font-weight: 700; }

    /* ═══ POR LO SIGUIENTE ═══ */
    .desc-line {
        border-bottom: 1px solid #aaa; min-height: 17px;
        margin-bottom: 12px; color: #1b2d6b;
        font-size: 10.5px; font-weight: 600; padding: 1px 2px;
    }

    /* ═══ CLÁUSULAS + PRECIOS ═══ */
    .bottom-grid { display: table; width: 100%; margin-top: 12px; }
    .bottom-left  { display: table-cell; vertical-align: top; padding-right: 14px; }
    .bottom-right { display: table-cell; vertical-align: middle; width: 175px; }

    .clausulas-box { border: 1.5px solid #888; border-radius: 5px; padding: 8px 10px; }
    .clausulas-title { text-align: center; font-weight: 700; font-size: 11px; margin-bottom: 6px; }
    .clausulas-box ol { padding-left: 13px; font-size: 8.2px; line-height: 1.55; color: #222; }
    .clausulas-box ol li { margin-bottom: 2px; }

    .price-block { margin-bottom: 10px; }
    .price-label { font-size: 11px; font-weight: 900; letter-spacing: 1px; color: #1b2d6b; margin-bottom: 3px; text-transform: uppercase; }
    .price-pill { background: #d4daea; border-radius: 22px; padding: 6px 16px; font-size: 16px; font-weight: 900; text-align: center;}

    /* ═══ FIRMAS ═══ */
    .signatures { display: table; width: 100%; margin-top: 40px; }
    .sig-col {
        display: table-cell; text-align: center;
        width: 50%; padding: 0 20px; vertical-align: bottom;
    }

    /* Espacio en blanco para firmar a mano */
    .sig-space { height: 56px; }

    .sig-line { border-top: 1.5px solid #333; margin-bottom: 5px; }

    .sig-name { font-weight: 700; font-size: 11px; }
    .sig-sub  { font-size: 9px; color: #555; margin-top: 2px; }
    @page{margin: 10mm;}

</style>
<body>
<div class="page" id="contrato">
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="logo-text">Quique Ronceros</div>
            <div class="logo-sub">el fotógrafo</div>
        </div>
        <div class="header-center">
            <div class="title-contrato">CONTRATO</div>
            <div class="header-contact">
                Cel.: 983488541 &nbsp;·&nbsp; Telf.: 056-612171<br>
                email: Quiqueronceros@gmail.com
            </div>
        </div>
        <div class="header-right">
            <table class="date-table">
                <tr><th>DÍA</th><th>MES</th><th>AÑO</th></tr>
                <tr>
                    <td><?= date('d')?></td>
                    <td><?= date('m')?></td>
                    <td><?= date('y')?></td>
                </tr>
            </table>
            <div class="numero-contrato">
                <?= str_pad(date('y'), 3, "0", STR_PAD_LEFT)?>
                -
                <?= str_pad($contrato['id_contrato'], 4, "0", STR_PAD_LEFT)?>
            </div>
        </div>
    </div>

    <!-- DATOS DEL CLIENTE -->
    <div class="field-row">
        <div class="field-label">Cliente:</div>
        <div class="field-value">Promoción Secundaria Colegio Byron</div>
    </div>
    <div class="two-col">
        <div class="col">
            <div class="field-row">
                <div class="field-label">Dirección:</div>
                <div class="field-value">Chincha</div>
            </div>
        </div>
        <div class="col">
            <div class="field-row">
                <div class="field-label">Contacto:</div>
                <div class="field-value">
                    <?= $contrato['cliente']?>
                    :
                    <?= $contrato['telefono']?>
                </div>
            </div>
        </div>
    </div>

    <!-- TIPO DE SERVICIO -->
    <div class="section-title">Tipo de Servicio</div>
    <table class="services-table">
        <tr>
            <td style="width:50%">
                <span class="cb checked"></span>
                Anuarios &nbsp;
                <span class="svc-line">21 estudiantes</span>
            </td>
            <td style="width:50%">
                <span class="cb"></span>
                Otros &nbsp;
                <span class="svc-line">&nbsp;</span>
            </td>
        </tr>
        <tr>
            <!--<td>
                <span class="cb"></span>
                Sesión prom. &nbsp;
                <span class="svc-line">&nbsp;</span>
            </td>-->
            <td>
                <span class="cb"></span>
                Cuadros
                <span class="svc-line"></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="cb"></span>
                Photobook &nbsp;
                <span class="svc-line">&nbsp;</span>
            </td>
            <!--<td>
                <span class="cb"></span>
                Fiesta de Promoción &nbsp;
                <span class="svc-line">&nbsp;</span>
            </td>-->
        </tr>
    </table>

    <div class="sesion-row">
        <div class="sesion-label">Fecha de la sesión 1:</div>
        <!--<div class="sesion-value">15 / 07 / 2025</div>-->
    </div>
    <div class="sesion-row">
        <div class="sesion-label">Fecha de la sesión 2:</div>
        <!--<div class="sesion-value">22 / 07 / 2025</div>-->
    </div>

    <!-- POR LO SIGUIENTE -->
    <div class="section-title" style="margin-top:10px">Por lo Siguiente:</div>
    <div class="desc-line">21 Anuarios 25x25 (5 hojas) – Estudiantes S/. 135.00 c/u</div>
    <div class="desc-line">1 Anuario 25x25 (5 hojas) – Tutor (sin costo adicional)</div>
    <div class="desc-line">2 Sesiones de fotos incluidas en el paquete</div>
    <div class="desc-line">&nbsp;</div>

    <!-- CLÁUSULAS + PRECIOS -->
    <div class="bottom-grid">
        <div class="bottom-left">
            <div class="clausulas-box">
                <div class="clausulas-title">CLÁUSULAS DEL CONTRATO</div>
                <ol>
                    <li>El presente contrato es irrevocable.</li>
                    <li>En caso de resolución del presente contrato por la parte del Cliente, el monto entregado en adelanto no será devuelto, pero podrá ser aplicado en otra fecha en un plazo máximo de 30 días.</li>
                    <li>En cada sesión se da un porcentaje del pago total.</li>
                    <li>El saldo final del 10% será cancelado cuando se entregue los anuarios.</li>
                    <li>El abono del dinero implica la aceptación y conformidad del contrato en todas sus cláusulas expuestas.</li>
                </ol>
            </div>
        </div>

        <!-- SALTO DE LINEA XD-->
        <br>
        <br>
        <br>
        <br>
        <br>

        <div class="bottom-right">
            <div class="price-block">
                <div class="price-label">Total</div>
                <div class="price-pill">
                    S/.
                    <?= number_format($contrato['total_estimado'], 2) ?>
                </div>
            </div>
            <div class="price-block">
                <div class="price-label">Adelanto</div>
                <div class="price-pill">
                    S/.
                    <?= number_format($contrato['adelanto'], 2) ?>
                </div>
            </div>
            <div class="price-block" style="margin-bottom:0">
                <div class="price-label">Saldo</div>
                <div class="price-pill">
                    S/.
                    <?php $saldo_pendiente = $contrato['total_estimado'] - $contrato['adelanto'] ?>
                    <?= number_format($saldo_pendiente, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <!-- FIRMAS -->
    <div class="signatures">

        <div class="sig-col">
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div class="sig-name">CLIENTE</div>
            <div class="sig-sub">Promoción Secundaria Colegio Byron</div>
        </div>

        <div class="sig-col">
            <div class="sig-space"></div>
            <div class="sig-line"></div>
            <div class="sig-name">ADMINISTRACIÓN</div>
            <div class="sig-sub">Quique Ronceros · Fotografía</div>
        </div>
    </div>

    </div><!-- /page -->
</body>
</html>