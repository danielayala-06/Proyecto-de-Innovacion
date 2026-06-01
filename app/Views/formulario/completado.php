<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario completado — Quique Ronceros</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#F7F4EE;color:#1A1714;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.card{background:#fff;border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,.10);padding:3rem 2.5rem;max-width:440px;width:100%;text-align:center}
.icon{font-size:3.5rem;margin-bottom:1rem}
h1{font-family:'Playfair Display',serif;font-size:1.7rem;margin-bottom:.75rem;color:#1A1714}
p{color:#6B6460;font-size:.95rem;line-height:1.6}
.alumno-name{color:#B8963E;font-weight:600}
.brand{margin-top:2.5rem;font-size:.78rem;color:#B8963E;letter-spacing:.08em;text-transform:uppercase;font-weight:500}
</style>
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
