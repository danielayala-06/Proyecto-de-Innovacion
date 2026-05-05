# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de gestión de contratos y cotizaciones para "Ronceros", construido con **CodeIgniter 4.7** (PHP 8.2+) y MySQL. Arquitectura MVC clásica con una capa de API REST separada.

## Commands

```bash
# Instalar dependencias
composer install

# Iniciar servidor de desarrollo (http://localhost:8080)
php spark serve

# Ejecutar migraciones
php spark migrate

# Revertir migraciones
php spark migrate:rollback

# Cargar datos de prueba
php spark db:seed CotizacionesPruebas

# Crear nueva migración
php spark make:migration NombreMigracion

# Ejecutar tests (PHPUnit)
composer test
# O directamente:
./vendor/bin/phpunit

# Ejecutar un test específico
./vendor/bin/phpunit tests/Ruta/AlArchivoTest.php
./vendor/bin/phpunit --filter NombreDelTest
```

## Architecture

### MVC + API Layer

El proyecto tiene dos capas de controladores con responsabilidades distintas:

- **`app/Controllers/`** — Controladores web que renderizan vistas HTML (dashboard, formularios).
- **`app/Controllers/Api/`** — Controladores REST que devuelven JSON. Usan `ResponseTrait` de CodeIgniter para respuestas estandarizadas.

Los controladores web llaman a la API interna o directamente a los modelos; los controladores API reciben `$_POST`/`$_GET` y responden con `$this->respond()`.

### Authentication Flow

- `AuthController` gestiona login/logout con sesiones PHP del lado servidor.
- `AuthFilter` (`app/Filters/`) es el middleware que protege rutas autenticadas; se aplica en `app/Config/Filters.php`.
- Los passwords se verifican con `password_verify()`.
- Las sesiones se almacenan en `writable/session/`.

### Data Flow: Cotizaciones → Contratos

El flujo principal del negocio es:
1. Se crea una **cotización** (estado: PENDIENTE).
2. Se aprueba → cambia estado a APROBADA.
3. A partir de una cotización aprobada se genera un **contrato**.
4. Los contratos tienen pagos y reprogramaciones asociados.

### Models

Los modelos extienden `CodeIgniter\Model` y usan el Query Builder. Métodos clave a conocer:

| Modelo | Métodos importantes |
|--------|-------------------|
| `Cotizacion` | `getCotizacionesResumen()`, `getResumenGeneralCoti()` |
| `Contrato` | `contratosConCliente()`, `contratoConCliente($id)` |
| `Cliente` | `clientesWithPersona()` — join con tabla `personas` |
| `Usuario` | `findByUsername($username)` |

### Transformers

`app/Transformers/` contiene transformadores que formatean datos de modelos antes de devolverlos en las respuestas de la API (patrón Transformer). `CotizacionesTransformer` es el principal.

### Routes

Todas las rutas están en `app/Config/Routes.php`. Hay dos grupos:
- Rutas web (protegidas por `AuthFilter`)
- Rutas API bajo el prefijo `/api/`

### Database

- Driver: **MySQLi**
- Las migraciones están en `app/Database/Migrations/` en orden cronológico.
- El esquema incluye: `roles`, `personas`, `empresas`, `clientes`, `usuarios`, `servicios`, `productos`, `paquetes`, `cotizaciones`, `contratos`, `pagos`, `reprogramaciones`, `permisos`, `roles_permisos`, `promociones`.
- Los tests usan una base de datos SQLite en memoria (configurado en `phpunit.xml.dist`).

## Environment Setup

Copiar `env.example` a `.env` y configurar:

```env
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = nombre_bd
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306
```

La variable `DECOLECTA.KEY` es una clave de API de terceros utilizada en alguna funcionalidad del sistema.
