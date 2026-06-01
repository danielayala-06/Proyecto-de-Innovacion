# "Año de la Esperanza y el Fortalecimiento de la Democracia"

---

**SERVICIO NACIONAL DE ADIESTRAMIENTO EN TRABAJO INDUSTRIAL**
**DIRECCIÓN ZONAL: ICA – AYACUCHO**
**ESCUELA DE TECNOLOGÍA DE LA INFORMACIÓN**
**CARRERA: INGENIERÍA DE SOFTWARE CON INTELIGENCIA ARTIFICIAL**

**Proyecto de Innovación y/o Mejora**
**Nivel Profesional Técnico**

---

# "Proyecto de Innovación de un Sistema de Gestión de Contratos y Flujo de Trabajo para la Empresa Ronceros Consultores & Editores S.R.L."

**Autores:**
- Ayala Romo, Daniel Jesús
- Félix Tipacti, Diggy Tony Jesús

**Asesor:** Ing. Richard Jhonson Barrios Quispe

**Chincha Alta – Perú**
**2026**

---

## DEDICATORIA

Este proyecto está dedicado a nuestras familias, quienes con su apoyo incondicional y palabras de aliento nos han impulsado a seguir adelante en cada etapa de nuestra formación profesional. A nuestros docentes, por compartir su conocimiento y guiarnos con paciencia y dedicación a lo largo de este camino. Y a todos aquellos que creen en el poder de la tecnología como herramienta de transformación y mejora continua.

**Ayala Romo, Daniel Jesús – Félix Tipacti, Diggy Tony Jesús**

---

## RESUMEN EJECUTIVO DEL PROYECTO DE INNOVACIÓN Y/O MEJORA

Dentro de la empresa Ronceros Consultores & Editores S.R.L., dedicada a brindar servicios de fotografía profesional, marketing digital, impresiones y edición de revistas, se identificó una serie de deficiencias en el área de gestión comercial que afectaban directamente la experiencia del cliente y la eficiencia operativa.

El problema radicaba principalmente en el proceso manual para la generación, control y seguimiento de cotizaciones y contratos de servicios fotográficos. Este proceso comenzaba con la atención al cliente, donde el personal recopilaba los datos del cliente, las características del paquete deseado y los detalles del evento a fotografiar. Luego, se elaboraba manualmente una cotización en documentos físicos o archivos de Word/Excel, lo cual generaba inconsistencias, pérdida de información y dificultades para dar seguimiento al estado de cada cliente.

Sin embargo, durante este proceso, el personal no contaba con un sistema centralizado que permitiera gestionar los paquetes disponibles, aplicar reglas de precios automáticamente, generar contratos ni hacer seguimiento del estado de cada cotización, lo que generaba ineficiencias y potenciales errores.

Para resolver estas deficiencias, se propuso e implementó un **Sistema Web de Gestión de Contratos y Flujo de Trabajo** que centraliza y digitaliza todos los procesos relacionados con la cotización, contratación, programación de sesiones fotográficas y seguimiento de pagos.

El sistema incorpora módulos para la gestión de clientes con validación mediante la API de RENIEC, cotizaciones con reglas de precios dinámicas, generación de contratos, calendario de sesiones fotográficas, control de pagos y un panel de administración con reportes. La asignación de roles (Administrador, Vendedor, Fotógrafo, Supervisor) permite un control granular de acceso y responsabilidades.

La estructura del documento se organiza de la siguiente manera:

- **Capítulo I:** Generalidades de la institución y datos relevantes de la empresa.
- **Capítulo II:** Identificación del problema, objetivos, antecedentes y justificación del proyecto.
- **Capítulo III:** Análisis de la situación actual, diagrama de Ishikawa y diagrama de Pareto.
- **Capítulo IV:** Propuesta técnica de la mejora, plan de acción y cronograma.
- **Capítulo V:** Costos asociados a la implementación de la mejora.
- **Capítulo VI:** Beneficios técnicos y económicos esperados.
- **Capítulo VII:** Conclusiones del proyecto.
- **Capítulo VIII:** Recomendaciones para la empresa.

---

## ÍNDICE

- [Capítulo I – Generalidades de la Empresa](#capítulo-i--generalidades-de-la-empresa)
- [Capítulo II – Plan del Proyecto de Innovación y/o Mejora](#capítulo-ii--plan-del-proyecto-de-innovación-yo-mejora)
- [Capítulo III – Análisis de la Situación Actual](#capítulo-iii--análisis-de-la-situación-actual)
- [Capítulo IV – Propuesta Técnica de la Mejora](#capítulo-iv--propuesta-técnica-de-la-mejora)
- [Capítulo V – Costos de Implementación](#capítulo-v--costos-de-implementación)
- [Capítulo VI – Evaluación Técnica y Económica](#capítulo-vi--evaluación-técnica-y-económica)
- [Capítulo VII – Conclusiones](#capítulo-vii--conclusiones)
- [Capítulo VIII – Recomendaciones](#capítulo-viii--recomendaciones)
- [Referencias Bibliográficas](#referencias-bibliográficas)

---

# CAPÍTULO I – GENERALIDADES DE LA EMPRESA

## 1. Presentación de la Empresa

### 1.1 Razón Social

| Campo | Detalle |
|---|---|
| **Razón Social** | RONCEROS CONSULTORES & EDITORES S.R.L. |
| **RUC** | 20611477920 |
| **N.º de Partida** | 11093784 |
| **Representante Legal** | Enrique Mario Ronceros Flores |
| **Dirección** | Av. Abelardo Alva Maurtua 500 – Chincha Alta, Ica, Perú |
| **Actividad Económica** | Servicio de fotografía, marketing digital, impresiones, edición de revistas |

### 1.2 Misión, Visión, Objetivos y Valores de la Empresa

#### 1.2.1 Misión

Inmortalizar los instantes más valiosos de nuestros clientes a través de una mirada creativa, un alto estándar de calidad y un compromiso total con el profesionalismo, convirtiendo cada fotografía en un recuerdo lleno de emoción y significado.

#### 1.2.2 Visión

Ser la empresa líder en fotografía y producción audiovisual en nuestra región, reconocida por ofrecer servicios de calidad y por innovar constantemente en cada proyecto.

#### 1.2.3 Objetivos

- Brindar un servicio fotográfico de alta calidad que supere las expectativas de los clientes, asegurando la captura de momentos únicos e irrepetibles con tecnología y técnica de vanguardia.
- Optimizar la gestión comercial y operativa de la empresa mediante la implementación de herramientas digitales que agilicen los procesos de cotización, contratación y seguimiento de servicios.
- Fortalecer la relación con los clientes ofreciendo transparencia en los procesos, comunicación oportuna y facilidad de acceso a la información sobre sus contratos y sesiones programadas.
- Expandir la oferta de servicios hacia nuevos mercados, incluyendo marketing digital, impresiones personalizadas y producción audiovisual, posicionando a la empresa como un referente regional en soluciones creativas integrales.

#### 1.2.4 Valores de la Empresa

- Creatividad
- Compromiso
- Calidad
- Innovación
- Integridad

### 1.3 Servicios, Mercados y Clientes

#### 1.3.1 Servicios

Ronceros Consultores & Editores S.R.L. ofrece una amplia gama de servicios orientados a cubrir las necesidades de registro visual y comunicación gráfica de sus clientes:

- **Fotografía profesional:** Sesiones individuales, familiares, escolares y empresariales. Atención a eventos sociales (matrimonios, graduaciones, quinceañeros) y corporativos.
- **Marketing digital:** Creación de contenido visual para redes sociales, diseño de branding y campañas digitales.
- **Impresiones:** Impresión de fotografías, albums, toldos, banners y material gráfico personalizado.
- **Edición de revistas:** Diseño editorial y producción de revistas institucionales y comerciales.
- **Paquetes escolares:** Fotografías de graduación y promoción para instituciones educativas, organizadas por nivel (inicial-primaria, secundaria, postgrado).

#### 1.3.2 Mercados

La empresa opera principalmente en el mercado local y regional de Chincha Alta, Ica. Su mercado objetivo incluye:

- Familias e individuos que buscan servicios de fotografía para momentos especiales.
- Instituciones educativas que requieren fotografías de graduación y promoción.
- Empresas y emprendimientos que necesitan contenido visual para marketing.
- Organizaciones que requieren producción editorial y material gráfico.

#### 1.3.3 Clientes

Los principales clientes de Ronceros Consultores & Editores S.R.L. son personas naturales, familias, instituciones educativas (colegios y universidades) y empresas de la región Ica. Los clientes más frecuentes son aquellos que contratan paquetes fotográficos escolares (a través de apoderados de alumnos) y servicios de eventos sociales. La empresa busca fortalecer la relación con cada cliente mediante un servicio personalizado, transparente y de alta calidad.

### 1.4 Estructura de la Organización

```
              RONCEROS CONSULTORES & EDITORES S.R.L.
                              |
                    GERENCIA GENERAL
                    (Enrique Ronceros)
                              |
          ┌───────────────────┼────────────────────┐
          |                   |                    |
   Área Comercial      Área de Producción    Área Administrativa
   (Ventas/Contratos)  (Fotografía/Edición)  (Finanzas/RRHH)
          |                   |
   Vendedores           Fotógrafos
   Supervisores         Editores
```

### 1.5 Ubicación

La empresa Ronceros Consultores & Editores S.R.L. se encuentra ubicada en:

**Av. Abelardo Alva Maurtua 500 – Chincha Alta, Ica, Perú**

---

# CAPÍTULO II – PLAN DEL PROYECTO DE INNOVACIÓN Y/O MEJORA

## 2. Plan de Proyecto de Innovación y/o Mejora

### 2.1 Identificación del Problema en la Empresa

#### 2.1.1 Situación

Ronceros Consultores & Editores S.R.L. es una empresa con una sólida trayectoria en el rubro de fotografía y servicios creativos en la región de Chincha Alta. A pesar del reconocimiento que tiene en el mercado local, el área comercial enfrenta serias deficiencias operativas debidas a la falta de digitalización en la gestión de cotizaciones, contratos y flujos de trabajo internos.

El proceso de atención al cliente carecía de una estructura digital clara. Cuando un cliente solicitaba un servicio fotográfico, el personal debía:

- Buscar manualmente la información de paquetes en documentos físicos o archivos de Excel no centralizados.
- Calcular precios y elaborar cotizaciones a mano, con riesgo de errores en los montos.
- Redactar contratos en Word, repitiendo trabajo y sin plantillas estandarizadas.
- Registrar los datos del cliente sin validación, lo que generaba duplicados e inconsistencias.
- Anotar las sesiones fotográficas en agendas físicas, sin visibilidad del equipo.
- Registrar pagos en hojas de cálculo independientes, sin vinculación con los contratos.

Entre las principales deficiencias identificadas se encuentran:

- **Ausencia de centralización de información:** Los datos de clientes, cotizaciones y contratos estaban dispersos en archivos físicos y digitales no integrados, dificultando la consulta y el seguimiento.
- **Errores en cotizaciones manuales:** La elaboración manual de cotizaciones generaba inconsistencias en precios, descuentos incorrectos y tiempos de respuesta lentos al cliente.
- **Falta de trazabilidad del flujo de trabajo:** No existía visibilidad del estado de cada cotización (pendiente, aprobada, rechazada) ni del avance de cada contrato.
- **Gestión de sesiones desorganizada:** Las sesiones fotográficas se agendaban sin un sistema centralizado, lo que generaba conflictos de horarios y olvidos.
- **Control de pagos deficiente:** El seguimiento de pagos parciales y saldos pendientes se realizaba manualmente, con riesgo de pérdida de información.
- **Sin control de acceso por roles:** Todo el personal accedía a la misma información sin restricciones, comprometiendo la seguridad y la integridad de los datos.

#### 2.1.2 Solución

Con el fin de optimizar la gestión comercial y operativa, se propuso e implementó el **Sistema Web de Gestión de Contratos y Flujo de Trabajo para Ronceros Consultores & Editores S.R.L.**

Este sistema digitaliza y centraliza todos los procesos relacionados con la atención al cliente, la generación de cotizaciones, la firma de contratos, la programación de sesiones y el control de pagos.

Las características más destacadas del sistema implementado son:

- **Registro digital de clientes con integración RENIEC:** Los datos personales de los clientes se validan automáticamente consultando la API de RENIEC mediante el DNI, eliminando errores de transcripción.
- **Gestión de paquetes fotográficos:** El sistema administra paquetes organizados por nivel educativo (inicial-primaria, secundaria, postgrado, otro), con productos y sesiones asociadas, y reglas de precios dinámicas.
- **Cotizaciones automáticas con reglas de negocio:** El sistema aplica reglas y beneficios automáticamente al generar una cotización, calculando precios de forma precisa y transparente.
- **Generación de contratos:** A partir de una cotización aprobada, el sistema genera automáticamente el contrato de servicios con todos los datos del cliente, paquete y condiciones acordadas.
- **Calendario de sesiones fotográficas:** Las sesiones se programan en un calendario digital integrado, con control de asistencia y visualización por fotógrafo asignado.
- **Control de pagos:** El sistema registra pagos parciales y totales vinculados a cada contrato, con seguimiento del saldo pendiente.
- **Control de acceso por roles:** Cuatro roles diferenciados (Administrador, Vendedor, Fotógrafo, Supervisor) con permisos específicos garantizan la seguridad y organización interna.
- **Panel de reportes y estadísticas:** Los administradores y supervisores pueden visualizar métricas de cotizaciones, contratos activos y pagos en tiempo real.

#### 2.1.3 Aplicación del Diagrama de Afinidades

**Tabla 1: Tabla de Afinidades**

| IDEAS BASE | IDEAS PLANTEADAS |
|---|---|
| **Información** | - Validación automática de datos del cliente mediante RENIEC. |
| | - Registro centralizado de clientes, cotizaciones y contratos. |
| **Gestión** | - Control de acceso por roles (Administrador, Vendedor, Fotógrafo, Supervisor). |
| | - Visualización de datos estadísticos: cotizaciones generadas, contratos activos, pagos pendientes. |
| | - Seguimiento del estado de cada cotización y contrato. |
| **Organización** | - Clasificación de cotizaciones por estado: pendiente, aprobada, rechazada, contratada. |
| | - Calendario centralizado de sesiones fotográficas por fotógrafo. |
| | - Interfaz modular e intuitiva por dominio funcional. |
| **Operación** | - Generación automática de contratos desde cotizaciones aprobadas. |
| | - Exportación de reportes y documentos. |
| | - Aplicación automática de reglas de precios y beneficios. |

#### 2.1.4 Aplicación de la Matriz de Priorización

**Área: Administración**

**Tabla 2: Matriz de Priorización – Resultado 1**

| IDEAS BASE | FREC. | IMP. | FACT. |
|---|---|---|---|
| Información | 5 | 5 | 5 |
| Gestión | 5 | 5 | 5 |
| Organización | 4 | 5 | 4 |
| Operación | 4 | 5 | 4 |

**Área: Ventas**

**Tabla 3: Matriz de Priorización – Resultado 2**

| IDEAS BASE | FREC. | IMP. | FACT. |
|---|---|---|---|
| Información | 5 | 5 | 5 |
| Gestión | 4 | 5 | 4 |
| Organización | 4 | 4 | 4 |
| Operación | 5 | 5 | 4 |

**Área: Fotografía**

**Tabla 4: Matriz de Priorización – Resultado 3**

| IDEAS BASE | FREC. | IMP. | FACT. |
|---|---|---|---|
| Información | 4 | 5 | 5 |
| Gestión | 3 | 4 | 4 |
| Organización | 5 | 5 | 5 |
| Operación | 4 | 5 | 4 |

**Tabla 5: Matriz de Priorización – Consolidada**

| IDEAS BASE | FRECUENCIA | IMPORTANCIA | FACTIBILIDAD | TOTAL |
|---|---|---|---|---|
| Información | 5+5+4 = 14 | 5+5+5 = 15 | 5+5+5 = 15 | **44** |
| Gestión | 5+4+3 = 12 | 5+5+4 = 14 | 5+4+4 = 13 | **39** |
| Organización | 4+4+5 = 13 | 5+4+5 = 14 | 4+4+5 = 13 | **40** |
| Operación | 4+5+4 = 13 | 5+5+5 = 15 | 4+4+4 = 12 | **40** |

### 2.2 Objetivos del Proyecto de Innovación/Mejora/Creatividad

#### 2.2.1 Objetivo General

Desarrollar e implementar un sistema web para gestionar de manera eficiente las cotizaciones, contratos y flujos de trabajo en la empresa Ronceros Consultores & Editores S.R.L., mejorando los procesos operativos, la comunicación interna y la trazabilidad del servicio desde la atención al cliente hasta la entrega del producto fotográfico.

#### 2.2.2 Objetivos Específicos

- Desarrollar un módulo de gestión de clientes que permita registrar y validar automáticamente los datos personales mediante la API de RENIEC, garantizando la integridad y precisión de la información desde el primer contacto.
- Implementar un módulo de cotizaciones con aplicación automática de reglas de precios y beneficios, que permita generar presupuestos precisos y transparentes de forma rápida, reduciendo los tiempos de atención al cliente.
- Diseñar un módulo de gestión de contratos que automatice la generación de documentos contractuales a partir de cotizaciones aprobadas, estandarizando el proceso y eliminando la elaboración manual en Word.
- Implementar un sistema de control de estados y flujo de trabajo para cada cotización y contrato, permitiendo que el personal tenga visibilidad en tiempo real del estado de cada operación comercial.
- Crear un módulo de calendario y sesiones fotográficas que centralice la programación de sesiones, asigne fotógrafos y registre la asistencia, eliminando conflictos de horarios y pérdida de información.
- Implementar un control de pagos vinculado a cada contrato que permita registrar pagos parciales, calcular saldos pendientes y generar reportes financieros para la toma de decisiones gerenciales.
- Establecer un sistema de control de acceso basado en roles (Administrador, Vendedor, Fotógrafo, Supervisor) que garantice la seguridad de la información y la correcta distribución de responsabilidades dentro del sistema.
- Capacitar al personal en el uso del sistema para garantizar una correcta implementación y un manejo eficiente de todos los módulos, asegurando la adopción exitosa de la herramienta.

### 2.3 Antecedentes del Proyecto de Innovación/Mejora/Creatividad

#### 2.3.1 En el Ámbito Internacional

- **(Ramírez López, 2021)**, "Desarrollo e implementación de un sistema web para la gestión de servicios y contratos en la empresa TechFix Solutions (México)".
  - **Objetivo general:** Desarrollar un sistema web que permita gestionar los procesos de cotización, contratación y seguimiento de servicios tecnológicos en TechFix Solutions.
  - **Metodología:** Modelo en cascada.
  - **Conclusiones:** Antes del sistema, la empresa operaba con hojas de Excel y formularios físicos, lo que generaba pérdida de información y demoras en los procesos. Tras la implementación, se mejoró la trazabilidad de los servicios, el control de pagos y la comunicación interna mediante notificaciones automáticas.
  - **Recomendaciones:** (1) Incorporar un módulo de estadísticas avanzado. (2) Implementar notificaciones automáticas por correo para clientes. (3) Añadir soporte para firma digital de contratos.

- **(González Paredes, 2022)**, "Sistema web para la gestión comercial y contratos en la empresa EventPro S.A. (Colombia)".
  - **Objetivo general:** Implementar un sistema web para automatizar el control de cotizaciones, contratos y pagos de servicios de eventos.
  - **Metodología:** SCRUM.
  - **Conclusiones:** La empresa tenía problemas en la coordinación de sus servicios, lo que ocasionaba retrasos y falta de control sobre los ingresos. Con el sistema se logró centralizar la información, optimizar los tiempos de atención y ofrecer a los clientes visibilidad sobre el estado de su contrato en línea.
  - **Recomendaciones:** (1) Desarrollar una app móvil complementaria. (2) Mejorar la seguridad con autenticación en dos pasos. (3) Capacitar continuamente al personal en el uso de la plataforma.

#### 2.3.2 En el Ámbito Nacional

- **(Alvarado Cruz, 2020)**, "Diseño e implementación de un sistema web para la gestión de servicios fotográficos en el estudio Foto Arte – Huancayo".
  - **Objetivo general:** Diseñar e implementar un sistema web para registrar, controlar y monitorear los servicios fotográficos y contratos en Foto Arte.
  - **Metodologías:** SCRUM y prototipos evolutivos.
  - **Conclusiones:** Antes del sistema, los registros se realizaban en hojas manuales, generando duplicidad de información y pérdida de trazabilidad. Con el sistema se logró mejorar la organización interna y reducir en un 38% el tiempo de atención al cliente.
  - **Recomendaciones:** (1) Implementar un módulo de estadísticas de ventas. (2) Automatizar la generación de reportes gerenciales. (3) Establecer políticas de respaldo automático de la base de datos.

- **(Reátegui Mendoza, 2019)**, "Sistema informático para la mejora del proceso comercial en el estudio de fotografía Visual Perú – Tarapoto".
  - **Objetivo general:** Mejorar el proceso de atención y contratación de servicios fotográficos mediante el desarrollo de un sistema informático que gestione cotizaciones, contratos y pagos.
  - **Metodología:** RUP (Rational Unified Process).
  - **Conclusiones:** La implementación del sistema permitió digitalizar los procesos comerciales, reducir errores en la generación de cotizaciones y aumentar la satisfacción del cliente en un 55%.
  - **Recomendaciones:** (1) Capacitar de forma continua al personal. (2) Habilitar un portal de consulta para clientes. (3) Incluir integración con servicios de pago digital.

### 2.4 Justificación del Proyecto de Innovación/Mejora/Creatividad

En Ronceros Consultores & Editores S.R.L., el proceso manual de gestión comercial representaba una limitación significativa para el crecimiento de la empresa. Uno de los riesgos inherentes a los registros físicos y archivos no centralizados es la pérdida de información, inconsistencias en datos y tiempos de respuesta lentos que afectan la experiencia del cliente.

La digitalización de los procesos mediante el sistema implementado ofrece ventajas clave:

1. **Precisión en cotizaciones:** El sistema aplica automáticamente las reglas de precios y beneficios de cada paquete, eliminando errores de cálculo manual y garantizando transparencia hacia el cliente.
2. **Trazabilidad completa:** Cada cotización y contrato tiene un historial de estados, permitiendo que el personal sepa en todo momento en qué etapa se encuentra cada operación.
3. **Seguridad de la información:** Los datos se almacenan en una base de datos relacional centralizada, protegida por autenticación segura con bcrypt y control de acceso por roles.
4. **Eficiencia operativa:** La automatización de tareas repetitivas (generación de contratos, cálculo de precios, validación de clientes) libera tiempo del personal para actividades de mayor valor.
5. **Escalabilidad:** El sistema permite gestionar un volumen creciente de clientes y operaciones sin comprometer la organización, facilitando la expansión del negocio.
6. **Reducción de errores humanos:** La validación de datos del cliente mediante la API de RENIEC elimina errores de transcripción y reduce conflictos por información incorrecta.

### 2.5 Marco Teórico Conceptual

#### 2.5.1 Fundamento Teórico del Proyecto de Innovación y Mejora

- **Aplicativo web:** Es un tipo de software que los usuarios pueden utilizar a través de un navegador web, sin necesidad de instalación local. Estos sistemas permiten acceder a información y funcionalidades mediante internet, facilitando el acceso desde distintos dispositivos. *(Rouse, M., Web application, TechTarget, 2020)*

- **API REST (Interfaz de Programación de Aplicaciones):** Conjunto de reglas que permite la comunicación entre sistemas mediante protocolo HTTP. En este proyecto se utiliza tanto para las rutas internas del sistema (api/*) como para la integración con la API de RENIEC para validación de datos de ciudadanos. *(Red Hat, What is an API?, 2021)*

- **Arquitectura MVC (Modelo-Vista-Controlador):** Patrón de diseño de software que separa la lógica de negocio, la presentación y el control de flujo en tres capas independientes. Utilizado como base del framework CodeIgniter 4 en este proyecto. *(Freeman, E., Head First Design Patterns, 2004)*

- **Control de acceso basado en roles (RBAC):** Mecanismo de seguridad que asigna permisos según el rol del usuario dentro del sistema, garantizando que cada persona acceda únicamente a la información y funcionalidades que le corresponden. *(NIST, Role-Based Access Control, 2020)*

#### 2.5.2 Tecnologías Utilizadas

| Ítem | Tecnología | Descripción | Unidad | Cantidad |
|---|---|---|---|---|
| 1 | **PHP 8.x** | Lenguaje de programación del lado del servidor, base del framework CodeIgniter 4. | Lenguaje | 1 |
| 2 | **CodeIgniter 4** | Framework PHP con arquitectura MVC, utilizado para el backend del sistema con rutas web y API. | Framework | 1 |
| 3 | **MySQL** | Sistema gestor de bases de datos relacional utilizado para almacenar toda la información del sistema. | BD Relacional | 1 |
| 4 | **Bootstrap 5** | Framework CSS para el diseño de interfaces web responsivas con componentes visuales predefinidos. | Framework CSS | 1 |
| 5 | **JavaScript ES6** | Lenguaje de programación del lado del cliente, organizado en módulos por dominio funcional. | Lenguaje | 1 |
| 6 | **Git / GitHub** | Sistema de control de versiones distribuido y plataforma de colaboración para gestión del código fuente. | Control Versiones | 1 |
| 7 | **Visual Studio Code** | Editor de código fuente con soporte para PHP, JavaScript, extensiones y depuración integrada. | IDE | 1 |
| 8 | **API RENIEC** | Interfaz externa utilizada para validar automáticamente los datos personales de clientes mediante DNI. | API Externa | 1 |

---

# CAPÍTULO III – ANÁLISIS DE LA SITUACIÓN ACTUAL

## 3. Análisis de la Situación Actual

### 3.1 Descripción del Problema y/o Necesidad

Ronceros Consultores & Editores S.R.L. enfrentaba una problemática operativa en su área comercial, originada por la ausencia de herramientas digitales para gestionar el proceso de atención al cliente, cotización y contratación de servicios fotográficos. Esta carencia generaba desorganización en la elaboración de presupuestos, pérdida de información de clientes, dificultad para hacer seguimiento al estado de cada cotización y contrato, así como conflictos en la programación de sesiones fotográficas. Ante esta situación, se evidenció la necesidad de implementar un sistema web centralizado que digitalizara y automatizara estos procesos.

### 3.2 Beneficiarios Directos de la Empresa

Los principales beneficiarios del sistema implementado son:

- **El personal de ventas (Vendedores):** Podrán generar cotizaciones precisas y rápidas, gestionar contratos y dar seguimiento al estado de cada cliente desde una interfaz centralizada.
- **Los fotógrafos:** Tendrán visibilidad de sus sesiones asignadas a través del calendario, podrán registrar asistencia y consultar los detalles de cada cliente y paquete.
- **La gerencia y supervisores:** Dispondrán de un panel con reportes y estadísticas en tiempo real sobre cotizaciones, contratos activos y pagos, facilitando la toma de decisiones estratégicas.
- **Los clientes:** Recibirán una atención más rápida, precisa y profesional, con contratos bien elaborados y comunicación oportuna sobre el estado de sus servicios.

### 3.3 Efectos del Problema en el Área de Trabajo

La ausencia de un sistema centralizado para la gestión comercial generaba los siguientes efectos negativos:

- Demoras de hasta 35 minutos por cotización, debido a la búsqueda manual de información y elaboración en Word/Excel.
- Errores frecuentes en los montos de cotizaciones por cálculo manual incorrecto de descuentos y reglas de precios.
- Pérdida de datos de clientes por uso de documentos físicos y archivos no centralizados.
- Conflictos de horarios en sesiones fotográficas por uso de agendas físicas sin visibilidad compartida.
- Dificultad para conocer el estado de pagos y saldos pendientes de contratos activos.

**Tabla 6: Efectos del Problema y/o Necesidad**

| EFECTOS O CONSECUENCIAS | NIVEL DE IMPACTO |
|---|---|
| Demoras en cotización y generación de contratos | 4 |
| Errores en cálculo de precios y descuentos | 4 |
| Pérdida o inconsistencia de datos de clientes | 3 |
| Conflictos en programación de sesiones fotográficas | 3 |
| Dificultad en seguimiento de pagos y saldos | 4 |

### 3.4 Análisis de las Causas Raíz – Diagrama de Ishikawa

**Problema central:** Gestión ineficiente del proceso de cotización, contratación y flujo de trabajo en Ronceros Fotografía.

**Causas identificadas:**

**MÉTODO:**
- Proceso de cotización completamente manual (Word/Excel).
- Sin plantillas estandarizadas para contratos.
- Reglas de precios no documentadas ni automatizadas.

**MANO DE OBRA:**
- Personal sin herramienta digital para gestión de clientes.
- Vendedores sin acceso a información de paquetes actualizada en tiempo real.
- Sin capacitación en herramientas de gestión comercial digital.

**MATERIAL / INFORMACIÓN:**
- Datos de clientes dispersos en archivos físicos y digitales.
- Sin validación de datos al registrar clientes.
- Historial de cotizaciones y contratos no centralizado.

**MÁQUINA / SISTEMA:**
- Sin sistema web integrado para gestión comercial.
- Sin calendario digital para sesiones fotográficas.
- Sin módulo de control de pagos vinculado a contratos.

**MEDIO AMBIENTE:**
- Comunicación interna por canales informales (WhatsApp, verbal).
- Sin control de acceso diferenciado por rol.
- Sin reportes automáticos para toma de decisiones.

### 3.5 Diagrama de Pareto – Priorización de Causas Raíz

**Tabla 7: Matriz de Priorización de Causas**

| CÓDIGO | CAUSA | FRECUENCIA | IMPORTANCIA | FACTIBILIDAD | TOTAL |
|---|---|---|---|---|---|
| CA01 | Proceso manual de cotización en Word/Excel | 5 | 5 | 5 | 15 |
| CA02 | Sin sistema centralizado de clientes y contratos | 5 | 5 | 5 | 15 |
| CA03 | Sin aplicación automática de reglas de precios | 4 | 5 | 5 | 14 |
| CA04 | Sin calendario digital para sesiones fotográficas | 4 | 4 | 5 | 13 |
| CA05 | Sin control de pagos vinculado a contratos | 3 | 5 | 4 | 12 |
| CA06 | Sin control de acceso diferenciado por roles | 3 | 4 | 4 | 11 |

**Tabla 8: Priorización de Causa Raíz (Pareto)**

| CAUSA | DESCRIPCIÓN | FRECUENCIA | % ACUMULADO | ACUMULADO |
|---|---|---|---|---|
| CA01 | Proceso manual de cotización | 15 | 19% | 15 |
| CA02 | Sin sistema centralizado | 15 | 38% | 30 |
| CA03 | Sin reglas de precios automáticas | 14 | 56% | 44 |
| CA04 | Sin calendario de sesiones | 13 | 73% | 57 |
| CA05 | Sin control de pagos | 12 | 88% | 69 |
| CA06 | Sin control de acceso por roles | 11 | 100% | 80 |

> Las causas CA01 a CA04 representan el 73% del problema acumulado, confirmando que la implementación de un sistema web integrado con módulos de cotización, gestión de clientes, precios automáticos y calendario es la acción prioritaria.

---

# CAPÍTULO IV – PROPUESTA TÉCNICA DE LA MEJORA

## 4. Propuesta Técnica de la Mejora

### 4.1 Plan de Acción de la Mejora Propuesta

**Tabla 9: Plan de Acción de la Mejora Propuesta**

| Propuesta de innovación | DESARROLLO E IMPLEMENTACIÓN DE UN SISTEMA WEB DE GESTIÓN DE CONTRATOS Y FLUJO DE TRABAJO PARA RONCEROS CONSULTORES & EDITORES S.R.L. |
|---|---|
| **Responsables** | Ayala Romo, Daniel Jesús – Félix Tipacti, Diggy Tony Jesús |
| **Período** | Febrero 2025 – Junio 2025 |

| Actividad | Responsable | ¿Cómo? | ¿Dónde? | ¿Por qué? |
|---|---|---|---|---|
| Planificación y análisis del proyecto | Practicantes | Reuniones con el gerente y personal de la empresa | Área comercial | Para levantar requerimientos del sistema |
| Diseño de base de datos | Practicantes | Modelado con StarUML (diagramas ER) | Entorno virtual | Para definir entidades y relaciones del negocio |
| Diseño de wireframes y mockups | Practicantes | Figma para diseño de interfaces | Entorno virtual | Para validar la experiencia de usuario antes de codificar |
| Desarrollo del backend | Practicantes | CodeIgniter 4, PHP, MySQL | Entorno virtual | Para implementar la lógica de negocio y API REST |
| Desarrollo del frontend | Practicantes | Bootstrap 5, JavaScript ES6, módulos por dominio | Entorno virtual | Para construir las interfaces interactivas del sistema |
| Integración API RENIEC | Practicantes | Consumo del servicio RENIEC con clave en .env | Entorno virtual | Para validar automáticamente datos de clientes |
| Testeo de la aplicación | Practicantes / Gerente | Pruebas funcionales con datos reales de la empresa | Área de desarrollo | Para verificar que el sistema no tenga errores |
| Implementación en producción | Practicantes | Hosting y dominio configurados | Servidor web | Para poner el sistema en uso real por la empresa |
| Documentación | Practicantes | Redacción de manual de usuario y técnico | Entorno virtual | Para facilitar el uso y mantenimiento del sistema |

### 4.2 Consideraciones Técnicas para la Implementación

#### 4.2.1 Arquitectura del Sistema

El sistema implementa una arquitectura por capas basada en el patrón **MVC (Modelo-Vista-Controlador)** de CodeIgniter 4:

**Capa Web (Rutas → Controladores → Vistas):**
- Las rutas web redirigen las solicitudes a los controladores correspondientes.
- Los controladores renderizan las vistas inyectando el layout (header/footer) y los datos necesarios.
- El frontend se construye con módulos ES6 independientes por dominio funcional.

**Capa API (Rutas api/* → Controladores API → Servicios → Modelos → Transformers):**
- Las rutas API reciben peticiones JSON del frontend.
- Los servicios encapsulan la lógica de negocio y las transacciones de base de datos.
- Los transformers formatean las respuestas JSON con estructura estándar: `{ status, data, message }`.

**Sistema de autenticación:**
- Autenticación con bcrypt y control de intentos de login (Throttler: 10 intentos/IP, bloqueo de 15 min).
- Sesiones protegidas con `session()->regenerate(true)` en cada login.
- Filtro `AuthFilter` que protege todas las rutas excepto `/login` y `/logout`.

#### 4.2.2 Módulos del Sistema

**Módulo de Clientes:**
- Registro y edición de personas naturales con validación mediante API RENIEC.
- Gestión de apoderados vinculados a estudiantes para paquetes escolares.

**Módulo de Paquetes:**
- Gestión de paquetes fotográficos organizados por nivel: `inicial-primaria`, `secundaria`, `postgrado`, `otro`.
- Cada paquete tiene productos y sesiones asociadas, con estado activo/inactivo.
- Reglas y beneficios configurables por paquete.

**Módulo de Cotizaciones:**
- Generación de cotizaciones con aplicación automática de reglas de precios.
- Estados: borrador, enviada, aprobada, rechazada, contratada.
- Detalle de ítems, montos y beneficios aplicados.

**Módulo de Contratos:**
- Generación automática desde cotizaciones aprobadas.
- Seguimiento del estado del contrato durante todo el flujo de trabajo.

**Módulo de Sesiones Fotográficas:**
- Calendario de sesiones con asignación de fotógrafo.
- Control de asistencia por sesión.

**Módulo de Pagos:**
- Registro de pagos parciales y totales vinculados a contratos.
- Múltiples formas de pago configurables.

**Panel de Administración:**
- Gestión de usuarios con roles: Administrador, Vendedor, Fotógrafo, Supervisor.
- Reportes y estadísticas de cotizaciones, contratos y pagos.

### 4.3 Diagrama de Análisis de Proceso (DAP)

#### 4.3.1 DAP – Situación Actual (Proceso Manual)

| **DIAGRAMA ANALÍTICO DEL PROCESO** | |
|---|---|
| **Método:** Tradicional (Manual) | **Tiempo total:** 35 minutos |

| # | DESCRIPCIÓN DEL PROCESO | DISTANCIA (m) | TIEMPO (min) | TIPO |
|---|---|---|---|---|
| 01 | El cliente llama o visita la empresa solicitando información | — | 2 | Operación |
| 02 | El vendedor busca manualmente los paquetes disponibles en archivos | — | 5 | Inspección |
| 03 | El vendedor registra los datos del cliente en papel o Excel | — | 4 | Operación |
| 04 | El vendedor elabora la cotización manualmente en Word/Excel | — | 10 | Operación |
| 05 | Se imprime o envía la cotización al cliente por WhatsApp | 2 | 3 | Transporte |
| 06 | Se espera respuesta del cliente y se realizan ajustes manuales | — | 5 | Espera |
| 07 | El vendedor redacta el contrato manualmente en Word | — | 4 | Operación |
| 08 | Se agenda la sesión en la agenda física o calendario compartido | — | 2 | Operación |
| **TOTAL** | | **2 m** | **35 min** | |

#### 4.3.2 DAP – Situación Propuesta (Con Sistema Web)

| **DIAGRAMA ANALÍTICO DEL PROCESO** | |
|---|---|
| **Método:** Sistema Web (Propuesto) | **Tiempo total:** 12 minutos |

| # | DESCRIPCIÓN DEL PROCESO | DISTANCIA (m) | TIEMPO (min) | TIPO |
|---|---|---|---|---|
| 01 | El vendedor accede al sistema e ingresa el DNI del cliente | — | 1 | Operación |
| 02 | El sistema consulta la API RENIEC y completa los datos automáticamente | — | 1 | Operación |
| 03 | El vendedor selecciona el paquete y el sistema calcula precios automáticamente | — | 2 | Operación |
| 04 | El sistema genera la cotización con reglas y beneficios aplicados | — | 1 | Operación |
| 05 | El vendedor revisa y envía la cotización al cliente | — | 1 | Operación |
| 06 | El cliente aprueba y el sistema genera el contrato automáticamente | — | 1 | Operación |
| 07 | El vendedor programa la sesión en el calendario del sistema | — | 2 | Operación |
| 08 | El sistema registra el pago inicial y genera el comprobante | — | 2 | Operación |
| 09 | El sistema actualiza el estado del contrato y notifica al fotógrafo | — | 1 | Operación |
| **TOTAL** | | **0 m** | **12 min** | |

### 4.4 Cronograma de Ejecución de la Mejora

| # | FASE / ACTIVIDAD | DURACIÓN | INICIO | FIN |
|---|---|---|---|---|
| 1 | **PLANIFICACIÓN** | 7 días | 10/02/2025 | 17/02/2025 |
| 2 | Formalización del convenio de prácticas | 1 día | 10/02/2025 | 10/02/2025 |
| 3 | Identificación del área a desarrollar | 1 día | 11/02/2025 | 11/02/2025 |
| 4 | Reunión con el gerente y personal de la empresa | 1 día | 12/02/2025 | 12/02/2025 |
| 5 | Recolección de datos y levantamiento de requerimientos | 3 días | 13/02/2025 | 15/02/2025 |
| 6 | **ANÁLISIS** | 10 días | 17/02/2025 | 27/02/2025 |
| 7 | Análisis del proceso actual y documentación | 5 días | 17/02/2025 | 21/02/2025 |
| 8 | Entrevistas con vendedores y fotógrafos | 2 días | 24/02/2025 | 25/02/2025 |
| 9 | Definición de requerimientos funcionales y no funcionales | 3 días | 26/02/2025 | 28/02/2025 |
| 10 | **DISEÑO** | 8 días | 03/03/2025 | 12/03/2025 |
| 11 | Diseño del modelo de base de datos (StarUML) | 3 días | 03/03/2025 | 05/03/2025 |
| 12 | Diseño de wireframes y mockups (Figma) | 3 días | 06/03/2025 | 10/03/2025 |
| 13 | Revisión y validación del diseño con el cliente | 2 días | 11/03/2025 | 12/03/2025 |
| 14 | **DESARROLLO** | 55 días | 13/03/2025 | 22/05/2025 |
| 15 | Configuración del entorno (CI4, MySQL, Git) | 2 días | 13/03/2025 | 14/03/2025 |
| 16 | Desarrollo módulo de autenticación y roles | 5 días | 17/03/2025 | 21/03/2025 |
| 17 | Desarrollo módulo de clientes + integración RENIEC | 7 días | 24/03/2025 | 01/04/2025 |
| 18 | Desarrollo módulo de paquetes y reglas de precios | 7 días | 02/04/2025 | 10/04/2025 |
| 19 | Desarrollo módulo de cotizaciones | 8 días | 11/04/2025 | 22/04/2025 |
| 20 | Desarrollo módulo de contratos | 5 días | 23/04/2025 | 29/04/2025 |
| 21 | Desarrollo módulo de sesiones y calendario | 7 días | 30/04/2025 | 08/05/2025 |
| 22 | Desarrollo módulo de pagos y reportes | 7 días | 09/05/2025 | 16/05/2025 |
| 23 | Integración de módulos y pruebas | 5 días | 19/05/2025 | 22/05/2025 |
| 24 | **IMPLEMENTACIÓN** | 10 días | 23/05/2025 | 06/06/2025 |
| 25 | Despliegue en servidor de producción | 2 días | 23/05/2025 | 26/05/2025 |
| 26 | Capacitación al personal | 3 días | 27/05/2025 | 29/05/2025 |
| 27 | Pruebas con datos reales y ajustes finales | 3 días | 02/06/2025 | 04/06/2025 |
| 28 | Entrega oficial del proyecto | 1 día | 06/06/2025 | 06/06/2025 |

---

# CAPÍTULO V – COSTOS DE IMPLEMENTACIÓN

## 5. Costo de Implementación de la Mejora

### 5.1 Costo de Materiales

**Tabla 10: Costo de Materiales**

| Ítem | Descripción | Cantidad | Costo Unitario (S/) | Costo Total (S/) |
|---|---|---|---|---|
| 1 | Libreta de apuntes | 2 | 4.00 | 8.00 |
| 2 | Lapicero | 2 | 2.50 | 5.00 |
| 3 | Lápiz | 2 | 2.00 | 4.00 |
| 4 | Borrador | 1 | 0.50 | 0.50 |
| | | | **TOTAL** | **S/ 17.50** |

### 5.2 Costo de Mano de Obra

**Tabla 11: Costo de Mano de Obra**

| Ítem | Fase | Duración | Inversión (S/) |
|---|---|---|---|
| 1 | Planificación y análisis | 2 semanas | 600 |
| 2 | Diseño (BD, wireframes) | 1 semana | 600 |
| 3 | Desarrollo backend (CI4 + API) | 5 semanas | 3,000 |
| 4 | Desarrollo frontend (JS + Bootstrap) | 4 semanas | 2,400 |
| 5 | Pruebas e integración | 1 semana | 600 |
| 6 | Implementación y capacitación | 1 semana | 600 |
| | | **TOTAL** | **S/ 7,800** |

### 5.3 Costo de Herramientas, Máquinas y Equipos

**Tabla 12: Costo de Herramientas (Software)**

| Ítem | Descripción | Tipo | Valor (S/) |
|---|---|---|---|
| 1 | Visual Studio Code | Open Source | 0 |
| 2 | MySQL / XAMPP | Open Source / Free | 0 |
| 3 | CodeIgniter 4 | Open Source (MIT) | 0 |
| 4 | Bootstrap 5 | Open Source (MIT) | 0 |
| 5 | Git / GitHub | Free | 0 |
| 6 | Figma (plan gratuito) | Free | 0 |
| | | **TOTAL** | **S/ 0** |

**Tabla 13: Costo de Equipos**

| Ítem | Descripción | Uso | Valor (S/) |
|---|---|---|---|
| 1 | Laptop – Intel Core i5, 8GB RAM, 256GB SSD (*) | Desarrollo del sistema | 0 |
| 2 | PC – Recepción (*) | Acceso al sistema por personal de ventas | 0 |
| 3 | PC – Administración (*) | Gestión de usuarios y reportes | 0 |

_(*) Equipos ya disponibles en la empresa y en los practicantes._

### 5.4 Otros Costos de Implementación

**Tabla 14: Otros Costos**

| Ítem | Descripción | Valor (S/) |
|---|---|---|
| 1 | Hosting compartido + dominio (1 año) | 100.00 |
| 2 | Clave API RENIEC (DECOLECTA) | 0 |
| | **TOTAL** | **S/ 100.00** |

**Especificaciones del Hosting:**

| Característica | Cantidad |
|---|---|
| Espacio en Disco | 10 GB |
| Transferencia | Ilimitada |
| Bases de Datos MySQL | Ilimitadas |
| Certificado SSL | 1 |
| PHP | 8.x |

### 5.5 Costo Total de la Implementación

**Tabla 15: Costo Total**

| Concepto | Costo (S/) |
|---|---|
| Costo de materiales | 17.50 |
| Costo de mano de obra | 7,800.00 |
| Costo de herramientas | 0.00 |
| Hosting y dominio | 100.00 |
| **Costo total de la implementación** | **S/ 7,917.50** |

---

# CAPÍTULO VI – EVALUACIÓN TÉCNICA Y ECONÓMICA

## 6. Evaluación Técnica y Económica de la Mejora

### 6.1 Beneficio Técnico y/o Económico Esperado

**Tabla 16: Comparación DAP Actual vs. Propuesto**

| Sistema Actual | Sistema Mejorado | Ahorro por proceso |
|---|---|---|
| 35 minutos | 12 minutos | **23 minutos** |

> "Este proceso se realiza aproximadamente **10 veces al día**, lo que representa un ahorro de **230 minutos diarios** (3.83 horas)."

**Tabla 17: Tiempo Optimizado**

| Período | Horas ahorradas |
|---|---|
| Al día | 3.83 h |
| A la semana | 22.98 h |
| Al mes | 91.92 h |
| Al año | 1,103.04 h |

### 6.2 Relación Beneficio / Costo

Para calcular la relación Beneficio – Costo, se toma en cuenta el beneficio económico generado por el tiempo liberado del personal, valorizado según el costo de la hora de trabajo del vendedor (S/ 6.25/hora, equivalente a un sueldo de S/ 1,000 mensuales con 160 horas/mes).

**Tabla 18: Beneficio / Costo**

| Beneficio económico por 2 años | Costo total de la implementación |
|---|---|
| 2,206.08 horas × S/ 6.25 = **S/ 13,788.00** | **S/ 7,917.50** |

```
B/C = 13,788.00 / 7,917.50 = 1.74
```

> Según este índice, el retorno de la inversión se obtendrá antes de finalizar el primer año de implementación.

**Tabla 19: Proyección de Beneficio Acumulado**

| Año | Beneficio Anual (S/) | Beneficio Acumulado (S/) | Costo de la Mejora (S/) |
|---|---|---|---|
| Año 1 | 6,894.00 | 6,894.00 | 7,917.50 |
| Año 2 | 6,894.00 | 13,788.00 | 7,917.50 |

**Beneficios no cuantificables:**

Además de los beneficios económicos directos, el sistema aporta valor estratégico no monetizable:

- **Reducción de errores en cotizaciones:** La aplicación automática de reglas de precios elimina inconsistencias y reclamos de clientes por montos incorrectos.
- **Mejora de la imagen profesional:** La generación automática de contratos y cotizaciones bien estructuradas eleva la percepción de profesionalismo de la empresa ante los clientes.
- **Mayor satisfacción del cliente:** La rapidez en la atención y la transparencia en la información generan mayor confianza y fidelización.
- **Control y seguridad de la información:** El sistema centralizado con autenticación segura y control de roles protege los datos de la empresa y sus clientes.
- **Escalabilidad del negocio:** La empresa puede gestionar un mayor volumen de clientes y contratos sin incrementar proportionalmente la carga operativa.

---

# CAPÍTULO VII – CONCLUSIONES

## 7. Conclusiones

### 7.1 Conclusiones Respecto a los Objetivos del Proyecto de Innovación y/o Mejora

El desarrollo e implementación del Sistema Web de Gestión de Contratos y Flujo de Trabajo para Ronceros Consultores & Editores S.R.L. representa un avance significativo en la modernización y eficiencia operativa de la empresa. Los resultados obtenidos permiten concluir lo siguiente:

1. **Se digitalizó y centralizó la gestión comercial:** El sistema reemplazó exitosamente los procesos manuales en Word/Excel por módulos digitales integrados, eliminando la dispersión de información y reduciendo el tiempo de atención por cotización de 35 a 12 minutos, un ahorro del 66%.

2. **Se automatizaron los procesos clave:** La integración con la API de RENIEC para validación de clientes, la aplicación automática de reglas de precios en cotizaciones y la generación automática de contratos eliminaron tareas repetitivas y propensas a errores.

3. **Se mejoró la trazabilidad del flujo de trabajo:** El sistema de estados por cotización y contrato, combinado con el panel de reportes, brinda visibilidad en tiempo real a la gerencia y al personal sobre el estado de cada operación comercial.

4. **Se organizó la programación de sesiones:** El calendario digital de sesiones fotográficas centralizó la asignación de fotógrafos y el control de asistencia, eliminando conflictos de horarios y pérdida de información en agendas físicas.

5. **Se implementó un control de acceso robusto:** El sistema de roles diferenciados (Administrador, Vendedor, Fotógrafo, Supervisor) garantiza la seguridad y la integridad de la información, asignando a cada usuario únicamente las funciones que le corresponden.

6. **La inversión es económicamente viable:** Con una relación Beneficio/Costo de 1.74 y un retorno proyectado en menos de 12 meses, el proyecto demuestra ser financieramente sostenible y beneficioso para la empresa.

---

# CAPÍTULO VIII – RECOMENDACIONES

## 8. Recomendaciones

### 8.1 Recomendaciones para Ronceros Consultores & Editores S.R.L.

- **Capacitar continuamente al personal:** Se recomienda realizar sesiones de repaso periódicas para el personal nuevo y actualizaciones cuando se incorporen nuevas funcionalidades al sistema, asegurando el uso correcto de todos los módulos.

- **Realizar respaldos periódicos de la base de datos:** Aunque el sistema almacena la información en un servidor con respaldo, se recomienda establecer un protocolo interno de copias de seguridad mensuales almacenadas en almacenamiento en la nube (Google Drive o similar) para garantizar la disponibilidad de la información ante cualquier eventualidad.

- **Monitorear el rendimiento del sistema:** Es conveniente revisar periódicamente el comportamiento del sistema web, identificando posibles cuellos de botella, errores en los logs o tiempos de respuesta elevados, con el fin de mantener la operatividad en condiciones óptimas.

- **Mantener actualizada la clave de la API RENIEC:** La integración con RENIEC requiere una clave de acceso activa. Se recomienda revisar la vigencia de la suscripción y renovarla oportunamente para evitar interrupciones en el registro de clientes.

- **Considerar la implementación de módulos adicionales:** A futuro, se recomienda evaluar la incorporación de módulos de notificaciones automáticas por WhatsApp/correo, firma digital de contratos y una interfaz de consulta pública para clientes, lo cual elevaría aún más la experiencia de servicio.

---

## REFERENCIAS BIBLIOGRÁFICAS

- Aiquipa Tello, A. A. (2018). *Diseño e implementación de una plataforma web para la gestión de servicios en empresas de fotografía*. Universidad César Vallejo. https://repositorio.ucv.edu.pe/handle/20.500.12692/28104

- Pérez Yacsavilca, J. A. (2021). *Sistema web para la gestión de contratos de servicios con tecnologías modernas*. Universidad César Vallejo. https://repositorio.ucv.edu.pe/handle/20.500.12692/76029

- Reyna Cama, S. C. (2018). *Sistema web para el control y seguimiento de servicios fotográficos aplicando metodología ágil*. Universidad César Vallejo. https://repositorio.ucv.edu.pe/handle/20.500.12692/41135

- Iza Viracocha, J. A. (2021). *Solución web para la administración de contratos y servicios con Laravel y MySQL*. Escuela Politécnica Nacional. https://bibdigital.epn.edu.ec/handle/15000/21405

- British Columbia Institute of Technology. (2020). *CodeIgniter 4 User Guide*. CodeIgniter Foundation. https://codeigniter.com/user_guide/

- Red Hat. (2021). *What is an API?* https://www.redhat.com/en/topics/api/what-are-application-programming-interfaces

- W3Schools. (2024). *PHP Introduction*. https://www.w3schools.com/php

- GitHub Docs. (2024). *About GitHub*. https://docs.github.com/en/get-started

- Visual Studio Code, Microsoft. (s.f.). *Code editing. Redefined*. https://code.visualstudio.com/

- RENIEC – Registro Nacional de Identificación y Estado Civil. (2024). *Servicios de consulta de datos*. https://www.reniec.gob.pe/

- Oracle. (s.f.). *MySQL Documentation*. https://dev.mysql.com/doc/

- Bootstrap. (2024). *Bootstrap 5 Documentation*. https://getbootstrap.com/docs/5.0/

---

*Documento generado para el Proyecto de Innovación y/o Mejora – SENATI Dirección Zonal Ica-Ayacucho – 2026*
