# Ronceros Fotografía — Sistema de Gestión

> Sistema de gestión integral para estudio fotográfico escolar: cotizaciones, contratos, sesiones, asistencia, pagos e inscripción de apoderados.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.7-EF4223?style=flat-square&logo=codeigniter)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11_LTS-003545?style=flat-square&logo=mariadb)
![Platform](https://img.shields.io/badge/platform-Linux%20%7C%20Windows-blue?style=flat-square)
![Version](https://img.shields.io/badge/versión-1.0.0-brightgreen?style=flat-square)
![License](https://img.shields.io/badge/licencia-MIT-yellow?style=flat-square)

---

## Índice

1. [Descripción](#1-descripción)
2. [Características](#2-características)
3. [Stack tecnológico](#3-stack-tecnológico)
4. [Arquitectura](#4-arquitectura)
5. [Inicio rápido — Desarrollo](#5-inicio-rápido--desarrollo)
6. [Instalación en Windows — Producción](#6-instalación-en-windows--producción)
7. [Roles y usuarios de prueba](#7-roles-y-usuarios-de-prueba)
8. [Módulos del sistema](#8-módulos-del-sistema)
9. [API REST](#9-api-rest)
10. [Actualizaciones sin servidor](#10-actualizaciones-sin-servidor)
11. [Variables de entorno](#11-variables-de-entorno)
12. [Estructura del proyecto](#12-estructura-del-proyecto)
13. [Tests](#13-tests)

---

## 1. Descripción

**Ronceros Fotografía** es una aplicación web MVC construida sobre **CodeIgniter 4** para gestionar el flujo completo de un estudio fotográfico escolar:

- El equipo de **ventas** crea cotizaciones con paquetes del catálogo, aplica descuentos y las convierte en contratos formales.
- El equipo de **fotografía** programa sesiones por promoción escolar, controla la asistencia de cada alumno en tiempo real y gestiona el calendario de trabajo.
- Los **apoderados** completan un formulario de inscripción desde su celular, sin necesidad de cuenta ni aplicación.
- El **administrador** supervisa pagos, reportes y la configuración general del sistema.

El sistema es **multiusuario con control de roles**, soporta modo oscuro/claro persistente, y cuenta con un **instalador gráfico para Windows** y un **actualizador automático** de un solo clic.

---

## 2. Características

### Ventas y contratos
- Crear cotizaciones con múltiples ítems: paquetes del catálogo, productos/cortesías e ítems personalizados con precio libre.
- Aplicar descuentos por monto fijo.
- Gestionar estados: `PENDIENTE → APROBADA → RECHAZADA / EXPIRADA`.
- Convertir una cotización aprobada en contrato con un solo clic.
- Registrar pagos parciales o totales (Efectivo, Yape, Plin, transferencia, etc.) con número de voucher.
- Consultar automáticamente datos del cliente con su DNI usando la **API RENIEC**.

### Gestión escolar
- Programar sesiones fotográficas por promoción (exteriores, colegio, estudio, otro).
- Detectar conflictos de horario automáticamente.
- Marcar asistencia por estudiante y sesión con un clic (asistió / faltó / sin marcar).
- Ver estadísticas de asistencia en tiempo real.
- Exportar e importar la lista de estudiantes de una promoción en formato **CSV**.
- Gestionar calendario mensual de sesiones.

### Inscripción de apoderados
- Generar un link único por alumno.
- El apoderado llena el formulario desde cualquier dispositivo sin cuenta.
- Registrar datos del alumno, apoderado, consentimientos de imagen, preferencia de cuadro/anuario.
- Ver el avance de formularios completados vs. enviados en tiempo real.

### Catálogo
- Administrar paquetes fotográficos por nivel: inicial, primaria, secundaria, postgrado.
- Asociar productos (álbumes, impresiones) y tipos de sesión a cada paquete.
- Subir imágenes de referencia por paquete.
- Activar/desactivar paquetes sin eliminarlos.

### Autenticación y seguridad
- Login con **bcrypt** y protección anti-fuerza bruta (CI4 Throttler: 10 intentos/IP, bloqueo 15 min).
- **"Mantener sesión iniciada"**: cookie persistente de 30 días con token SHA-256 en base de datos.
- Protección CSRF en todas las rutas web.
- Regeneración de sesión al autenticarse para prevenir session fixation.

### Instalación y mantenimiento
- **Instalador gráfico para Windows** (`INSTALAR.bat`): descarga e instala PHP 8.2, MariaDB 10.11 y Nginx 1.26 si no están presentes, configura servicios de Windows y ejecuta migraciones automáticamente.
- **Actualizador de un clic** (`ACTUALIZAR.bat`): comprueba GitHub Releases, descarga la nueva versión, migra la base de datos y reinicia los servicios.

---

## 3. Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP + CodeIgniter 4 | PHP 8.2 / CI4 7.x |
| Base de datos | MariaDB (driver MySQLi) | 10.11 LTS |
| Servidor web (prod) | Nginx + PHP-CGI / PHP-FPM | 1.26.x |
| Frontend | ES6 Modules nativos (sin build step) | — |
| UI / componentes | Bootstrap 5 + Bootstrap Icons | 5.3 |
| Generación PDF | dompdf | 3.x |
| Hojas de cálculo | PhpSpreadsheet | 5.x |
| API externa | RENIEC (decolecta.net) | — |
| Instalador Windows | PowerShell WinForms + NSIS | — |

---

## 4. Arquitectura

### Capas del backend

```
Navegador
  └── ES6 Modules (state · ui · form · Main)
       └── fetch → api/* | formulario HTML → web/*
              │
              ▼
         AuthFilter ──── sin sesión → 401 JSON (api) / redirect /login (web)
              │
    ┌─────────┴──────────┐
    │  Web Routes        │  API Routes (api/*)
    │  Controllers/      │  Controllers/Api/
    │  (renderiza vistas)│  (JSON { status, data, message })
    └─────────┬──────────┘
              │
         Services/
         (lógica de negocio · transacciones DB)
         lanza RuntimeException($msg, $httpCode)
              │
          Models/
          (CI4 Model — MySQLi)
              │
        Transformers/
        (formato JSON de salida)
```

### Convención de respuesta API

```json
{
  "status": "success" | "error",
  "data":   { ... } | null,
  "message": "Descripción del resultado"
}
```

Los errores de `Service` se mapean a HTTP 4xx/5xx mediante `BaseApiController::_serviceError()`.

### Frontend por dominio

Cada módulo funcional vive en `public/js/modules/<dominio>/`:

| Archivo | Responsabilidad |
|---|---|
| `<dominio>.state.js` | Estado compartido, funciones puras (filtrar, ordenar, calcular) |
| `<dominio>.ui.js` | Renderizado del DOM — sin llamadas a la red |
| `<dominio>.form.js` | Lectura y escritura de formularios / modales |
| `<dominio>Main.js` | Punto de entrada: conecta state + ui + form + api |

Las llamadas HTTP se centralizan en `public/js/api/<dominio>.api.js`, que envuelve `utils/http.js`.

---

## 5. Inicio rápido — Desarrollo

### Requisitos previos

- PHP 8.2+ con extensiones: `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `zip`
- MariaDB 10.6+ o MySQL 8+
- Composer 2.x

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/daniel-06/ronceros-fotografia.git
cd ronceros-fotografia

# 2. Instalar dependencias PHP
composer install

# 3. Configurar el entorno
cp env .env
# Editar .env: base de datos, URL base, DECOLECTA.KEY

# 4. Crear la base de datos y ejecutar migraciones
php spark migrate

# 5. Cargar datos de prueba (usuarios, paquetes de ejemplo)
php spark db:seed DatabaseSeeder

# 6. Iniciar el servidor de desarrollo
php spark serve
# → http://localhost:8080
```

### Comandos útiles durante el desarrollo

```bash
# Limpiar bloqueo del throttler (si el login queda bloqueado en dev)
rm -f writable/cache/throttler_*

# Ver rutas registradas
php spark routes

# Crear una migración nueva
php spark make:migration NombreMigracion

# Ejecutar tests
./vendor/bin/phpunit
```

---

## 6. Instalación en Windows — Producción

El sistema incluye un instalador gráfico que automatiza todo el proceso.

### Opción A — Instalador todo-en-uno `.exe` (recomendado)

1. Descarga `RoncerosFotografia-Setup.exe` desde la sección [Releases](../../releases/latest).
2. Ejecuta el instalador como administrador.
3. Sigue el asistente (elige directorio de instalación → siguiente → terminar).
4. Marca la casilla **"Instalar servicios (PHP, MariaDB, Nginx)"** en la última pantalla.
5. En la ventana que aparece, ajusta los datos de la base de datos y haz clic en **Instalar**.

El instalador descarga e instala automáticamente PHP 8.2, MariaDB 10.11 y Nginx 1.26 si no están presentes en el equipo.

### Opción B — Instalación manual desde el código fuente

Si ya tienes el repositorio en el equipo Windows:

```
Doble clic en:  installer\INSTALAR.bat
```

La ventana gráfica detecta qué componentes faltan, los descarga e instala, y configura todo.

### Servicios de Windows creados

| Servicio | Descripción | Puerto |
|---|---|---|
| `Ronceros-MariaDB` | Base de datos MariaDB 10.11 | 3306 |
| `Ronceros-PHP` | PHP-CGI FastCGI (gestionado por NSSM) | 9000 |
| `Ronceros-Nginx` | Servidor web Nginx | 80 |

Los tres servicios se configuran con **inicio automático** y sobreviven reinicios del sistema.

### Archivos de control en producción

| Archivo | Función |
|---|---|
| `INICIAR.bat` | Inicia los 3 servicios y abre http://localhost en el navegador |
| `DETENER.bat` | Detiene los 3 servicios |
| `ACTUALIZAR.bat` | Comprueba y aplica actualizaciones automáticamente |

### Compilar el instalador `.exe` desde el código fuente

```bash
# En Linux (requiere NSIS)
sudo apt install nsis
makensis installer/setup.nsi
# Salida: RoncerosFotografia-Setup.exe
```

```batch
:: En Windows (requiere Inno Setup 6 o ps2exe)
installer\COMPILAR.bat
```

---

## 7. Roles y usuarios de prueba

Los siguientes usuarios se crean automáticamente al ejecutar `DatabaseSeeder`:

| Usuario | Contraseña | Rol | Acceso |
|---|---|---|---|
| `quique.admin` | `Admin1234!` | Administrador | Acceso total: reportes, configuración, todos los módulos |
| `maria.ventas` | `Ventas123!` | Vendedor | Cotizaciones, contratos, clientes, catálogo |
| `jorge.foto` | `Foto1234!` | Fotógrafo | Sesiones, asistencia, calendario |
| `ana.supervisor` | `Super123!` | Supervisor | Vista de reportes y monitoreo general |

> El campo de login es `nombre_user`, no el correo electrónico.

---

## 8. Módulos del sistema

### Cotizaciones `/cotizaciones`

Presupuestos previos al contrato. Permiten agregar paquetes del catálogo, servicios, ítems personalizados y un descuento opcional.

**Estados:** `PENDIENTE → APROBADA → RECHAZADA / EXPIRADA`

Una cotización aprobada se convierte en contrato con un clic, sin reingreso de datos.

---

### Contratos `/contratos`

Formalización del acuerdo con el cliente. Incluye registro de pagos parciales, notas, estado general y la promoción escolar asociada.

**Estados:** `ACTIVO → COMPLETADO / CANCELADO`

**Medios de pago registrables:** Efectivo, Yape, Plin, Transferencia, o cualquier texto libre.

---

### Clientes `/clientes`

Directorio de clientes con datos personales (obtenibles automáticamente ingresando el DNI), contacto, red social y preferencia de comunicación.

---

### Catálogo `/catalogo`

Gestión de paquetes fotográficos por nivel escolar. Cada paquete puede incluir productos (álbumes, impresiones) y tipos de sesión (exteriores, colegio, estudio).

> Internamente el dominio se llama `paquetes`. Solo la UI y la ruta cambiaron a "Catálogo".

---

### Sesiones fotográficas `/sesiones`

Programación de sesiones por promoción. Detecta conflictos de horario. El fotógrafo marca el estado de cada sesión y registra asistencia por alumno.

---

### Asistencia

Integrada en la vista de sesiones. Tabla de doble entrada: filas = alumnos, columnas = sesiones. Cada celda es un toggle (asistió / faltó / sin marcar). Las estadísticas se actualizan en tiempo real.

---

### Formularios de apoderados `/formulario/:token`

Link único por alumno para que el apoderado llene sus datos sin necesidad de cuenta. El formulario recoge datos del alumno, preferencias fotográficas y consentimientos legales.

---

### Estudiantes

Gestión de la lista de alumnos por promoción con importación y exportación en formato CSV.

---

### Calendario `/calendario`

Vista mensual de todas las sesiones programadas en el estudio.

---

### Reportes `/admin/reporte`

Disponible solo para Administrador y Supervisor: ventas por período, contratos activos, resumen de pagos.

---

## 9. API REST

Todas las rutas requieren sesión activa. Las rutas `api/*` están excluidas de CSRF pero protegidas por `AuthFilter`, que devuelve `HTTP 401` si no hay sesión.

### Resumen de endpoints

| Recurso | Métodos disponibles |
|---|---|
| `api/clientes` | GET, GET `:id`, POST, PUT `:id`, DELETE `:id` |
| `api/colegios` | GET, PUT `:id` |
| `api/cotizaciones` | GET, GET `:id`, POST, PUT `:id`, DELETE `:id`, PUT `:id/estado`, PUT `:id/archivar` |
| `api/contratos` | GET, GET `:id`, POST, PUT `:id`, DELETE `:id`, PUT `:id/estado`, PUT `:id/archivar` |
| `api/pagos` | GET `?id_contrato=`, POST, DELETE `:id` |
| `api/paquetes` | GET, GET `:id`, POST, PUT `:id`, DELETE `:id`, PUT `:id/estado`, POST `:id/imagen` |
| `api/productos` | GET |
| `api/sesiones` | GET `?id_contrato=`, POST, PUT `:id`, DELETE `:id`, PUT `:id/estado`, POST `:id/asistencia` |
| `api/estudiantes` | GET `?id_promocion=`, POST, PUT `:id`, DELETE `:id` |
| `api/promociones` | GET, POST, PUT `:id`, DELETE `:id`, PUT `:id/activar` |
| `api/reniec/dni` | GET `?dni=` |

### Formato de respuesta

```json
{
  "status":  "success",
  "data":    { "id_cotizacion": 42, "total_estimado": 1500.00 },
  "message": "Cotización creada exitosamente"
}
```

```json
{
  "status":  "error",
  "data":    null,
  "message": "El cliente no existe"
}
```

---

## 10. Actualizaciones sin servidor

El sistema incluye un mecanismo de actualización automática basado en **GitHub Releases**. No requiere VPS, git en el cliente, ni intervención técnica del usuario final.

### Flujo del desarrollador (publicar una actualización)

```bash
# 1. Hacer los cambios y commitear
git add .
git commit -m "feat: descripción de la mejora"

# 2. Crear un tag con la nueva versión
git tag v1.1.0
git push origin main --tags

# 3. Publicar el release en GitHub
gh release create v1.1.0 --title "Versión 1.1.0" \
  --notes "- Corrección en X\n- Mejora en Y"
```

GitHub genera automáticamente el ZIP del código fuente.

### Flujo del usuario final (aplicar la actualización)

1. Doble clic en **`ACTUALIZAR.bat`**.
2. La ventana muestra la versión instalada y la versión disponible, junto con las notas del release.
3. Clic en **Actualizar**.
4. El sistema se detiene, descarga el ZIP, copia los nuevos archivos, ejecuta las migraciones y reinicia los servicios.

### Configuración del actualizador

Antes de distribuir el instalador, editar las líneas 9–11 de `installer/actualizar-gui.ps1`:

```powershell
$GITHUB_OWNER = 'tu-usuario-github'
$GITHUB_REPO  = 'nombre-del-repositorio'
$GITHUB_TOKEN = ''  # Token PAT si el repo es privado
```

---

## 11. Variables de entorno

El archivo `.env` (no versionado) contiene la configuración del entorno. Se crea copiando `env`:

```bash
cp env .env
```

### Variables principales

```ini
# Entorno
CI_ENVIRONMENT = development  # production en Windows

# URL base de la aplicación
app.baseURL = 'http://localhost:8080/'

# Base de datos
database.default.hostname = localhost
database.default.database = ronceros_foto
database.default.username = ronceros
database.default.password = tu_password
database.default.DBDriver = MySQLi
database.default.port     = 3306

# API RENIEC (consulta de DNI)
DECOLECTA.KEY = sk_xxxxx.xxxxxxxxxx

# Google Calendar (opcional — dejar vacío para desactivar)
# GOOGLE_CREDENTIALS_PATH = writable/google-credentials.json
# GOOGLE_CALENDAR_ID      = primary
```

### Configurar Google Calendar (opcional)

El sistema puede sincronizar sesiones fotográficas con Google Calendar mediante una **cuenta de servicio** (sin intervención del usuario):

1. Crear un proyecto en [Google Cloud Console](https://console.cloud.google.com).
2. Activar la **API Google Calendar**.
3. Crear una **Cuenta de servicio** y descargar su clave JSON.
4. Guardar el JSON en `writable/google-credentials.json`.
5. En Google Calendar → Configuración → Compartir con personas → agregar el correo de la cuenta de servicio con permiso **"Hacer cambios en eventos"**.
6. Activar en `.env`:
   ```ini
   GOOGLE_CREDENTIALS_PATH = writable/google-credentials.json
   GOOGLE_CALENDAR_ID      = tu_correo@gmail.com
   ```

La integración es **fail-silent**: si falla por cualquier motivo, el sistema sigue funcionando normalmente.

---

## 12. Estructura del proyecto

```
ronceros-fotografia/
│
├── app/
│   ├── Config/                  # Configuración de CI4 (rutas, filtros, base de datos)
│   ├── Controllers/             # Controladores web (renderizan vistas)
│   │   └── Api/                 # Controladores API (devuelven JSON)
│   ├── Database/
│   │   ├── Migrations/          # Migraciones ordenadas por fecha
│   │   └── Seeds/               # Seeders (DatabaseSeeder llama a todos)
│   ├── Filters/
│   │   └── AuthFilter.php       # Protección de rutas + validación de cookie remember-me
│   ├── Models/                  # Modelos CI4 (una clase por tabla)
│   ├── Services/                # Lógica de negocio y transacciones DB
│   │   ├── Auth/                # RememberTokenService
│   │   ├── Google/              # GoogleCalendarService (fail-silent)
│   │   └── Sesiones/            # SesionService, etc.
│   ├── Transformers/            # Formateo de respuestas JSON
│   └── Views/
│       ├── Layouts/             # header.php y footer.php (inyectados por cada controlador)
│       ├── auth/                # login.php (standalone, sin layout)
│       ├── cotizaciones/
│       ├── contratos/
│       ├── clientes/
│       ├── paquetes/            # Vista del catálogo (ruta: /catalogo)
│       ├── sesiones/
│       ├── calendario/
│       └── pdf/                 # Plantillas PDF (dompdf)
│
├── public/                      # Webroot (único directorio expuesto por Nginx)
│   ├── index.php                # Front controller de CI4
│   ├── css/
│   │   └── styles.css           # Único stylesheet con custom properties (modo oscuro/claro)
│   └── js/
│       ├── api/                 # <dominio>.api.js — wrap de HTTP por dominio
│       ├── modules/             # <dominio>/{state, ui, form, Main}.js por dominio
│       └── utils/               # http.js, alerts.js, formatters.js
│
├── installer/
│   ├── instalar-gui.ps1         # Instalador gráfico Windows (WinForms)
│   ├── actualizar-gui.ps1       # Actualizador gráfico Windows (GitHub Releases)
│   ├── setup.nsi                # Script NSIS → compila a .exe desde Linux
│   ├── setup.iss                # Script Inno Setup → compila a .exe desde Windows
│   ├── INSTALAR.bat             # Lanzador del instalador (sin necesitar .exe)
│   ├── COMPILAR.bat             # Genera el .exe (Inno Setup o ps2exe)
│   └── composer.phar            # Composer empaquetado para la instalación
│
├── scripts/
│   ├── start.ps1                # Inicia servicios en Windows
│   ├── stop.ps1                 # Detiene servicios en Windows
│   └── instalar-portable.ps1   # Instalador consola (alternativa a la GUI)
│
├── INICIAR.bat                  # Atajo de escritorio: inicia servicios + abre navegador
├── DETENER.bat                  # Atajo: detiene los 3 servicios
├── ACTUALIZAR.bat               # Atajo: abre el actualizador gráfico
├── version.txt                  # Versión instalada (leída por el actualizador)
├── spark                        # CLI de CodeIgniter 4
├── composer.json
└── .env                         # Configuración local (NO versionado)
```

---

## 13. Tests

```bash
# Ejecutar toda la suite
./vendor/bin/phpunit

# Ejecutar solo una clase
./vendor/bin/phpunit --filter NombreDeLaClase

# Con cobertura (requiere Xdebug)
./vendor/bin/phpunit --coverage-text
```

Los tests viven en `tests/` con soporte de `mikey179/vfsstream` para el sistema de archivos virtual y `fakerphp/faker` para datos de prueba.

---

## Licencia

MIT © Ronceros Fotografía

---

> Para documentación técnica detallada (esquema completo de base de datos, todos los endpoints con parámetros, estructura de módulos JS) ver [DOCUMENTACION.md](DOCUMENTACION.md).
