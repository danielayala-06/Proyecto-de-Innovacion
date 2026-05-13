<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Ronceros Fotografía</title>

    <!-- Aplica el tema guardado antes de renderizar (evita parpadeo) -->
    <script>
        (function () {
            var t = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">

    <style>
        /* ── LOGIN PAGE ─────────────────────────────────────────────── */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-page);
            padding: 1.5rem;
        }

        .login-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
            animation: fadeInUp 0.3s ease;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
            margin-bottom: 0.25rem;
        }
        .login-brand i {
            font-size: 1.6rem;
            color: var(--accent);
        }

        .login-subtitle {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .login-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
        }

        .login-input {
            background-color: var(--bg-input) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            font-size: 0.9rem;
            padding: 0.6rem 0.85rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .login-input:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(180,144,64,.18) !important;
            outline: none;
        }
        .login-input.is-invalid {
            border-color: var(--red-border) !important;
        }
        .login-input::placeholder {
            color: var(--text-placeholder) !important;
        }

        .login-input-group {
            position: relative;
        }
        .login-input-group .login-input {
            padding-right: 2.5rem;
        }
        .toggle-password {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            transition: color 0.15s;
        }
        .toggle-password:hover { color: var(--accent); }

        .btn-login {
            background: var(--accent);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem;
            border-radius: 8px;
            width: 100%;
            letter-spacing: 0.3px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        }
        .btn-login:hover:not(:disabled) {
            background: var(--accent-hover);
            box-shadow: 0 4px 12px rgba(180,144,64,.35);
            transform: translateY(-1px);
        }
        .btn-login:active:not(:disabled) { transform: translateY(0); }
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .field-error {
            font-size: 0.75rem;
            color: var(--red-text);
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .alert-login {
            border-radius: 10px;
            font-size: 0.82rem;
            padding: 0.7rem 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .alert-login i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        .alert-error {
            background: var(--red-bg);
            border: 1px solid var(--red-border);
            color: var(--red-text);
        }
        .alert-warning {
            background: var(--amber-bg);
            border: 1px solid var(--amber-border);
            color: var(--amber-text);
        }
        .alert-info {
            background: var(--blue-bg);
            border: 1px solid var(--blue-border);
            color: var(--blue-text);
        }

        .btn-theme-login {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            font-size: 1rem;
        }
        .btn-theme-login:hover {
            background: var(--bg-hover);
            color: var(--accent);
        }

        .divider-text {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-align: center;
            margin: 1.5rem 0 0;
        }
    </style>
</head>
<body>

<!-- Botón de tema flotante -->
<button class="btn-theme-login" id="btn-theme" aria-label="Cambiar tema" title="Cambiar tema">
    <i id="theme-icon" class="bi bi-moon-fill"></i>
</button>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Brand -->
        <div class="mb-1">
            <span class="login-brand">
                <i class="bi bi-aperture"></i>
                Ronceros Fotografía
            </span>
            <p class="login-subtitle">Accede a tu cuenta para continuar</p>
        </div>

        <!-- Alertas flash -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-login alert-error mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert-login alert-warning mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= esc(session()->getFlashdata('warning')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert-login alert-info mb-3" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                <span><?= esc(session()->getFlashdata('info')) ?></span>
            </div>
        <?php endif; ?>

        <!-- Errores de validación múltiples -->
        <?php $validationErrors = session()->getFlashdata('errors'); ?>
        <?php if (!empty($validationErrors)): ?>
            <div class="alert-login alert-error mb-3" role="alert">
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

            <!-- Usuario -->
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

            <!-- Contraseña -->
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

            <!-- Submit -->
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

        <p class="divider-text">
            Ronceros Fotografía &copy; <?= date('Y') ?>
        </p>
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
        passInput.type       = visible ? 'password' : 'text';
        eyeIcon.className    = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
        togglePass.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    // ── VALIDACIÓN FRONTEND ──────────────────────────────────────────────────
    // Regex usuario: solo a-z A-Z 0-9 . _ -
    const reUser   = /^[a-zA-Z0-9._\-]+$/;
    // Regex password: solo ASCII imprimible (0x20–0x7E), sin emojis ni multibyte
    const rePass   = /^[\x20-\x7E]+$/;
    // Detecta emojis (rangos Unicode principales)
    const reEmoji  = /[\u{1F000}-\u{1FFFF}\u{2600}-\u{27FF}\u{FE00}-\u{FEFF}]/u;

    const userInput   = document.getElementById('nombre_user');
    const errorUser   = document.getElementById('error-user');
    const errorPass   = document.getElementById('error-pass');

    function showError(el, msg) {
        el.innerHTML = msg ? `<i class="bi bi-exclamation-circle"></i> ${msg}` : '';
    }

    function validarUsuario(val) {
        if (!val) return 'El usuario es obligatorio.';
        if (val.length < 4) return 'Mínimo 4 caracteres.';
        if (val.length > 50) return 'Máximo 50 caracteres.';
        if (reEmoji.test(val)) return 'El usuario no puede contener emojis.';
        if (!reUser.test(val)) return 'Solo letras, números, puntos, guiones y guiones bajos.';
        return '';
    }

    function validarPassword(val) {
        if (!val) return 'La contraseña es obligatoria.';
        if (val.length < 8) return 'Mínimo 8 caracteres.';
        if (val.length > 100) return 'Máximo 100 caracteres.';
        if (reEmoji.test(val)) return 'La contraseña no puede contener emojis.';
        if (!rePass.test(val)) return 'La contraseña contiene caracteres no permitidos.';
        return '';
    }

    // Validación en tiempo real (on blur)
    userInput.addEventListener('blur', function () {
        showError(errorUser, validarUsuario(this.value.trim()));
        this.classList.toggle('is-invalid', !!validarUsuario(this.value.trim()));
    });

    passInput.addEventListener('blur', function () {
        showError(errorPass, validarPassword(this.value));
        this.classList.toggle('is-invalid', !!validarPassword(this.value));
    });

    // Limpiar error al escribir
    userInput.addEventListener('input', function () {
        if (errorUser.innerHTML) {
            showError(errorUser, '');
            this.classList.remove('is-invalid');
        }
    });
    passInput.addEventListener('input', function () {
        if (errorPass.innerHTML) {
            showError(errorPass, '');
            this.classList.remove('is-invalid');
        }
    });

    // ── SUBMIT ───────────────────────────────────────────────────────────────
    const form      = document.getElementById('login-form');
    const btnSubmit = document.getElementById('btn-submit');
    const btnText   = document.getElementById('btn-text');
    const btnLoad   = document.getElementById('btn-loading');

    form.addEventListener('submit', function (e) {
        const userVal = userInput.value.trim();
        const passVal = passInput.value;

        const errU = validarUsuario(userVal);
        const errP = validarPassword(passVal);

        showError(errorUser, errU);
        showError(errorPass, errP);

        userInput.classList.toggle('is-invalid', !!errU);
        passInput.classList.toggle('is-invalid', !!errP);

        if (errU || errP) {
            e.preventDefault();
            // Enfocar el primer campo con error
            if (errU) userInput.focus();
            else       passInput.focus();
            return;
        }

        // Estado de carga para evitar doble envío
        btnSubmit.disabled = true;
        btnText.classList.add('d-none');
        btnLoad.classList.remove('d-none');
    });

    // ── BLOQUEAR PEGAR EMOJIS ────────────────────────────────────────────────
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
