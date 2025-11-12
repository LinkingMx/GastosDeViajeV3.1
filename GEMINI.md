# Análisis del Proyecto: Sistema de Gestión de Gastos de Viaje (GastosDeViajeV3.1)

## 1. Resumen General y Tecnologías

**Propósito del Proyecto:**
Esta aplicación, desarrollada en Laravel, tiene como objetivo principal gestionar el ciclo de vida completo de las solicitudes de gastos de viaje. Abarca desde la creación de la solicitud por parte de un empleado, pasando por múltiples niveles de aprobación (departamental, equipo de viajes), la gestión de anticipos por parte de tesorería, hasta la comprobación final de gastos y el reembolso.

**Pila Tecnológica Principal:**
*   **Backend:** PHP 8.2, Laravel 12
*   **Frontend & Panel de Administración:** FilamentPHP 3.3 (utilizando Livewire, Alpine.js, y Tailwind CSS)
*   **Base de Datos:** SQLite (para desarrollo local), con migraciones preparadas para MySQL.
*   **Frontend Assets:** Vite para la compilación de assets (CSS & JS).
*   **Autenticación y Permisos:** `bezhansalleh/filament-shield` para una gestión robusta de roles y permisos.
*   **Testing:** PestPHP.

---

## 2. Análisis Detallado de Recursos y Modelos

A continuación se desglosan las entidades principales del sistema, su propósito, estructura y relaciones.

### Módulo de Administración y Configuración

Estos recursos establecen la estructura organizativa y las reglas de negocio básicas.

*   **User (Usuario):**
    *   **Propósito:** Representa a los empleados y administradores del sistema. Es el actor principal que inicia las solicitudes.
    *   **Relaciones Clave:** `belongsTo` `Department`, `Position`, `Bank`. Tiene una auto-relación para `overrideAuthorizer` (un autorizador específico que anula al del departamento).
    *   **Lógica de Negocio:** Contiene la lógica para determinar el autorizador de un usuario. Incluye campos booleanos (`travel_team`, `treasury_team`) para asignar roles funcionales especiales que definen flujos de trabajo y permisos.

*   **Department (Departamento):**
    *   **Propósito:** Agrupa a los usuarios en unidades organizativas. Cada departamento tiene un autorizador por defecto.
    *   **Relaciones Clave:** `hasMany` `User`, `belongsTo` `User` (como `authorizer`).
    *   **Lógica de Negocio:** Define la primera línea de autorización en el flujo de solicitudes de viaje.

*   **Position (Posición):**
    *   **Propósito:** Define el rol o cargo de un usuario. Es crucial para determinar los viáticos (`PerDiem`) que le corresponden.
    *   **Relaciones Clave:** `hasMany` `User`, `hasMany` `PerDiem`.
    *   **Lógica de Negocio:** El nivel (`level`) jerárquico puede ser usado para futuras reglas de autorización o políticas de gasto.

*   **Branch (Sucursal / Centro de Costo):**
    *   **Propósito:** Representa una ubicación física o un centro de costos al que se asocian los gastos.
    *   **Relaciones Clave:** No tiene relaciones directas con otros modelos, pero es un campo clave en `TravelRequest`.
    *   **Lógica de Negocio:** Incluye validaciones para el `ceco` (código de centro de costo) y `tax_id` (RFC), diferenciando entre sucursales fiscales y centros de costo operativos.

*   **Country (País):**
    *   **Propósito:** Define las ubicaciones de origen y destino, y determina si un viaje es `domestic` o `foreign`.
    *   **Relaciones Clave:** Usado en `TravelRequest` para `originCountry` y `destinationCountry`.
    *   **Lógica de Negocio:** El booleano `is_foreign` es fundamental para calcular los viáticos correctos.

*   **Bank (Banco):**
    *   **Propósito:** Catálogo de bancos para la información financiera de los usuarios (pago de anticipos y reembolsos).
    *   **Relaciones Clave:** `hasMany` `User`.
    *   **Lógica de Negocio:** Incluye validación de formato para la CLABE interbancaria.

### Módulo de Configuración de Gastos

Estos recursos definen la estructura y las reglas para los gastos que se pueden solicitar.

*   **ExpenseConcept (Concepto de Gasto):**
    *   **Propósito:** Define las categorías generales de gastos (ej. "Hospedaje", "Alimentación", "Gastos Varios").
    *   **Relaciones Clave:** `hasMany` `ExpenseDetail`.
    *   **Lógica de Negocio:** El campo `is_unmanaged` es crítico. Si es `false` (gestionado), el gasto es manejado por la empresa (ej. reserva de hotel centralizada). Si es `true` (no gestionado), permite al usuario agregar detalles específicos y solicitar un monto.

*   **ExpenseDetail (Detalle de Gasto):**
    *   **Propósito:** Define sub-categorías específicas para los conceptos "no gestionados". Por ejemplo, para el concepto "Gastos Varios", los detalles podrían ser "Propinas" o "Lavandería".
    *   **Relaciones Clave:** `belongsTo` `ExpenseConcept`.
    *   **Lógica de Negocio:** Solo pueden existir para conceptos con `is_unmanaged = true`. El campo `priority` ayuda a ordenar los gastos en los formularios.

*   **PerDiem (Viático):**
    *   **Propósito:** Define las tarifas de viáticos diarios. Es la intersección de una `Position`, un `ExpenseDetail`, un `scope` (nacional/extranjero) y una `currency`.
    *   **Relaciones Clave:** `belongsTo` `Position`, `belongsTo` `ExpenseDetail`.
    *   **Lógica de Negocio:** Es el núcleo del cálculo automático de gastos. El sistema busca el `PerDiem` aplicable basado en el puesto del usuario, el tipo de viaje y las fechas.

*   **AttachmentType (Tipo de Documento):**
    *   **Propósito:** Define los tipos de archivos que se pueden adjuntar a una solicitud (ej. "Reserva de Vuelo", "Comprobante de Depósito").
    *   **Relaciones Clave:** `hasMany` `TravelRequestAttachment`.
    *   **Lógica de Negocio:** Permite organizar y clasificar los documentos adjuntos a lo largo del ciclo de vida de la solicitud.

### Módulo Principal de Operaciones

Este es el corazón de la aplicación, donde se gestiona el flujo de trabajo principal.

*   **TravelRequest (Solicitud de Viaje):**
    *   **Propósito:** Es la entidad central que encapsula toda la información de un viaje.
    *   **Relaciones Clave:** `belongsTo` `User`, `Branch`, `Country`. `hasMany` `TravelRequestComment`, `TravelRequestAttachment`, `ExpenseVerification`.
    *   **Lógica de Negocio (Flujo de Trabajo):**
        1.  **`draft`**: El usuario crea la solicitud. Se puede editar y guardar.
        2.  **`pending`**: El usuario envía la solicitud. Pasa al autorizador del departamento.
        3.  **`approved`**: El autorizador aprueba. La solicitud pasa automáticamente a `travel_review`.
        4.  **`rejected`**: El autorizador rechaza. El usuario puede moverla a `revision`.
        5.  **`revision`**: El usuario edita la solicitud rechazada para reenviarla.
        6.  **`travel_review`**: El equipo de viajes revisa la logística.
        7.  **`travel_approved`**: Aprobación final. El equipo de tesorería es notificado para el pago del anticipo.
        8.  **`travel_rejected`**: El equipo de viajes rechaza.
        9.  **`pending_verification`**: Tesorería marca el anticipo como pagado. El usuario debe ahora comprobar los gastos.
    *   **Datos Almacenados:** Guarda en campos JSON (`additional_services`, `per_diem_data`, `custom_expenses_data`) una "foto" de los gastos solicitados en el momento de la creación.

*   **ExpenseVerification (Comprobación de Gastos):**
    *   **Propósito:** Entidad donde el usuario, después del viaje, sube los comprobantes (facturas, recibos) para justificar los gastos contra el anticipo recibido.
    *   **Relaciones Clave:** `belongsTo` `TravelRequest`, `User`. `hasMany` `ExpenseReceipt`.
    *   **Lógica de Negocio:** Tiene su propio flujo de aprobación (borrador, pendiente, aprobado, rechazado) manejado por el equipo de viajes/contabilidad. Calcula automáticamente si se necesita un reembolso para el empleado o si el empleado debe devolver dinero.

*   **ExpenseReceipt (Comprobante de Gasto):**
    *   **Propósito:** Representa un único comprobante (CFDI, foto de recibo, etc.) subido por el usuario.
    *   **Relaciones Clave:** `belongsTo` `ExpenseVerification`.
    *   **Lógica de Negocio:** Diferencia entre `fiscal` (CFDI) y `non_deductible`. Incluye un servicio (`CfdiParserService`) para leer archivos XML y extraer datos automáticamente.

*   **TravelRequestAttachment y TravelRequestComment:**
    *   **Propósito:** Modelos de soporte que registran los archivos adjuntos y el historial de comentarios de una `TravelRequest`, proporcionando trazabilidad completa.

---

## 3. Flujos de Trabajo Principales

1.  **Creación y Aprobación de Solicitud:**
    *   Un `User` crea una `TravelRequest` (estado `draft`).
    *   El sistema calcula los `PerDiem` aplicables según la `Position` del usuario y el `Country` de destino.
    *   El usuario envía la solicitud (estado `pending`).
    *   El `authorizer` del `Department` (o el `overrideAuthorizer` del usuario) recibe una notificación.
    *   El autorizador aprueba (estado `approved`, luego `travel_review`) o rechaza (estado `rejected`).
    *   El equipo de viajes (`User` con `travel_team = true`) revisa y da la aprobación final (estado `travel_approved`).

2.  **Gestión de Anticipo:**
    *   Una vez en `travel_approved`, el equipo de tesorería (`User` con `treasury_team = true`) es notificado.
    *   Tesorería realiza el depósito y lo registra en la `TravelRequest`, cambiando el estado a `pending_verification`.

3.  **Comprobación de Gastos:**
    *   Después del viaje, el `User` crea una `ExpenseVerification` asociada a la `TravelRequest`.
    *   El usuario sube `ExpenseReceipts` (XML, PDF, imágenes).
    *   El sistema compara los gastos solicitados con los comprobados.
    *   La comprobación se envía a revisión y es aprobada por el equipo de contabilidad/viajes.
    *   Si hay diferencias, se gestiona un reembolso o una devolución.

Este análisis proporciona un contexto profundo y estructurado del proyecto, ideal para que un agente de IA pueda comprender su arquitectura, lógica de negocio y flujos de trabajo para proponer mejoras o rediseños.