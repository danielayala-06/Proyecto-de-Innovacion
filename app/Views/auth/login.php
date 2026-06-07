<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Ronceros Fotografía</title>

    <script>
        (function () {
            var t = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/login.css') ?>">
</head>
<body>

<button class="btn-theme-login" id="btn-theme" aria-label="Cambiar tema" title="Cambiar tema">
    <i id="theme-icon" class="bi bi-moon-fill"></i>
</button>

<div class="login-bg">
    <div class="login-card">

        <!-- ── PANEL FOTO ─────────────────────────────────────────── -->
        <div class="photo-panel">
            <img src="<?= base_url('images/_P3A6815.jpg') ?>" alt="Ronceros Fotografía">
            <div class="photo-brand">
                <p class="photo-brand-name">Ronceros<br>Fotografía</p>
                <p class="photo-brand-sub">Estudio Profesional</p>
            </div>
        </div>

        <!-- ── PANEL FORMULARIO ───────────────────────────────────── -->
        <div class="form-panel">

            <p class="form-eyebrow">Bienvenido de vuelta</p>
            <h1 class="form-title">Iniciar sesión</h1>
            <p class="form-subtitle">Accede a tu cuenta para continuar</p>
            <div class="form-title-bar"></div>

            <!-- Alertas flash -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-login alert-error" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert-login alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= esc(session()->getFlashdata('warning')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert-login alert-info" role="alert">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><?= esc(session()->getFlashdata('info')) ?></span>
                </div>
            <?php endif; ?>

            <?php $validationErrors = session()->getFlashdata('errors'); ?>
            <?php if (!empty($validationErrors)): ?>
                <div class="alert-login alert-error" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <ul class="mb-0 ps-3" style="margin:0;">
                        <?php foreach ((array) $validationErrors as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form id="login-form" method="POST" action="<?= base_url('/login') ?>" novalidate autocomplete="on">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nombre_user" class="login-label">Usuario</label>
                    <input
                        type="text"
                        id="nombre_user"
                        name="nombre_user"
                        class="form-control login-input"
                        placeholder="tu.usuario"
                        value="<?= esc(old('nombre_user', '')) ?>"
                        autocomplete="username"
                        maxlength="50"
                        required
                        aria-describedby="error-user"
                    >
                    <div id="error-user" class="field-error" role="alert" aria-live="polite"></div>
                </div>

                <div class="mb-4">
                    <label for="password" class="login-label">Contraseña</label>
                    <div class="login-input-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control login-input"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            maxlength="100"
                            required
                            aria-describedby="error-pass"
                        >
                        <button type="button" class="toggle-password" id="toggle-pass" aria-label="Mostrar contraseña" tabindex="-1">
                            <i class="bi bi-eye" id="eye-icon"></i>
                        </button>
                    </div>
                    <div id="error-pass" class="field-error" role="alert" aria-live="polite"></div>
                </div>

                <button type="submit" id="btn-submit" class="btn-login">
                    <span id="btn-text">
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Ingresar
                    </span>
                    <span id="btn-loading" class="d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Verificando...
                    </span>
                </button>
            </form>

            <p class="form-footer">
                Ronceros Fotografía &copy; <?= date('Y') ?>
            </p>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    // ── TEMA ─────────────────────────────────────────────────────────────────
    const html      = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');

    function actualizarIcono(tema) {
        if (!themeIcon) return;
        themeIcon.className = tema === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
    actualizarIcono(html.getAttribute('data-theme') || 'light');

    document.getElementById('btn-theme').addEventListener('click', function () {
        const actual = html.getAttribute('data-theme') || 'light';
        const nuevo  = actual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme',    nuevo);
        html.setAttribute('data-bs-theme', nuevo);
        localStorage.setItem('theme', nuevo);
        actualizarIcono(nuevo);
    });

    // ── MOSTRAR / OCULTAR CONTRASEÑA ─────────────────────────────────────────
    const passInput  = document.getElementById('password');
    const eyeIcon    = document.getElementById('eye-icon');
    const togglePass = document.getElementById('toggle-pass');

    togglePass.addEventListener('click', function () {
        const visible = passInput.type === 'text';
        passInput.type    = visible ? 'password' : 'text';
        eyeIcon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
        togglePass.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    // ── VALIDACIÓN FRONTEND ──────────────────────────────────────────────────
    const reUser  = /^[a-zA-Z0-9._\-]+$/;
    const rePass  = /^[\x20-\x7E]+$/;
    const reEmoji = /[\u{1F000}-\u{1FFFF}\u{2600}-\u{27FF}\u{FE00}-\u{FEFF}]/u;

    const userInput = document.getElementById('nombre_user');
    const errorUser = document.getElementById('error-user');
    const errorPass = document.getElementById('error-pass');

    function showError(el, msg) {
        el.innerHTML = msg ? `<i class="bi bi-exclamation-circle"></i> ${msg}` : '';
    }

    function validarUsuario(val) {
        if (!val)            return 'El usuario es obligatorio.';
        if (val.length < 4)  return 'Mínimo 4 caracteres.';
        if (val.length > 50) return 'Máximo 50 caracteres.';
        if (reEmoji.test(val)) return 'El usuario no puede contener emojis.';
        if (!reUser.test(val)) return 'Solo letras, números, puntos, guiones y guiones bajos.';
        return '';
    }

    function validarPassword(val) {
        if (!val)             return 'La contraseña es obligatoria.';
        if (val.length < 8)   return 'Mínimo 8 caracteres.';
        if (val.length > 100) return 'Máximo 100 caracteres.';
        if (reEmoji.test(val)) return 'La contraseña no puede contener emojis.';
        if (!rePass.test(val)) return 'La contraseña contiene caracteres no permitidos.';
        return '';
    }

    userInput.addEventListener('blur', function () {
        showError(errorUser, validarUsuario(this.value.trim()));
        this.classList.toggle('is-invalid', !!validarUsuario(this.value.trim()));
    });
    passInput.addEventListener('blur', function () {
        showError(errorPass, validarPassword(this.value));
        this.classList.toggle('is-invalid', !!validarPassword(this.value));
    });

    userInput.addEventListener('input', function () {
        if (errorUser.innerHTML) { showError(errorUser, ''); this.classList.remove('is-invalid'); }
    });
    passInput.addEventListener('input', function () {
        if (errorPass.innerHTML) { showError(errorPass, ''); this.classList.remove('is-invalid'); }
    });

    // ── SUBMIT ───────────────────────────────────────────────────────────────
    const form      = document.getElementById('login-form');
    const btnSubmit = document.getElementById('btn-submit');
    const btnText   = document.getElementById('btn-text');
    const btnLoad   = document.getElementById('btn-loading');

    form.addEventListener('submit', function (e) {
        const userVal = userInput.value.trim();
        const passVal = passInput.value;
        const errU    = validarUsuario(userVal);
        const errP    = validarPassword(passVal);

        showError(errorUser, errU);
        showError(errorPass, errP);
        userInput.classList.toggle('is-invalid', !!errU);
        passInput.classList.toggle('is-invalid', !!errP);

        if (errU || errP) {
            e.preventDefault();
            if (errU) userInput.focus();
            else      passInput.focus();
            return;
        }

        btnSubmit.disabled = true;
        btnText.classList.add('d-none');
        btnLoad.classList.remove('d-none');
    });

    // ── BLOQUEAR EMOJIS AL PEGAR ─────────────────────────────────────────────
    [userInput, passInput].forEach(function (inp) {
        inp.addEventListener('paste', function (e) {
            const txt = (e.clipboardData || window.clipboardData).getData('text');
            if (reEmoji.test(txt)) {
                e.preventDefault();
                const field = inp === userInput ? errorUser : errorPass;
                showError(field, 'No se permiten emojis.');
                inp.classList.add('is-invalid');
            }
        });
    });
})();
</script>
</body>
</html>
