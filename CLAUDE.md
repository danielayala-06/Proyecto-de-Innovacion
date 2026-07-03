# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

bash
# Start dev server (port 8080)
php spark serve

# Database
php spark migrate
php spark db:seed DatabaseSeeder

# Run tests
./vendor/bin/phpunit

# Clear throttler cache (use when login is blocked during dev)
rm -f writable/cache/throttler_*


## Architecture

The app is a CodeIgniter 4 MVC system for managing photography studio quotes, contracts, clients, and packages (Ronceros Fotografía). It follows a strict layered backend with a modular ES6 frontend.

### Backend layers

*Web routes* → Controllers/ (renders views) → view('Layouts/header') + view('Layouts/footer') injected as strings in $data.

*API routes* (api/*) → Controllers/Api/ → Services/ (business logic, DB transactions) → Models/ → Transformers/ (format JSON response).

All API responses follow { status: 'success'|'error', data: ..., message: ... }. Services throw \RuntimeException with HTTP code as $code (404/409/422/500); the API controller maps these to HTTP status codes via _serviceError().

### Auth system

- AuthFilter (app/Filters/AuthFilter.php) — blocks unauthenticated requests; returns 401 JSON for api/*, redirects to /login for web routes.
- AuthController — handles login/logout with bcrypt, CI4 Throttler (10 attempts/IP, 5 attempts/username, 15-min lockout), and session()->regenerate(true) on login.
- All routes except /login and /logout are protected via ['filter' => 'auth'] group in Routes.php.
- CSRF is global for web, excluded for api/* in Filters.php.
- Session data set on login: logged_in, usuario_id, nombre_user, nombres, apellidos, id_rol, rol.

### Frontend modules

Each feature domain under public/js/modules/<domain>/ follows the same split:
- <domain>.state.js — shared state, pure filter/sort/group functions, stat calculators
- <domain>.ui.js — DOM rendering (no fetch)
- <domain>.form.js — modal form read/write
- <domain>Main.js — wires state + ui + form + api, exposes window.* functions called from the view

public/js/api/<domain>.api.js wraps http.js (which handles BASE_URL, JSON headers, error normalization). Views declare <script>const BASE_URL = "<?= base_url('') ?>"</script> before loading the module entry point.

Shared utilities: utils/alerts.js (toast notifications), utils/formatters.js (moneda, fecha, estado, codigo), utils/http.js.

### Views

Views are plain PHP files. The layout is assembled manually — each controller passes 'header' => view('Layouts/header') and 'footer' => view('Layouts/footer') in $data, then the view echoes <?= $header ?> / <?= $footer ?>. The IDE flags $header/$footer as undefined — these are false positives.

The auth login view (app/Views/auth/login.php) is standalone (no layout injection).

### Naming convention: Catálogo vs paquetes

La sección que el usuario ve como **"Catálogo"** (ruta `/catalogo`) corresponde internamente al dominio `paquetes` (tabla `paquetes`, `PaqueteController`, `PaqueteService`, `PaquetesModel`, `paquete.api.js`, etc.). No renombrar el código interno — solo la UI y la ruta cambiaron.

### CSS

Single stylesheet: public/css/styles.css. Uses CSS custom properties for theming (data-theme="dark|light" on <html>). Theme is persisted in localStorage and applied before first paint via an inline script in header.php.

## Database

- Driver: MySQLi. Config in .env (database.default.*).
- $indexPage = 'index.php' in app/Config/App.php — CI4's site_url() includes index.php in dev; base_url() does not.
- Run all seeders via DatabaseSeeder, which calls individual seeders in dependency order.

*Seeded test users:*

| Username | Password | Role |
|---|---|---|
| carlos.admin | Admin1234! | Administrador |
| maria.ventas | Ventas123! | Vendedor |
| jorge.foto | Foto1234! | Fotógrafo |
| ana.supervisor | Super123! | Supervisor |

## Key constraints

- nombre_user is the login field (not email, not nom_user). Allowed chars: [a-zA-Z0-9._-].
- Passwords: printable ASCII only ([\x20-\x7E]), min 8 chars.
- paquetes.nivel_disponible enum: inicial, primaria, secundaria, postgrado, otro. Inicial and primaria are distinct levels with separate product catalogs. The frontend groups and sorts packages by this field; inactive ones always sort last within each group.
- NIVEL_ORDER in paquete.state.js defines the display order — keep it consistent with the enum.
- reglas_paquetes.tipo_condicion enum: CANTIDAD_MIN, CANTIDAD_MAX, ELEGIBILIDAD_MIN. ELEGIBILIDAD_MIN fires when the group is smaller than the required minimum (blocks the sale); CANTIDAD_MIN fires when the threshold is met (unlocks a benefit); CANTIDAD_MAX fires when the limit is exceeded (violation).
- The external RENIEC API key is in .env as DECOLECTA.KEY.

## Pendientes

- **Donald Scarrow 6to — precio de sesiones**: el seeder `ContratosRealSeeder` asignó S/ 210 a "2 Sesiones fotográficas" para cuadrar el total de S/ 3150 (14×140 + 7×140 = 2940; diferencia = 210). Confirmar con el usuario si ese monto es correcto o si las sesiones tienen otro precio, y actualizar el detalle en `cotizaciones_detalles` para la cotización correspondiente (contrato #9).