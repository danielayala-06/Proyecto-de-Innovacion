<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Formularios de Promoción</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#F7F4EE;color:#1A1714;font-size:15px}
.topbar{background:#1A1714;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.topbar-brand{display:flex;align-items:center;gap:.6rem}
.topbar-dot{width:9px;height:9px;border-radius:50%;background:#B8963E;flex-shrink:0}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.05rem;color:#F7F4EE;font-weight:700}
.topbar p{font-size:.7rem;color:#B8963E;letter-spacing:.1em;text-transform:uppercase;margin-top:1px}
.container{max-width:960px;margin:0 auto;padding:2rem 1.5rem}
.page-title{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:.25rem}
.page-sub{color:#6B6460;font-size:.85rem;margin-bottom:2rem}
.card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead tr{background:#F7F4EE}
th{padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:600;color:#6B6460;text-transform:uppercase;letter-spacing:.06em}
td{padding:.85rem 1rem;border-top:1px solid #F7F4EE;font-size:.88rem}
tr:hover td{background:#FDFCF9}
.badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:6px;font-size:.72rem;font-weight:600;letter-spacing:.04em}
.badge-green{background:#E6F5EC;color:#1A5E2E}
.badge-amber{background:#F8F0D8;color:#7A5000}
.prog-bar{height:6px;background:#EDE8DC;border-radius:3px;overflow:hidden;min-width:80px}
.prog-fill{height:100%;background:#B8963E;border-radius:3px;transition:width .4s}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s}
.btn-dark{background:#1A1714;color:#fff}
.btn-dark:hover{opacity:.85}
.empty{text-align:center;padding:3rem;color:#9E9488;font-size:.9rem}
</style>
</head>
<body>

<div class="topbar">
    <div class="topbar-brand">
        <div class="topbar-dot"></div>
        <div>
            <h1>Quique Ronceros el Fotógrafo</h1>
            <p>Panel de Formularios</p>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="page-title">Promociones Escolares</h2>
    <p class="page-sub">Gestiona las promociones y el estado de los formularios enviados.</p>

    <div class="card">
        <?php if (empty($promociones)): ?>
            <p class="empty">No hay promociones registradas aún.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Promoción</th>
                    <th>Alumnos</th>
                    <th>Progreso</th>
                    <th>Cuadros</th>
                    <th>Anuarios</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promociones as $p): ?>
                <?php
                    $pct = $p['total_alumnos'] > 0
                        ? round(($p['completados'] / $p['total_alumnos']) * 100)
                        : 0;
                    $cuadrosLibres  = max(0, (int)$p['cuadros_total']  - (int)$p['cuadros_usados']);
                    $anuariosLibres = max(0, (int)$p['anuarios_total'] - (int)$p['anuarios_usados']);
                ?>
                <tr>
                    <td><?= esc($p['nombre_colegio'] ?? '—') ?></td>
                    <td style="font-weight:600"><?= esc($p['nombre']) ?><?= $p['nivel'] ? '<br><small style="color:#6B6460;font-weight:400">' . esc($p['nivel']) . '</small>' : '' ?></td>
                    <td><?= $p['total_alumnos'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
                            <span style="font-size:.75rem;color:#6B6460"><?= $pct ?>%</span>
                        </div>
                        <div style="font-size:.72rem;color:#9E9488;margin-top:2px"><?= $p['completados'] ?>/<?= $p['total_alumnos'] ?></div>
                    </td>
                    <td><?= $cuadrosLibres ?>/<?= (int)$p['cuadros_total'] ?></td>
                    <td><?= $anuariosLibres ?>/<?= (int)$p['anuarios_total'] ?></td>
                    <td>
                        <span class="badge <?= $p['activa'] ? 'badge-green' : 'badge-amber' ?>">
                            <?= $p['activa'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/formularios/promocion/' . $p['id']) ?>" class="btn btn-dark">Ver →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
