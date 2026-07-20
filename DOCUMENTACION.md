# Ronceros Fotografía — Documentación del Sistema

> Sistema de gestión integral para estudio fotográfico: cotizaciones, contratos, sesiones escolares, pagos y control de asistencia.

---

## Índice

1. [Visión General](#1-visión-general)
2. [Stack Tecnológico](#2-stack-tecnológico)
3. [Arquitectura del Sistema](#3-arquitectura-del-sistema)
4. [Roles y Permisos](#4-roles-y-permisos)
5. [Autenticación y Seguridad](#5-autenticación-y-seguridad)
6. [Módulos del Sistema](#6-módulos-del-sistema)
7. [Flujo de Datos Principal](#7-flujo-de-datos-principal)
8. [API REST — Endpoints](#8-api-rest--endpoints)
9. [Esquema de Base de Datos](#9-esquema-de-base-de-datos)
10. [Frontend — Estructura de Módulos JS](#10-frontend--estructura-de-módulos-js)
11. [Integración Externa](#11-integración-externa)
12. [Despliegue](#12-despliegue)

---

## 1. Visión General

**Ronceros Fotografía** es una aplicación web MVC construida sobre CodeIgniter 4. Permite al equipo del estudio:

- Crear y gestionar **cotizaciones** con paquetes fotográficos, servicios y descuentos.
- Convertir cotizaciones en **contratos** y registrar pagos parciales.
- Programar **sesiones fotográficas** por promoción escolar.
- Controlar la **asistencia de estudiantes** por sesión en tiempo real.
- Enviar **formularios de inscripción** a apoderados con un link único por alumno.
- Administrar el **catálogo** de paquetes, productos e imágenes.
- Consultar reportes y calendario de sesiones.

El sistema es multiusuario con control de acceso por rol. Soporta modo oscuro/claro persistente.

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.x + **CodeIgniter 4** |
| Base de datos | **MySQL** (driver MySQLi) |
| Servidor web | **Nginx** + PHP-FPM |
| Sistema operativo (VPS) | Debian 12 |
| Frontend | **ES6 Modules** nativos (sin build step) |
| UI / componentes | **Bootstrap 5** + Bootstrap Icons |
| Admin / interactividad | **Alpine.js** (vistas admin) |
| Temas | CSS custom properties (`data-theme="dark|light"`) |
| API externa | **RENIEC** — consulta de DNI vía decolecta.net |

---

## 3. Arquitectura del Sistema

### 3.1 Diagrama de capas

```
┌─────────────────────────────────────────────────────────────┐
│                        NAVEGADOR                            │
│   ES6 Modules (state · ui · form · Main)                    │
│   Bootstrap 5  ·  Alpine.js  ·  CSS custom properties       │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP (web + API)
┌───────────────────────▼─────────────────────────────────────┐
│                   CODEIGNITER 4 (PHP)                        │
│                                                             │
│  ┌──────────┐   ┌────────────┐   ┌──────────────────────┐  │
│  │ AuthFilter│   │  Web Routes│   │     API Routes        │  │
│  │(AuthFilter│   │ /cotizacion│   │     api/*             │  │
│  │  .php)   │   │ /contratos │   │  (excluidos de CSRF)  │  │
│  └──────────┘   └─────┬──────┘   └──────────┬───────────┘  │
│                        │                     │              │
│               ┌────────▼──────┐   ┌──────────▼──────────┐  │
│               │  Controllers/ │   │  Controllers/Api/    │  │
│               │  (renderiza   │   │  (BaseApiController) │  │
│               │   vistas PHP) │   │  JSON {status,data,  │  │
│               └──────┬────────┘   │         message}     │  │
│                      │            └──────────┬────────────┘  │
│                      │                       │              │
│               ┌──────▼───────────────────────▼────────────┐ │
│               │              Services/                     │ │
│               │   (lógica de negocio · transacciones DB)   │ │
│               │   lanzan RuntimeException(msg, $httpCode)  │ │
│               └──────────────────┬────────────────────────┘ │
│                                  │                          │
│               ┌──────────────────▼────────────────────────┐ │
│               │              Models/                       │ │
│               │         (CI4 Model — MySQLi)               │ │
│               └──────────────────┬────────────────────────┘ │
│                                  │                          │
│               ┌──────────────────▼────────────────────────┐ │
│               │             Transformers/                  │ │
│               │        (formateo de respuesta JSON)        │ │
│               └───────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Layout de vistas

Cada controlador inyecta el layout manualmente:

```php
$data['header'] = view('Layouts/header');
$data['footer'] = view('Layouts/footer');
return view('cotizaciones/crear', $data);
```

La vista de login (`auth/login.php`) es standalone (sin layout).

### 3.3 Rutas públicas (sin autenticación)

| Ruta | Función |
|---|---|
| `GET /login` | Formulario de inicio de sesión |
| `POST /login` | Procesa login |
| `GET /logout` | Cierra sesión |
| `GET /formulario/:token` | Formulario de inscripción para apoderado |
| `GET /formulario/gracias` | Confirmación de envío |
| `GET /formulario/grupo/:token` | Vista de grupo/promoción |

---

## 4. Roles y Permisos

| Rol | Usuario de prueba | Contraseña | Acceso |
|---|---|---|---|
| **Administrador** | `carlos.admin` | `Admin1234!` | Acceso total: reportes, configuración, todos los módulos |
| **Vendedor** | `maria.ventas` | `Ventas123!` | Cotizaciones, contratos, clientes, catálogo |
| **Fotógrafo** | `jorge.foto` | `Foto1234!` | Sesiones fotográficas, asistencia, calendario |
| **Supervisor** | `ana.supervisor` | `Super123!` | Vista de reportes y monitoreo general |

Los permisos se almacenan en `roles_permisos` (tabla intermedia entre `roles` y `permisos`).

---

## 5. Autenticación y Seguridad

### 5.1 Login

- Campo de login: `nombre_user` (formato `[a-zA-Z0-9._-]`).
- Contraseña: ASCII imprimible, mínimo 8 caracteres, almacenada con **bcrypt**.
- Al autenticarse exitosamente: `session()->regenerate(true)`.
- Datos en sesión: `logged_in`, `usuario_id`, `nombre_user`, `nombres`, `apellidos`, `id_rol`, `rol`.

### 5.2 Anti-fuerza bruta (CI4 Throttler)

- 10 intentos por IP.
- 5 intentos por nombre de usuario.
- Bloqueo de 15 minutos al superar el límite.
- Cache de throttler en `writable/cache/throttler_*`.

### 5.3 CSRF

- Habilitado globalmente para rutas web.
- Excluido para `api/*` (autenticación por sesión activa).

### 5.4 AuthFilter

- Bloquea todas las rutas excepto `/login` y `/logout`.
- Rutas `api/*`: devuelve HTTP 401 en JSON.
- Rutas web: redirige a `/login`.

---

## 6. Módulos del Sistema

### 6.1 Cotizaciones

**Ruta:** `/cotizaciones`

Permite crear presupuestos para clientes antes de formalizar un contrato.

**Flujo:**
1. Seleccionar cliente principal (y opcionalmente segundo responsable).
2. Ingresar datos de la promoción escolar (colegio, grado, sección, N° de estudiantes).
3. Agregar ítems al presupuesto:
   - **Paquetes** del catálogo (máx. 2× el N° de estudiantes).
   - **Servicios/cortesías** (cantidad máx. 100 por ítem).
   - **Ítems personalizados** con precio manual.
4. Aplicar descuento opcional (monto fijo).
5. Guardar con estado `PENDIENTE`.

**Estados:** `PENDIENTE` → `APROBADA` → (convierte en contrato) | `RECHAZADA` | `EXPIRADA`

**Campos clave de `cotizaciones`:**

| Campo | Descripción |
|---|---|
| `id_cliente` | Cliente principal |
| `id_cliente2` | Segundo responsable (opcional) |
| `id_usuario` | Vendedor que la creó |
| `total_estimado` | Suma de ítems |
| `descuento_monto` | Descuento en soles |
| `estado` | `PENDIENTE / APROBADA / RECHAZADA / EXPIRADA` |
| `archivado` | Flag de archivado (no aparece en lista activa) |

---

### 6.2 Contratos

**Ruta:** `/contratos`

Se crea a partir de una cotización aprobada. Formaliza el acuerdo con el cliente.

**Flujo:**
1. Desde una cotización `APROBADA`, crear contrato.
2. Ingresar adelanto obligatorio.
3. Registrar pagos adicionales (Efectivo, Yape, Plin, etc.).
4. El contrato genera automáticamente la `promocion_escolar` asociada.

**Estados de contrato:** `ACTIVO` → `COMPLETADO` | `CANCELADO`

**Pagos:** Se registran con fecha, monto, moneda (`PEN/USD/EUR`), forma de pago (texto libre) y número de voucher opcional.

---

### 6.3 Clientes

**Ruta:** `/clientes`

Gestión del directorio de clientes.

- Cada cliente referencia a una `persona` (nombres, apellido, teléfono, documento).
- Campos adicionales: red social, método de comunicación preferido, acepta promociones.
- Se puede consultar el DNI automáticamente vía **API RENIEC**.
- Estado: `ACTIVO / INACTIVO`.

---

### 6.4 Catálogo (Paquetes)

**Ruta:** `/catalogo`

> Internamente el dominio se llama `paquetes`. Solo la UI y la ruta usan "Catálogo".

Administración del portafolio de productos fotográficos.

- **Niveles disponibles:** `inicial`, `primaria`, `secundaria`, `postgrado`, `otro`.
- **Categorías:** `Cuadros`, `Anuarios`, `Paquetes`, `otros`.
- Cada paquete tiene nombre, descripción, imagen, precio y estado (`ACTIVO/INACTIVO`).
- Un paquete puede incluir múltiples **productos** (álbumes, impresiones, etc.) y múltiples **sesiones** (tipo + lugar + cantidad).
- Los inactivos se muestran al final de su grupo en el selector de cotizaciones.

---

### 6.5 Sesiones Fotográficas

**Ruta:** `/contratos/:id/sesiones` | `/sesiones`

Control de sesiones programadas por promoción escolar.

**Flujo:**
1. El fotógrafo accede a la promoción desde el contrato.
2. Programa sesiones con fecha/hora, tipo (exteriores, colegio, estudio, otro) y observaciones.
3. Gestiona el estado de cada sesión: `pendiente` → `finalizado` | `cancelado`.
4. Desde la vista de asistencia, marca a cada estudiante: asistió (✓) / faltó (—) / sin marcar.

**Restricciones:**
- No se pueden programar dos sesiones a la misma hora (conflicto global por horario).
- Fecha máxima de sesión: ~10 meses desde la fecha actual.

---

### 6.6 Control de Asistencia

Integrado en la vista de sesiones. Muestra una tabla donde:
- Columnas = sesiones programadas (máx. 3 visibles).
- Filas = estudiantes de la promoción.
- Celda = estado de asistencia (toggle por clic).

Las estadísticas (asistieron / faltaron / sin marcar) se actualizan en tiempo real.

---

### 6.7 Formularios de Inscripción Estudiantil

**Rutas públicas:** `/formulario/:token` | `/formulario/grupo/:token`

Sistema de auto-registro para apoderados sin necesidad de cuenta.

**Flujo:**
1. El administrador crea una `prom_promocion` con la lista de alumnos.
2. El sistema genera un token único por alumno (`prom_alumnos.token`).
3. Se comparte el link con el apoderado (copiado desde el panel de admin).
4. El apoderado completa el formulario: datos del alumno, fecha de nacimiento, color favorito, profesión futura, datos del tutor, preferencia de cuadro/anuario, consentimientos.
5. Los datos se guardan en `prom_formularios`.

---

### 6.8 Promociones Escolares

**Ruta:** `/promociones`

Vista de gestión de las `prom_promociones` (diferente de `promociones_escolares` que pertenece a contratos).

Permite:
- Ver el avance de formularios completados vs. enviados.
- Activar/desactivar promociones.
- Acceder al panel de administración de formularios con links de copia.

---

### 6.9 Calendario

**Ruta:** `/calendario`

Vista mensual de todas las sesiones fotográficas programadas. Permite al equipo ver la agenda de trabajo por día.

---

### 6.10 Reportes (Admin)

**Ruta:** `/admin/reporte/*`

Reportes de gestión accesibles solo para roles con permiso de administración: ventas por período, contratos activos, resumen de pagos.

---

## 7. Flujo de Datos Principal

### 7.1 Ciclo de vida completo de un contrato escolar

```
[Vendedor]
    │
    ▼
COTIZACIÓN (PENDIENTE)
    • Cliente + 2do responsable
    • Paquetes del catálogo (≤ 2× estudiantes)
    • Servicios / cortesías (≤ 100 c/u)
    • Descuento opcional
    │
    ▼  [Aprobación]
CONTRATO (ACTIVO)
    • Adelanto registrado
    • Promoción escolar creada automáticamente
    │
    ├─── PAGOS (parciales/totales)
    │        • Fecha · Monto · Moneda · Forma de pago · Voucher
    │
    ├─── SESIONES FOTOGRÁFICAS (1..n)
    │        • Fecha/hora · Tipo · Lugar · Estado
    │        │
    │        └─── ASISTENCIA POR SESIÓN
    │                 • Estudiante → asistió / faltó / sin marcar
    │
    └─── FORMULARIOS DE APODERADOS
             • Token único por alumno
             • Datos del alumno + tutor + preferencias
             │
             ▼
         PROM_FORMULARIOS (completado = true)
```

### 7.2 Flujo de una solicitud API

```
Navegador (fetch)
    │
    ▼
api/cotizaciones  [POST]
    │
    ▼
AuthFilter ──── sesión inválida ──→ HTTP 401 JSON
    │
    ▼
CotizacionesApi::crear()
    │
    ▼
CotizacionService::crear($data)
    ├── Validación de negocio (lanza RuntimeException con $code HTTP)
    ├── Transacción DB
    │     ├── INSERT cotizaciones
    │     └── INSERT cotizaciones_detalles (×n ítems)
    └── Retorna entidad
    │
    ▼
BaseApiController::_serviceError() — mapea excepciones a HTTP 4xx/5xx
    │
    ▼
JSON { status: "success", data: {...}, message: "..." }
```

---

## 8. API REST — Endpoints

Todas las rutas API requieren sesión activa. Prefijo: `api/`.

| Método | Ruta | Descripción |
|---|---|---|
| GET | `api/clientes` | Listar clientes |
| GET | `api/clientes/:id` | Obtener cliente |
| POST | `api/clientes` | Crear cliente |
| PUT | `api/clientes/:id` | Actualizar cliente |
| DELETE | `api/clientes/:id` | Eliminar cliente |
| GET | `api/colegios` | Listar colegios |
| PUT | `api/colegios/:id` | Actualizar colegio |
| GET | `api/cotizaciones` | Listar cotizaciones |
| GET | `api/cotizaciones/:id` | Obtener cotización |
| POST | `api/cotizaciones` | Crear cotización |
| PUT | `api/cotizaciones/:id` | Actualizar cotización |
| DELETE | `api/cotizaciones/:id` | Eliminar cotización |
| PUT | `api/cotizaciones/:id/estado` | Cambiar estado |
| PUT | `api/cotizaciones/:id/archivar` | Archivar/desarchivar |
| GET | `api/contratos` | Listar contratos |
| GET | `api/contratos/:id` | Obtener contrato |
| POST | `api/contratos` | Crear contrato |
| PUT | `api/contratos/:id` | Actualizar contrato |
| DELETE | `api/contratos/:id` | Eliminar contrato |
| PUT | `api/contratos/:id/estado` | Cambiar estado |
| PUT | `api/contratos/:id/archivar` | Archivar/desarchivar |
| GET | `api/pagos?id_contrato=:id` | Pagos de un contrato |
| POST | `api/pagos` | Registrar pago |
| DELETE | `api/pagos/:id` | Eliminar pago |
| GET | `api/reniec/dni?dni=:dni` | Consultar DNI en RENIEC |
| GET | `api/paquetes` | Listar paquetes |
| GET | `api/paquetes/:id` | Obtener paquete |
| POST | `api/paquetes` | Crear paquete |
| PUT | `api/paquetes/:id` | Actualizar paquete |
| DELETE | `api/paquetes/:id` | Eliminar paquete |
| PUT | `api/paquetes/:id/estado` | Activar/desactivar |
| GET | `api/paquetes/:id/productos` | Productos del paquete |
| POST | `api/paquetes/:id/imagen` | Subir imagen |
| GET | `api/productos` | Listar productos |
| GET | `api/promociones` | Listar promociones |
| POST | `api/promociones` | Crear promoción |
| PUT | `api/promociones/:id` | Actualizar promoción |
| DELETE | `api/promociones/:id` | Eliminar promoción |
| PUT | `api/promociones/:id/activar` | Activar promoción |
| GET | `api/sesiones?id_contrato=:id` | Sesiones de un contrato |
| POST | `api/sesiones` | Crear sesión |
| PUT | `api/sesiones/:id` | Editar sesión |
| DELETE | `api/sesiones/:id` | Eliminar sesión |
| PUT | `api/sesiones/:id/estado` | Cambiar estado de sesión |
| POST | `api/sesiones/:id/asistencia` | Marcar asistencia |
| GET | `api/estudiantes?id_promocion=:id` | Estudiantes de una promoción |
| POST | `api/estudiantes` | Agregar estudiante |
| PUT | `api/estudiantes/:id` | Editar estudiante |
| DELETE | `api/estudiantes/:id` | Eliminar estudiante |

**Formato de respuesta estándar:**

```json
{
  "status": "success" | "error",
  "data": { ... } | null,
  "message": "Descripción del resultado"
}
```

---

## 9. Esquema de Base de Datos

### 9.1 Diagrama de relaciones

```
personas ──────────┬──── clientes ────────────── cotizaciones ──── contratos
                   │                                   │                │
                   ├──── usuarios                      │                ├── pagos
                   │                                   ▼                │
                   └──── apoderados ─── estudiantes   promo_escolar ◄──┘
                                            │              │
                                            │              ├── sesiones_fotograficas
                                            │              │        └── sesion_asistencia
                                            └──────────────┘

paquetes ──── paquetes_productos ──── productos
    └──────── paquetes_sesiones
    └──────── cotizaciones_detalles

colegios ──── promociones_escolares (promo_escolar)
    └──────── prom_promociones ──── prom_alumnos ──── prom_formularios

roles ──── roles_permisos ──── permisos
```

---

### 9.2 Tablas

#### `personas`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_persona` | INT PK | Identificador |
| `nombres` | VARCHAR(100) | Nombres |
| `apellidos` | VARCHAR(100) | Apellidos (nullable) |
| `telefono` | CHAR(9) | Teléfono principal |
| `correo` | VARCHAR(150) | Correo electrónico |
| `tel_alternativo` | VARCHAR(20) | Teléfono alternativo |
| `numero_documento` | VARCHAR(50) | DNI / CE / Pasaporte |
| `tipo_documento` | ENUM | `DNI`, `CE`, `PASAPORTE` |

---

#### `usuarios`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_usuario` | INT PK | Identificador |
| `id_persona` | INT FK | Datos personales |
| `id_rol` | INT FK | Rol del usuario |
| `nombre_user` | VARCHAR(50) UNIQUE | Login (ej: `carlos.admin`) |
| `password_hash` | VARCHAR(255) | Contraseña bcrypt |
| `estado` | ENUM | `ACTIVO`, `INACTIVO` |

---

#### `roles` y `permisos`
| Tabla | Columnas clave |
|---|---|
| `roles` | `id_rol`, `nombre_rol` |
| `permisos` | `id_permiso`, `nombre_permiso`, `descripcion` |
| `roles_permisos` | `id_rol` FK, `id_permiso` FK |

---

#### `clientes`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_cliente` | INT PK | Identificador |
| `id_persona` | INT FK | Datos personales |
| `red_social` | VARCHAR(150) | Perfil de redes sociales |
| `metodo_comunicacion` | ENUM | `correo`, `whatsapp`, `llamada`, `otro` |
| `acepta_promociones` | BOOLEAN | Consentimiento de marketing |
| `estado` | ENUM | `ACTIVO`, `INACTIVO` |

---

#### `colegios`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_colegio` | INT PK | Identificador |
| `nombre_colegio` | VARCHAR | Nombre del colegio |
| (otros campos de contacto) | — | Dirección, teléfono, etc. |

---

#### `paquetes`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_paquete` | INT PK | Identificador |
| `nombre_paquete` | VARCHAR(150) | Nombre del paquete |
| `nivel_disponible` | ENUM | `inicial`, `primaria`, `secundaria`, `postgrado`, `otro` |
| `categoria` | ENUM | `Cuadros`, `Anuarios`, `Paquetes`, `otros` |
| `descripcion` | TEXT | Descripción libre |
| `imagen` | VARCHAR(255) | Ruta de imagen |
| `precio` | DECIMAL(7,2) | Precio base |
| `estado` | ENUM | `ACTIVO`, `INACTIVO` |

---

#### `productos`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_producto` | INT PK | Identificador |
| `nombre_producto` | VARCHAR | Nombre del producto |
| `descripcion` | TEXT | Descripción |
| `precio` | DECIMAL(7,2) | Precio unitario |
| `estado` | ENUM | `ACTIVO`, `INACTIVO` |

---

#### `paquetes_productos` *(tabla intermedia)*
| Columna | Tipo | Descripción |
|---|---|---|
| `id_paquete` | INT FK | Paquete |
| `id_producto` | INT FK | Producto incluido |
| `cantidad` | INT | Cantidad del producto en el paquete |

---

#### `paquetes_sesiones`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_paquete_sesion` | INT PK | Identificador |
| `id_paquete` | INT FK | Paquete |
| `tipo_sesion` | ENUM | `exteriores`, `colegio`, `estudio`, `otro` |
| `lugar_descripcion` | VARCHAR(150) | Descripción del lugar |
| `num_sesiones` | TINYINT | Cantidad de sesiones incluidas |

---

#### `cotizaciones`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_cotizacion` | INT PK | Identificador |
| `id_cliente` | INT FK | Cliente principal |
| `id_cliente2` | INT FK nullable | Segundo responsable |
| `id_usuario` | INT FK | Vendedor |
| `fecha_registro` | DATE | Fecha de creación |
| `total_estimado` | DECIMAL(7,2) | Total antes de descuento |
| `descuento_monto` | DECIMAL(7,2) | Descuento en soles |
| `observaciones` | TEXT | Notas internas |
| `estado` | ENUM | `PENDIENTE`, `APROBADA`, `RECHAZADA`, `EXPIRADA` |
| `archivado` | TINYINT | 0 = visible, 1 = archivado |

---

#### `cotizaciones_detalles`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_detalle` | INT PK | Identificador |
| `id_cotizacion` | INT FK | Cotización padre |
| `tipo_item` | ENUM | `paquete`, `producto`, `personalizado` |
| `id_referencia` | INT nullable | ID del paquete/producto (si aplica) |
| `descripcion` | TEXT | Descripción del ítem personalizado |
| `cantidad` | SMALLINT | Cantidad contratada |
| `precio_unitario` | DECIMAL(7,2) | Precio por unidad |

---

#### `contratos`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_contrato` | INT PK | Identificador |
| `id_cotizacion` | INT FK | Cotización origen |
| `fecha_creacion` | DATE | Fecha de creación |
| `fecha_emision` | DATE nullable | Fecha de emisión formal |
| `adelanto` | DECIMAL(7,2) | Pago inicial obligatorio |
| `total` | DECIMAL(7,2) | Total del contrato |
| `contacto2_nombre` | VARCHAR(150) | Nombre segundo contacto |
| `contacto2_telefono` | VARCHAR(20) | Teléfono segundo contacto |
| `observaciones` | TEXT | Notas del contrato |
| `estado` | ENUM | `ACTIVO`, `CANCELADO`, `COMPLETADO` |
| `archivado` | TINYINT | 0 = visible, 1 = archivado |

---

#### `pagos`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_pago` | INT PK | Identificador |
| `fecha` | DATE | Fecha del pago |
| `monto` | DECIMAL(7,2) | Monto pagado |
| `moneda` | ENUM | `PEN`, `EUR`, `USD` |
| `voucher` | VARCHAR(255) | Número o referencia de voucher |
| `forma_pago` | VARCHAR(60) | Texto libre (Efectivo, Yape, Plin…) |
| `id_contrato` | INT FK | Contrato al que pertenece |

---

#### `promociones_escolares`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_promocion` | INT PK | Identificador |
| `id_colegio` | INT FK | Colegio |
| `id_cotizacion` | INT FK | Cotización asociada |
| `nombre` | VARCHAR(100) | Nombre de la promoción (ej: "Promoción 2025") |
| `grado` | ENUM | `Inicial`, `Jardin`, `Primaria`, `Secundaria`, `Superior`, `Otro` |
| `seccion` | VARCHAR(10) | Sección (ej: A, 1A, Única) |
| `num_estudiantes` | SMALLINT | N° de estudiantes esperados |
| `anio` | YEAR | Año escolar |
| `is_active` | BOOLEAN | Activa o no |

---

#### `apoderados`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_apoderado` | INT PK | Identificador |
| `id_persona` | INT FK | Datos personales del apoderado |
| `tipo_relacion` | ENUM | `padre`, `madre`, `hermano`, `otro` |

---

#### `estudiantes`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_estudiante` | INT PK | Identificador |
| `id_apoderado` | INT FK | Apoderado responsable |
| `id_promocion` | INT FK | Promoción escolar |
| `nombres` | VARCHAR(30) | Nombres del alumno |
| `apellidos` | VARCHAR(30) | Apellidos del alumno |
| `fecha_nacimiento` | DATE | Fecha de nacimiento |
| `color_fav` | VARCHAR(30) | Color favorito |
| `profesion_futura` | VARCHAR(40) | Profesión que quiere estudiar |

---

#### `sesiones_fotograficas`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_sesion` | INT PK | Identificador |
| `id_promocion` | INT FK | Promoción escolar |
| `fecha_hora_sesion` | DATETIME | Fecha y hora programada |
| `tipo` | ENUM | `exteriores`, `colegio`, `estudio`, `otro` |
| `observaciones` | TEXT | Notas de la sesión |
| `estado` | ENUM | `pendiente`, `finalizado`, `cancelado` |

---

#### `sesion_asistencia`
| Columna | Tipo | Descripción |
|---|---|---|
| `id_asistencia` | INT PK | Identificador |
| `id_sesion` | INT FK | Sesión fotográfica |
| `id_estudiante` | INT FK | Estudiante |
| `asistio` | TINYINT nullable | `null` = sin marcar, `1` = asistió, `0` = faltó |

---

#### `prom_promociones` *(formularios)*
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `colegio_id` | INT FK | Colegio |
| `nombre` | VARCHAR(150) | Nombre de la promoción |
| `nivel` | VARCHAR(80) | Nivel educativo |
| `cuadros_total` / `cuadros_usados` | INT | Control de cuadros |
| `anuarios_total` / `anuarios_usados` | INT | Control de anuarios |
| `activa` | TINYINT | 1 = activa |
| `created_at` | DATETIME | Fecha de creación |

---

#### `prom_alumnos`
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `promocion_id` | INT FK | Promoción |
| `nombre` | VARCHAR(150) | Nombre del alumno |
| `token` | VARCHAR(64) UNIQUE | Token único para el link |
| `completado` | TINYINT | 1 = formulario completado |
| `enviado` | TINYINT | 1 = link enviado al apoderado |
| `created_at` | DATETIME | Fecha |

---

#### `prom_formularios`
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `alumno_id` | INT FK UNIQUE | Alumno (1 formulario por alumno) |
| `nombre_alumno` | VARCHAR(150) | Nombre confirmado por el apoderado |
| `fecha_nacimiento` | DATE | Fecha de nacimiento |
| `color_favorito` | VARCHAR(80) | Color favorito |
| `profesion_futura` | VARCHAR(150) | Profesión deseada |
| `nombre_tutor` | VARCHAR(150) | Nombre del apoderado |
| `relacion_tutor` | ENUM | `Padre`, `Madre`, `Tutor` |
| `telefono` | VARCHAR(20) | Teléfono del tutor |
| `email` | VARCHAR(150) | Email del tutor |
| `tiene_cuadro` | TINYINT | Solicita cuadro fotográfico |
| `tiene_anuario` | TINYINT | Solicita anuario |
| `acepta_imagenes` | TINYINT | Consentimiento de imágenes |
| `acepta_datos` | TINYINT | Consentimiento de datos |
| `ip_address` | VARCHAR(45) | IP del envío |
| `created_at` | DATETIME | Fecha de envío |

---

## 10. Frontend — Estructura de Módulos JS

Cada dominio funcional tiene sus propios módulos ES6 bajo `public/js/modules/<dominio>/`:

| Archivo | Responsabilidad |
|---|---|
| `<dominio>.state.js` | Estado compartido, funciones puras de filtro/sort/agrupación, cálculo de stats |
| `<dominio>.ui.js` | Renderizado del DOM (sin fetch) |
| `<dominio>.form.js` | Lectura y escritura de formularios/modales |
| `<dominio>Main.js` | Punto de entrada: conecta state + ui + form + api; expone funciones en `window.*` |

Las llamadas HTTP se centralizan en `public/js/api/<dominio>.api.js`, que usa `utils/http.js`.

**Utilidades compartidas:**

| Archivo | Función |
|---|---|
| `utils/http.js` | Fetch con BASE_URL, headers JSON, normalización de errores |
| `utils/alerts.js` | Notificaciones toast (éxito / error / info) |
| `utils/formatters.js` | `moneda()`, `fecha()`, `estado()`, `codigo()` |

Cada vista declara `<script>const BASE_URL = "<?= base_url('') ?>"</script>` antes de cargar el módulo.

---

## 11. Integración Externa

### API RENIEC (decolecta.net)

- **Endpoint interno:** `GET api/reniec/dni?dni=:dni`
- **Uso:** Autocompletar datos del cliente al ingresar su DNI.
- **Configuración:** Clave API en `.env` como `DECOLECTA.KEY`.
- **Restricción:** Solo funciona para documentos tipo DNI (8 dígitos).

---

## 12. Despliegue

### Ambiente de desarrollo

```bash
php spark serve          # Levanta en http://localhost:8080
php spark migrate        # Ejecuta migraciones
php spark db:seed DatabaseSeeder  # Carga datos de prueba
./vendor/bin/phpunit     # Ejecuta suite de tests
rm -f writable/cache/throttler_*  # Limpia bloqueo de login en dev
```

### Producción (VPS Debian 12)

- **Servidor web:** Nginx con PHP-FPM
- **Base de datos:** MySQL (`default-mysql-server`)
- **Configuración:** Variables de entorno en `.env` (no versioning)
- **Deploy:** Script automatizado en el repositorio para Nginx + PHP-FPM

> **Nota:** `$indexPage = 'index.php'` en `app/Config/App.php`. En dev, `site_url()` incluye `index.php`; en producción con Nginx se remueve via rewrite.

---

*Documentación generada para Ronceros Fotografía — Sistema de Gestión Integral v1.0*
