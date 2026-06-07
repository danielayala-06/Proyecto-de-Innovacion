<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario completado — Quique Ronceros</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('css/formulario-estado.css') ?>">
</head>
<body>
<div class="card">
    <div class="icon">✅</div>
    <h1>¡Ya enviaste tu formulario!</h1>
    <p>Hola <span class="alumno-name"><?= esc($alumno['nombre'] ?? '') ?></span>, tu información ya fue registrada correctamente.</p>
    <p style="margin-top:.75rem">Si necesitas hacer algún cambio, comunícate directamente con el fotógrafo.</p>
    <p class="brand">Quique Ronceros el Fotógrafo</p>
</div>
</body>
</html>
