<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión — Ronceros Fotografía</title>
  <script>
    (function () {
      var t = localStorage.getItem('theme') ||
              (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
  <style>
    html, body {
      height: 100%;
      background: var(--bg-page);
    }
    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    .login-card {
      width: 100%;
      max-width: 400px;
    }
    .login-brand {
      text-align: center;
      margin-bottom: 2rem;
    }
    .login-brand-icon {
      font-size: 2.4rem;
      color: var(--accent);
      margin-bottom: 0.4rem;
    }
    .login-brand-name {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-primary);
      margin-bottom: 0.2rem;
    }
    .login-brand-sub {
      font-size: 0.8rem;
      color: var(--text-muted);
    }
    .login-box {
      background: var(--bg-elevated);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 2rem 2.2rem;
      box-shadow: 0 4px 32px rgba(0,0,0,0.07);
    }
    .login-box h2 {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 1.6rem;
    }
    .login-label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }
    .login-input-wrap {
      position: relative;
    }
    .login-input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 1rem;
      pointer-events: none;
    }
    .login-input-wrap .form-control {
      padding-left: 36px !important;
    }
    .login-input-wrap .form-control.has-toggle {
      padding-right: 42px !important;
    }
    .login-toggle-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      padding: 4px 6px;
      line-height: 1;
      transition: color 0.15s;
    }
    .login-toggle-btn:hover { color: var(--text-primary); }
    .login-alert {
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.82rem;
      margin-bottom: 1.2rem;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }
    .login-alert-error {
      background: var(--red-bg);
      color: var(--red-text);
      border: 1px solid var(--red-border);
    }
    .login-alert-warn {
      background: var(--amber-bg);
      color: var(--amber-text);
      border: 1px solid var(--amber-border);
    }
    .login-submit {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 10px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 0.88rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s;
      margin-top: 0.5rem;
    }
    .login-submit:hover:not(:disabled) { background: var(--accent-hover); }
    .login-submit:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    .login-footer {
      text-align: center;
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 1.5rem;
    }
    .invalid-feedback { display: none; font-size: 0.78rem; margin-top: 4px; color: var(--red-text); }
    .form-control.is-invalid { border-color: var(--red-border) !important; }
    .form-control.is-invalid + .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback { display: block; }
  </style>
</head>
<body>

<div class="login-wrap">
  <div class="login-card">

    <!-- Brand -->
    <div class="login-brand">
      <div class="login-brand-icon"><i class="bi bi-camera-fill"></i></div>
      <div class="login-brand-name">Ronceros Fotografía</div>
      <div class="login-brand-sub">Panel de administración</div>
    </div>

    <!-- Card -->
    <div class="login-box">
      <h2><i class="bi bi-box-arrow-in-right me-2" style="color:var(--accent);"></i>Iniciar sesión</h2>

      <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="login-alert login-alert-error">
          <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:1px;"></i>
          <span><?= esc($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($errors = session()->getFlashdata('errors')): ?>
        <div class="login-alert login-alert-warn">
          <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px;"></i>
          <div><?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div>
        </div>
      <?php endif; ?>

      <form id="loginForm" action="<?= base_url('/login/auth') ?>" method="POST" novalidate>

        <!-- Usuario -->
        <div style="margin-bottom:1rem;">
          <label class="login-label" for="nombre_user">Usuario</label>
          <div class="login-input-wrap">
            <span class="login-input-icon"><i class="bi bi-person"></i></span>
            <input type="text"
                   name="nombre_user"
                   id="nombre_user"
                   class="form-control"
                   placeholder="Nombre de usuario"
                   value="<?= old('nombre_user') ?>"
                   required
                   minlength="3"
                   maxlength="50"
                   autocomplete="username">
          </div>
          <div class="invalid-feedback">Ingresa tu nombre de usuario (mínimo 3 caracteres).</div>
        </div>

        <!-- Contraseña -->
        <div style="margin-bottom:1.6rem;">
          <label class="login-label" for="password">Contraseña</label>
          <div class="login-input-wrap">
            <span class="login-input-icon"><i class="bi bi-lock"></i></span>
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control has-toggle"
                   placeholder="Contraseña"
                   required
                   minlength="6"
                   autocomplete="current-password">
            <button type="button" class="login-toggle-btn" id="togglePassword" tabindex="-1" title="Mostrar/ocultar contraseña">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div class="invalid-feedback">Ingresa tu contraseña (mínimo 6 caracteres).</div>
        </div>

        <button type="submit" class="login-submit" id="btnLogin">
          <i class="bi bi-box-arrow-in-right"></i> Ingresar
        </button>

      </form>
    </div>

    <div class="login-footer">&copy; <?= date('Y') ?> Ronceros Fotografía</div>
  </div>
</div>

<script>
(function () {
    const form    = document.getElementById('loginForm');
    const userEl  = document.getElementById('nombre_user');
    const passEl  = document.getElementById('password');
    const btnEl   = document.getElementById('btnLogin');
    const toggle  = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    // Mostrar / ocultar contraseña
    toggle.addEventListener('click', function () {
        const show  = passEl.type === 'password';
        passEl.type = show ? 'text' : 'password';
        eyeIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        passEl.focus();
    });

    // Quitar estado inválido al escribir
    [userEl, passEl].forEach(function (el) {
        el.addEventListener('input', function () {
            el.classList.remove('is-invalid');
        });
    });

    // Validación cliente + estado de carga
    form.addEventListener('submit', function (e) {
        let valid = true;

        [userEl, passEl].forEach(function (el) {
            if (!el.checkValidity()) {
                el.classList.add('is-invalid');
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            return;
        }

        btnEl.disabled    = true;
        btnEl.innerHTML   = '<span class="spinner-border spinner-border-sm"></span>&nbsp;Ingresando...';
    });
})();
</script>

</body>
</html>
