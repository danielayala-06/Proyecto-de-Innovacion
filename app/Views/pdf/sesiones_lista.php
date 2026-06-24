<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
</head>
<style>
    @page {
        margin-top:15mm;
        margin-right:15mm;
        margin-bottom:15mm;
        margin-left:30mm;
    }

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family: DejaVu Sans, sans-serif;
        font-size:12px;
        color:#5f5a54;
    }

    .page{
        margin-top:15mm;
        margin-right:15mm;
        margin-bottom:15mm;
        margin-left:30mm;
    }

    /* ==========================
    PALETA DEL SISTEMA
    ========================== */

    :root{
        --primary:#b78a2d;
        --border:#e5ddd1;
        --bg:#f5f1eb;
        --text:#5f5a54;
        --white:#ffffff;
    }

    /* ==========================
    PORTADA
    ========================== */

    .cover-card{
        border:1px solid var(--border);
        border-radius:12px;
        padding:24px;
        background:#fff;
    }

    .brand{
        font-size:24px;
        font-weight:700;
        color:#5f5a54;
    }

    .brand-sub{
        color:#b78a2d;
        margin-top:5px;
    }

    .contract{
        text-align:right;
        color:#777;
        font-size:10px;
    }

    .section-title{
        margin-top:20px;
        margin-bottom:12px;
        color:var(--primary);
        font-size:14px;
        font-weight:bold;
        border-bottom:1px solid var(--border);
        padding-bottom:8px;
    }

    .info-table{
        width:100%;
        border-collapse:collapse;
    }

    .info-table td{
        padding:10px;
        border-bottom:1px solid #f0f0f0;
    }

    .info-label{
        width:220px;
        font-weight:bold;
        color:#666;
    }

    .page-break{
        page-break-after:always;
    }

    /* ==========================
    ASISTENCIAS
    ========================== */

    .attendance-card{
        border:1px solid var(--border);
        border-radius:12px;
        padding:20px;
        background:#fff;
    }

    .attendance-title{
        font-size:18px;
        font-weight:700;
        color:#5f5a54;
        margin-bottom:15px;
    }

    .attendance-table{
        width:100%;
        border-collapse:collapse;
    }

    .attendance-table th{
        background:#faf8f5;
        color:#6f685f;
        border:1px solid var(--border);
        padding:10px;
        text-align:center;
        font-size:10px;
    }

    .attendance-table td{

        border:1px solid var(--border);

        padding:8px;
    }

    .col-num{
        width:15px;
        text-align:center;
    }

    .col-name{
        width:200px;
    }

    .col-obs{
        width:130px;
    }

    /* ==========================
    CHECKS GRANDES
    ========================== */

    .session-box{
        width:35px;
        height:35px;
        margin:auto;
        border:1px solid #c8b68c;
        background:#fff;
        text-align:center;
        line-height:26px;
        font-size:18px;
        font-weight:bold;
        color:#b78a2d;
    }

    .checked{
        text-align:center;
        background:#faf7ef;
    }

    .footer-info{
        margin-top:15px;
        padding:10px;
        border:1px solid var(--border);
        background:#faf8f5;
    }
    .session-header-type{
        border-bottom:1px solid #999;
        padding-bottom:3px;
        margin-bottom:3px;
        min-height:14px;
    }

    .session-header-date{
        font-size:9px;
    }
</style>
<body>
<div class="page">
    <div class="cover-card">

        <div style="overflow:hidden; margin-bottom:20px;">

            <div style="float:left;">
                <div class="brand">
                    CONTROL DE SESIONES FOTOGRÁFICAS
                </div>

                <div class="brand-sub">
                    Registro oficial de asistencia
                </div>
            </div>
            <div class="contract">
                Contrato #<?= str_pad($contrato['id_contrato'],4,'0',STR_PAD_LEFT) ?>
                <br>
                <?= date('d/m/Y') ?>
            </div>

        </div>

        <div class="section-title">
            DATOS GENERALES DEL AULA
        </div>

        <table class="info-table">

            <?php
            $fechaSesionColegio = null;
            $fechaSesionEstudioExterior = null;

            foreach ($sesiones as $sesion) {
                if ($fechaSesionColegio === null && $sesion['tipo'] === 'colegio') {
                    $fechaSesionColegio = date('d/m/Y', strtotime($sesion['fecha_hora_sesion']));
                }

                if ($fechaSesionEstudioExterior === null && in_array($sesion['tipo'], ['estudio', 'exteriores'], true)) {
                    $fechaSesionEstudioExterior = date('d/m/Y', strtotime($sesion['fecha_hora_sesion']));
                }

                if ($fechaSesionColegio !== null && $fechaSesionEstudioExterior !== null) {
                    break;
                }
            }
            ?>

            <tr>
                <td class="info-label">Colegio</td>
                <td><?= esc($promocion['nombre_colegio']) ?></td>
            </tr>

            <tr>
                <td class="info-label">Promoción</td>
                <td><?= esc($promocion['nombre']) ?></td>
            </tr>

            <tr>
                <td class="info-label">Grado / Sección</td>
                <td>
                    <?= esc(($promocion['grado'] ?? '') . ' ' . ($promocion['seccion'] ?? '')) ?>
                </td>
            </tr>

            <tr>
                <td class="info-label">N° de alumnos</td>
                <td><?= count($estudiantes) ?></td>
            </tr>
            
            <tr>
                <td class="info-label">Padre de Familia</td>
                <td><?= $contrato['cliente'] ?></td>
            </tr>
            
            <tr>
                <td class="info-label">Fecha de Sesion - Colegio</td>
                <td><?= $fechaSesionColegio ?? '' ?></td>
            </tr>
            
            <tr>
                <td class="info-label">Fecha de Sesion - Estudio/Exterior</td>
                <td><?= $fechaSesionEstudioExterior ?? '' ?></td>
            </tr>
            
            <tr>
                <td class="info-label">Fecha Final de Entrega</td>
                <td></td>
            </tr>

        </table>

    </div>

    <!-- Para agregar el salto de pagina -->
    <div class="page-break"></div>


    <br>
    <br>
    <br>

    <!-- Tabla de asistencia -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre del Alumno</th>
                <?php for($i = 0; $i < 3; $i++): ?>
                    <?php $sesion = $sesiones[$i] ?? null; ?>

                <th>
                    <div class="session-header-type">
                        <?= $sesion ? esc($sesion['tipo']) : '&nbsp;' ?>
                    </div>

                    <div class="session-header-date">
                        <?= $sesion
                            ? date('d/m/y', strtotime($sesion['fecha_hora_sesion']))
                            : '.../.../....'
                        ?>
                    </div>

                </th>
                <?php endfor; ?>

                <th>Observación</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($estudiantes as $idx => $est): ?>
                <tr>
                    <td class="col-num">
                        <?= $idx + 1 ?>
                    </td>
                    <td class="col-name">
                        <?= esc(
                            trim(
                                ($est['apellidos'] ?? '')
                                ? ($est['apellidos'] . ', ' . $est['nombres'])
                                : $est['nombres']
                            )
                        ) ?>
                    </td>
                    <?php for($i = 0; $i < 3; $i++): ?>
                        <?php $sesion = $sesiones[$i] ?? null; ?>

                        <td align="center">

                            <?php if($sesion): ?>

                                <?php
                                $estado =
                                    $asistencia[$sesion['id_sesion']]
                                            [$est['id_estudiante']]
                                    ?? null;
                                ?>

                                <?php if($estado === '1' || $estado === 1): ?>

                                    <div class="session-box checked">
                                        X
                                    </div>

                                <?php else: ?>

                                    <div class="session-box"></div>

                                <?php endif; ?>

                            <?php else: ?>

                                <!-- Sesión todavía no creada -->
                            <div class="session-box"></div>
                        <?php endif; ?>
                    </td>
                    <?php endfor; ?>
                    <td class="col-obs">
                        <?= esc($est['observaciones'] ?? '') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-info">
        Total alumnos:
        <strong><?= count($estudiantes) ?></strong>
        &nbsp;&nbsp;&nbsp;
        Total sesiones:
        <strong><?= count($sesiones) ?></strong>
    </div>

</div>
</div>
</body>
</html>
