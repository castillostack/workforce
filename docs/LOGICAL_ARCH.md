# Documento de Arquitectura de Software (SAD)
**Estilo Arquitectónico:** Monolito Modular en Capas (Layered Architecture).  
**Tecnología Base:** Laravel 11.x, PHP 8.2+, PostgreSQL, Eloquent ORM.

## 1. Vista Lógica (Capas del Sistema)

El sistema se divide en cuatro capas horizontales. La regla de dependencia es estricta: *una capa solo puede comunicarse con la capa inmediatamente inferior.*

### Capa 1: Presentación (Presentation Layer)
Responsable de la interacción con el usuario (UI) y la entrada de peticiones HTTP. No contiene lógica de negocio.
*   **Componentes Laravel (Controllers/Routes):**
    *   `AuthController`: Manejo de login, logout y sesiones.
    *   `DashboardController`: Vistas principales para Operadores y Jefes.
    *   `ScheduleController`: Vistas de calendario y formularios de solicitud de cambios.
    *   `ReportingController`: Visualización de gráficos y tablas de adherencia.
*   **Artefactos:** Templates Blade, Formularios (Laravel Collective o nativos), Archivos estáticos (CSS/JS via Vite).

### Capa 2: Servicios de Aplicación (Service Layer)
Esta es la capa más crítica en este diseño RUP. Aquí residen los **flujos de trabajo** y las **reglas de negocio**. Los controladores llaman a estos servicios.
*   **`WorkflowService`:** Orquesta el cambio de turno (Triple validación: Operador A -> Operador B -> Coord -> WFM). Maneja estados y transiciones.
*   **`ImporterService` (ETL):** Contiene la lógica para leer CSVs, limpiarlos usando PHP arrays/collections y prepararlos para la BD. Implementa la lógica de "Eliminar archivo solo si éxito".
*   **`AdherenceEngine`:** Algoritmo que compara la tabla `Schedule` vs. `AgentLog`. Calcula los tiempos y desviaciones.
*   **`NotificationService`:** Envío de correos o alertas en pantalla.

### Capa 3: Dominio y Datos (Domain & Persistence Layer)
Representa los conceptos del negocio y su mapeo a la base de datos relacional.
*   **Modelos Eloquent (ORM):**
    *   `User`, `Role` (Estructura Organizacional).
    *   `Schedule` (Horario planificado).
    *   `ShiftSwapRequest` (Solicitud de cambio).
    *   `CiscoLog` (Clase base para `AhtMetric`, `NotReadyLog`, `CallDetail`).
*   **Validaciones de Modelo:** Restricciones de integridad (ej: no solapar horarios).

### Capa 4: Infraestructura e Integración (Infrastructure Layer)
Componentes que tocan el sistema operativo o recursos externos.
*   **`FileSystemAdapter`:** Abstracción para leer/escribir/borrar archivos en los directorios `aht`, `schedule`, etc.
*   **`DBConnection`:** Configuración de Eloquent y Pool de conexiones.
*   **`Scheduler/Worker`:** Componente para ejecución de tareas en segundo plano (Jobs/Queues de Laravel).

---

## 2. Vista de Procesos (Concurrencia y Ejecución)

A pesar de ser un monolito, el sistema tiene dos modos de ejecución distintos que comparten la misma base de código y modelos:

### A. Proceso Web (Síncrono)
Maneja las peticiones de los usuarios en tiempo real.
*   **Ciclo:** Request -> Laravel Route -> Controller -> Service -> Model -> DB -> Response.
*   **Caso de uso típico:** Un coordinador aprueba un permiso.

### B. Proceso de Fondo / Worker (Asíncrono o Batch)
Maneja la carga pesada de datos CISCO y la generación de reportes nocturnos.
*   **Implementación:** Se usa **Laravel Jobs/Queues** (con Redis o Database driver) o comandos Artisan (`php artisan import:cisco`) ejecutados por el Cron del sistema operativo.
*   **Ciclo ETL:**
    1.  `FileSystemAdapter` detecta archivos.
    2.  `ImporterService` procesa el archivo con PHP (arrays/collections).
    3.  Eloquent realiza `insert` o `upsert` (optimizado para alto volumen).
    4.  `FileSystemAdapter` elimina el CSV.

---

## 3. Vista de Implementación (Estructura del Proyecto)

Para garantizar la mantenibilidad, propongo la siguiente estructura de directorios estándar para Laravel bajo principios RUP:

```text
/wfm-app
├── /app
│   ├── /Http
│   │   ├── /Controllers          # Capa de Presentación (Rutas/Vistas)
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ReportingController.php
│   │   └── /Middleware
│   ├── /Models                   # Capa de Dominio (Eloquent)
│   │   ├── User.php
│   │   ├── Schedule.php
│   │   └── CiscoData.php
│   ├── /Services                 # Capa de Servicios (Lógica de Negocio)
│   │   ├── ImporterService.php
│   │   ├── WorkflowService.php
│   │   └── AdherenceService.php
│   ├── /Jobs                     # Tareas Asíncronas
│   │   └── ImportCiscoData.php
│   └── /Providers
├── /database
│   ├── /migrations               # Scripts de Migraciones
│   └── /seeders
├── /resources
│   ├── /views                    # Blade Templates
│   └── /js                       # JS (Vite)
├── /routes                       # Definición de rutas
├── /storage                      # Archivos temporales/logs
├── /data_inbox                   # Directorio "Landing Zone" para archivos CISCO
│   ├── /aht
│   ├── /schedule
│   └── /not_ready
├── /tests                        # Pruebas unitarias e integración
├── config/                       # Variables de entorno
└── artisan                       # Punto de entrada CLI
```

---

## 4. Patrones de Diseño Aplicados

1.  **Repository Pattern (Simplificado):** Eloquent actúa como Unit of Work y Repository, pero encapsularemos consultas complejas (ej: "obtener adherencia del equipo X en la fecha Y") dentro de métodos de clase en los Modelos o en los Servicios, para no ensuciar los Controladores.
2.  **Service Provider Pattern:** Uso de `AppServiceProvider` en Laravel para configuración global y bindings.
3.  **Adapter Pattern:** El `ImporterService` actuará como un adaptador que transforma el formato CSV crudo de CISCO al modelo de objetos de nuestra base de datos. Si CISCO cambia el formato CSV, solo modificamos el adaptador.
4.  **State Pattern:** Para gestionar el flujo de cambio de turno (Solicitado -> AceptadoColega -> AprobadoCoord -> AprobadoWFM -> Finalizado).

---

## 5. Decisiones Técnicas Críticas (Justificación RUP)

*   **Uso de PHP para el ETL:**
    *   *Justificación:* Los archivos de CISCO (`AHT`, `Llamadas`) pueden ser voluminosos. Procesar con arrays/collections de PHP permite limpieza eficiente (manejo de fechas, nulos) antes de pasar a Eloquent. Para alto volumen, usar chunks o Jobs asíncronos.
*   **Eliminación de Archivos CSV:**
    *   *Mecanismo:* Se usará una transacción de base de datos. Solo si `DB::commit()` es exitoso, se ejecuta `Storage::delete(file_path)`. Si hay error, se hace `DB::rollBack()` y el archivo se preserva para reintento o auditoría manual.
*   **Base de Datos:**
    *   Se recomienda **PostgreSQL** por su robustez con tipos de datos de fecha/hora y capacidad de manejar JSON (útil si algún dato de CISCO viene desestructurado).
