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

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* ── FONDO DE PÁGINA ─────────────────────────────────────────── */
        .login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: #F0EDE7;
            position: relative;
            overflow: hidden;
        }

        /* Suave viñeta de fondo */
        .login-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 70% at 50% 50%,
                transparent 40%,
                rgba(0,0,0,.08) 100%);
            pointer-events: none;
        }

        [data-theme="dark"] .login-bg {
            background: #111009;
        }
        [data-theme="dark"] .login-bg::before {
            background: radial-gradient(ellipse 80% 70% at 50% 50%,
                transparent 40%,
                rgba(0,0,0,.5) 100%);
        }

        /* ── TARJETA POLAROID ────────────────────────────────────────── */
        .login-card {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 900px;
            min-height: 580px;
            border-radius: 24px;
            overflow: hidden;
            /* Borde dorado grueso — el efecto polaroid de la referencia */
            outline: 4px solid #D4A030;
            outline-offset: 0;
            box-shadow:
                0 0 0 8px rgba(212,160,48,.18),
                0 24px 64px rgba(0,0,0,.22),
                0 8px 24px rgba(0,0,0,.14);
            animation: cardIn 0.5s cubic-bezier(.22,.68,0,1.2) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
            to   { opacity: 1; transform: translateY(0)   scale(1);    }
        }

        /* ── PANEL FOTO (izquierdo) ──────────────────────────────────── */
        .photo-panel {
            position: relative;
            overflow: hidden;
            min-height: 420px;
        }

        .photo-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
        }

        /* Overlay degradado para que el texto sea legible */
        .photo-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(10, 8, 5, .10) 0%,
                rgba(10, 8, 5, .05) 40%,
                rgba(10, 8, 5, .55) 100%
            );
        }

        .photo-brand {
            position: absolute;
            bottom: 1.75rem;
            left: 1.75rem;
            right: 1.75rem;
            z-index: 1;
        }

        .photo-brand-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            text-shadow: 0 2px 12px rgba(0,0,0,.4);
        }

        .photo-brand-sub {
            font-size: 0.72rem;
            color: rgba(255,255,255,.75);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 0.3rem;
            text-shadow: 0 1px 6px rgba(0,0,0,.4);
        }

        /* Línea dorada decorativa sobre el texto */
        .photo-brand::before {
            content: '';
            display: block;
            width: 36px;
            height: 2.5px;
            background: #D4A030;
            border-radius: 2px;
            margin-bottom: 0.65rem;
        }

        /* ── PANEL FORMULARIO (derecho) ──────────────────────────────── */
        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.75rem 2.25rem;
            background: #FAFAF8;
        }

        [data-theme="dark"] .form-panel {
            background: #1A1710;
        }

        /* Encabezado del formulario */
        .form-eyebrow {
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #B49040;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .form-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: #1C1916;
            line-height: 1.2;
            margin-bottom: 0.3rem;
        }

        [data-theme="dark"] .form-title { color: #F0E8D4; }

        .form-subtitle {
            font-size: 0.8rem;
            color: #7C7468;
            margin-bottom: 0;
        }

        [data-theme="dark"] .form-subtitle { color: #8A8278; }

        .form-title-bar {
            width: 32px;
            height: 3px;
            background: linear-gradient(90deg, #B49040, #D4A84B);
            border-radius: 2px;
            margin: 0.8rem 0 1.6rem;
        }

        /* Inputs */
        .login-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #4E4840;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.3rem;
            display: block;
        }

        [data-theme="dark"] .login-label { color: #9E9488; }

        .login-input {
            background: #F0EDE7 !important;
            border: 1.5px solid #D6D0C8 !important;
            color: #1C1916 !important;
            border-radius: 10px !important;
            font-size: 0.875rem;
            padding: 0.62rem 0.85rem;
            width: 100%;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        [data-theme="dark"] .login-input {
            background: #221E16 !important;
            border-color: #3A3428 !important;
            color: #F0E8D4 !important;
        }

        .login-input:focus {
            border-color: #B49040 !important;
            box-shadow: 0 0 0 3px rgba(180,144,64,.14) !important;
            outline: none;
            background: #FBF9F5 !important;
        }

        [data-theme="dark"] .login-input:focus {
            background: #2A2418 !important;
        }

        .login-input.is-invalid { border-color: #C84040 !important; }

        .login-input::placeholder { color: #B4ACA4 !important; }

        [data-theme="dark"] .login-input::placeholder { color: #504840 !important; }

        .login-input-group { position: relative; }
        .login-input-group .login-input { padding-right: 2.8rem; }

        .toggle-password {
            position: absolute;
            right: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #B4ACA4;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            transition: color 0.15s;
        }
        .toggle-password:hover { color: #B49040; }

        /* Botón */
        .btn-login {
            background: linear-gradient(135deg, #B49040 0%, #C8A048 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.7rem;
            border-radius: 10px;
            width: 100%;
            letter-spacing: 0.04em;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(180,144,64,.35);
        }
        .btn-login:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(180,144,64,.45);
        }
        .btn-login:active:not(:disabled) { transform: none; }
        .btn-login:disabled { opacity: 0.55; cursor: not-allowed; }

        /* Errores */
        .field-error {
            font-size: 0.72rem;
            color: #C84040;
            margin-top: 0.28rem;
            display: flex;
            align-items: center;
            gap: 4px;
            min-height: 1rem;
        }

        /* Alertas flash */
        .alert-login {
            border-radius: 10px;
            font-size: 0.78rem;
            padding: 0.6rem 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 1rem;
        }
        .alert-login i { font-size: 0.9rem; flex-shrink: 0; margin-top: 1px; }
        .alert-error   { background: #F8E8E8; border: 1px solid #E4A0A0; color: #881818; }
        .alert-warning { background: #F8F0D8; border: 1px solid #E4C060; color: #7A5000; }
        .alert-info    { background: #E4EDF8; border: 1px solid #9CBEEC; color: #1A3878; }

        [data-theme="dark"] .alert-error   { background: #2C1414; border-color: #6A3030; color: #F0A0A0; }
        [data-theme="dark"] .alert-warning { background: #2C2410; border-color: #6A5A20; color: #F0D080; }
        [data-theme="dark"] .alert-info    { background: #101C30; border-color: #3060A0; color: #90C0F0; }

        /* Pie */
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.68rem;
            color: #B4ACA4;
            letter-spacing: 0.04em;
        }

        [data-theme="dark"] .form-footer { color: #4A4540; }

        /* Botón tema */
        .btn-theme-login {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 200;
            background: rgba(250,250,248,.85);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(180,144,64,.4);
            color: #B49040;
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.15s;
        }
        .btn-theme-login:hover { background: rgba(180,144,64,.12); }

        [data-theme="dark"] .btn-theme-login {
            background: rgba(26,23,16,.85);
            border-color: rgba(180,144,64,.35);
        }

        /* ── RESPONSIVE ──────────────────────────────────────────────── */
        @media (max-width: 680px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 420px;
                outline-width: 3px;
            }
            .photo-panel {
                min-height: 220px;
            }
            .form-panel {
                padding: 2rem 1.5rem;
            }
        }
    </style>
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
