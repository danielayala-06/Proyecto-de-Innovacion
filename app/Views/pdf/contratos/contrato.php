<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato N° <?= $contrato['id_contrato'] ?? '000000' ?></title>
    <link rel="stylesheet" href="<?= base_url('css/contratos.css') ?>">
</head>
<body>
<div class="page">

    <!-- HEADER -->
    <div class="header">
        <div class="logo-section">
            <div class="logo-name">Quique Ronceros</div>
            <div class="logo-subtitle">d i s e ñ o &amp; f o t o g r a f í a</div>
           <!-- <div class="logo-icons">
                <div class="logo-icon">🎨</div>
                <div class="logo-icon">📷</div>
                <div class="logo-icon">🎞</div>
                <div class="logo-icon">🖼</div>
            </div>-->
        </div>

        <div class="center-header">
            <div class="title-contrato">CONTRATO</div>
            <div class="address-info">
                Av. Alva Maurtua - Chincha Alta<br>
                Cel.: 056-956710619<br>
                Telf.: 056-269020 · Email: quiqueronceros@gmail.com
            </div>
        </div>

        <div class="right-header">
            <div class="date-box">
                <div class="label">DÍA</div>
                <div class="label">MES</div>
                <div class="label">AÑO</div>
                <div class="value"><?= $dia ?? '' ?></div>
                <div class="value"><?= $mes ?? '' ?></div>
                <div class="value"><?= $anio ?? '' ?></div>
            </div>
            <div class="numero-contrato">N° <?= str_pad($numero_contrato ?? '0', 6, '0', STR_PAD_LEFT) ?></div>
        </div>
    </div>

    <!-- DATOS DEL CLIENTE -->
    <div class="client-section">
        <div class="field-row">
            <span class="field-label">Cliente:</span>
            <span class="field-value"><?= $cliente['persona']['nombres'] ?> <?= $cliente['persona']['apellidos']?></span>
        </div>
        <div class="field-row">
            <span class="field-label">Dirección:</span>
            <span class="field-value"><?= $cliente_direccion ?? '' ?></span>
        </div>
        <div class="fields-inline">
            <div class="field-row">
                <span class="field-label">Teléfonos:</span>
                <span class="field-value"><?= $cliente_telefono ?? '' ?></span>
            </div>
            <div class="field-row">
                <span class="field-label">E-mail:</span>
                <span class="field-value"><?= $cliente['persona']['correo'] ?? '' ?></span>
            </div>
        </div>
    </div>

    <!-- TIPO DE SERVICIO -->
    <div class="section-title">TIPO DE SERVICIO</div>
    <div class="services-grid">
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'matrimonio_religioso' ? 'checked' : '' ?>"></div>
            <span class="service-label">Matrimonio Religioso</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'matrimonio_religioso' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'matrimonio_civil' ? 'checked' : '' ?>"></div>
            <span class="service-label">Matrimonio Civil</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'matrimonio_civil' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'cumpleanos' ? 'checked' : '' ?>"></div>
            <span class="service-label">Cumpleaños</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'cumpleanos' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === '15anos' ? 'checked' : '' ?>"></div>
            <span class="service-label">15 años</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === '15anos' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'promocion' ? 'checked' : '' ?>"></div>
            <span class="service-label">Promoción</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'promocion' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'publicidad' ? 'checked' : '' ?>"></div>
            <span class="service-label">Publicidad</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'publicidad' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'sesion_fotos' ? 'checked' : '' ?>"></div>
            <span class="service-label">Sesión de Fotos</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'sesion_fotos' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
        <div class="service-item">
            <div class="checkbox <?= ($tipo_servicio ?? '') === 'otros' ? 'checked' : '' ?>"></div>
            <span class="service-label">Otros</span>
            <div class="service-detail"><?= ($tipo_servicio ?? '') === 'otros' ? ($detalle_servicio ?? '') : '' ?></div>
        </div>
    </div>

    <!-- FECHA / HORA / LUGAR -->
    <div class="event-section">
        <div class="field-row">
            <span class="field-label">Fecha del Evento:</span>
            <span class="field-value"><?= $fecha_evento ?? '' ?></span>
        </div>
        <div class="fields-inline">
            <div class="field-row">
                <span class="field-label">Hora de Inicio:</span>
                <span class="field-value"><?= $hora_inicio ?? '' ?></span>
            </div>
            <div class="field-row">
                <span class="field-label">Hora de Culminación:</span>
                <span class="field-value"><?= $hora_fin ?? '' ?></span>
            </div>
        </div>
        <div class="field-row">
            <span class="field-label">Lugar del Evento:</span>
            <span class="field-value"><?= $lugar_evento ?? '' ?></span>
        </div>
    </div>

    <!-- POR LO SIGUIENTE -->
    <div class="description-section">
        <div class="section-title">POR LO SIGUIENTE:</div>
        <div class="description-lines"><?= $descripcion_linea1 ?? '' ?></div>
        <div class="description-lines"><?= $descripcion_linea2 ?? '' ?></div>
        <div class="description-lines"><?= $descripcion_linea3 ?? '' ?></div>
        <div class="description-lines"><?= $descripcion_linea4 ?? '' ?></div>
    </div>

    <!-- CLAUSULAS + PRECIOS -->
    <div class="bottom-section">
        <div class="clausulas-box">
            <div class="clausulas-title">CLÁUSULAS DEL CONTRATO</div>
            <ol>
                <li>El presente contrato es irrevocable.</li>
                <li>En caso de resolución del presente contrato por la parte del Cliente, el monto entregado en adelanto no será devuelto, pero podrá ser aplicado en otra fecha en un plazo máximo de 30 días.</li>
                <li>El precio total será cancelado hasta 1 día antes del evento.</li>
                <li>Los pagos y permisos exigidos por las Iglesias, Municipalidades y/o Instituciones que prestan servicios sociales y culturales deberán ser gestionados por el CLIENTE.</li>
                <li>QUIQUE RONCEROS - diseño &amp; fotografía, tiene el derecho de usar las fotografías para propósitos publicitarios sin requerir la autorización del Contratante.</li>
                <li>El abono del dinero implica la aceptación y conformidad del contrato en todas sus cláusulas expuestas.</li>
            </ol>
        </div>

        <div class="prices-box">
            <div class="price-row">
                <span class="price-label">PRECIO</span>
                <div class="price-value"><?= $precio ? 'S/ ' . number_format($precio, 2) : '' ?></div>
            </div>
            <div class="price-row">
                <span class="price-label">ADELANTO</span>
                <div class="price-value"><?= $adelanto ? 'S/ ' . number_format($adelanto, 2) : '' ?></div>
            </div>
            <div class="price-row">
                <span class="price-label">SALDO</span>
                <div class="price-value"><?= $saldo ? 'S/ ' . number_format($saldo, 2) : '' ?></div>
            </div>
        </div>
    </div>

    <!-- FIRMAS -->
    <div class="signatures">
        <div class="sig-block">
            <br><br>
            <div class="sig-line"></div>
            <div class="sig-name">CLIENTE</div>
            <div class="sig-dni">D.N.I.: <?= $cliente_dni ?? '.............................' ?></div>
        </div>
        <div class="sig-block">
            <br><br>
            <div class="sig-line"></div>
            <div class="sig-name">QUIQUE RONCEROS · FOTOGRAFIA</div>
            <div class="sig-sub">Enrique Mario Ronceros Flores</div>
            <div class="sig-dni">D.N.I.: 41008416</div>
        </div>
    </div>

</div>
</body>
</html>