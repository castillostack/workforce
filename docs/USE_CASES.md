# Modelo de Casos de Uso

## 1. Diagrama General de Actores

### Actores Primarios (Humanos)
*   **Operador:** Agente del centro de contacto. Su objetivo es cumplir su horario y gestionar su tiempo libre.
*   **Coordinador (Supervisor):** Responsable de un equipo. Necesita visibilidad operativa y autoridad para aprobar excepciones menores.
*   **Analista WFM (Workforce Management):** Actor experto. Responsable de la planificación macro, aprobación final de cambios y configuración de reglas.
*   **Gerencia (Jefes/Director):** Consumidores de información estratégica.

### Actores Secundarios (Sistemas/Tiempo)
*   **Cron (Temporizador del Sistema):** Actor temporal que dispara procesos automáticos (ETL diario, envío de correos).
*   **Sistema de Archivos (CISCO Source):** Fuente pasiva de datos (CSVs).

---

## 2. Paquetes de Casos de Uso
Para organizar el sistema, agrupamos los casos de uso en tres paquetes funcionales.

### Paquete A: Gestión de Horarios y Novedades
*Este paquete contiene la lógica de negocio humana y los flujos de aprobación.*

| Caso de Uso (CU) | Actores Principales | Descripción Breve |
| :--- | :--- | :--- |
| **CU-01: Consultar Horario Personal** | Operador | Visualizar turnos, descansos y almuerzos asignados. |
| **CU-02: Solicitar Intercambio de Turno** | Operador | Iniciar solicitud para cambiar turno con otro colega. Incluye flujo de aprobación. |
| **CU-03: Gestionar Permisos y Licencias** | Operador, Coord. | Solicitar/Aprobar permisos (Trimestrales, Compensatorios). |
| **CU-04: Justificar Tardanza/Ausencia** | Coord, Operador | Registrar motivo para una adherencia fallida detectada. |
| **CU-05: Cargar Planificación (Malla)** | WFM | Importar la planificación teórica de horarios futuros. |

### Paquete B: Motor de Datos e Integración (Backend)
*Este paquete contiene la lógica crítica de procesamiento masivo.*

| Caso de Uso (CU) | Actores Principales | Descripción Breve |
| :--- | :--- | :--- |
| **CU-06: Importar Datos Operativos (ETL)** | Cron | Detectar archivos CSV, validar formato, cargar a BD y eliminar archivo físico. |
| **CU-07: Calcular Adherencia Diaria** | Cron | Cruzar datos de `Schedule` vs `NotReady/Log` para detectar desviaciones. |
| **CU-08: Calcular Métricas AHT** | Cron | Procesar logs de llamadas para estadísticas de tiempos y colas. |

### Paquete C: Visualización y Reportería
*Este paquete se enfoca en la salida de información.*

| Caso de Uso (CU) | Actores Principales | Descripción Breve |
| :--- | :--- | :--- |
| **CU-09: Generar Informe de Adherencia** | WFM, Coord, Gerencia | Visualizar comparativo Horario vs Real, uso de breaks y almuerzos. |
| **CU-10: Generar Informe de Productividad** | WFM, Gerencia | Visualizar desglose de tiempos (Ready, Talking, NotReady) y motivos. |

---

## 3. Especificación Detallada de Casos de Uso Prioritarios

Para la **Fase de Elaboración**, debemos detallar los casos de uso que presentan mayor riesgo. He seleccionado dos:
1.  **CU-06 Importar Datos Operativos** (Riesgo Técnico: Integridad de datos y manipulación de archivos).
2.  **CU-02 Solicitar Intercambio de Turno** (Riesgo de Negocio: Complejidad del flujo de aprobación).

### Especificación: CU-06 Importar Datos Operativos

*   **Actores:** Cron (Temporizador).
*   **Precondiciones:** Existen archivos CSV en los directorios designados (`/aht`, `/chat`, etc.). Base de datos operativa.
*   **Flujo Principal (Escenario de Éxito):**
    1.  El Sistema se despierta a la hora configurada (o detecta evento de archivo nuevo).
    2.  El Sistema escanea los directorios definidos.
    3.  El Sistema identifica un archivo (ej: `aht_20251107.csv`).
    4.  El Sistema valida la estructura de cabeceras del CSV.
    5.  El Sistema inicia una transacción de base de datos.
    6.  El Sistema parsea e inserta registro por registro en la tabla correspondiente.
    7.  El Sistema hace `COMMIT` de la transacción.
    8.  **Punto Crítico:** El Sistema elimina físicamente el archivo CSV del directorio.
    9.  El Sistema registra el evento en el log de auditoría como "Éxito".
*   **Flujos Alternativos:**
    *   *4a. Error de Formato:* Si las columnas no coinciden, el sistema mueve el archivo a una carpeta `/error`, envía alerta a Soporte y NO procesa la carga.
    *   *7a. Fallo de BD:* Si la base de datos rechaza datos (ej: duplicados), se hace `ROLLBACK` y el archivo físico **se mantiene** intacto para reintento.

### Especificación: CU-02 Solicitar Intercambio de Turno

*   **Actores:** Solicitante (Operador A), Receptor (Operador B), Coordinador, WFM.
*   **Precondiciones:** Ambos operadores tienen turno asignado en la fecha deseada.
*   **Flujo Principal:**
    1.  **Solicitante** selecciona su turno y elige "Solicitar Intercambio".
    2.  El Sistema muestra lista de colegas disponibles o permite buscar por nombre.
    3.  **Solicitante** selecciona a **Receptor** y envía solicitud.
    4.  El Sistema notifica a **Receptor**.
    5.  **Receptor** ingresa al sistema y **Acepta** el cambio.
    6.  El Sistema cambia el estado de la solicitud a "Pendiente de Supervisión" y notifica al **Coordinador**.
    7.  **Coordinador** revisa impacto en el servicio y da "Visto Bueno".
    8.  El Sistema cambia estado a "Pendiente WFM" y notifica a **WFM**.
    9.  **WFM** valida cumplimiento normativo y **Aprueba Final**.
    10. El Sistema actualiza los horarios de A y B en la base de datos oficial.
    11. El Sistema notifica a todas las partes.
*   **Reglas de Negocio:**
    *   El cambio debe ser dentro de la misma semana fiscal (Regla opcional a definir).
    *   Si cualquiera de los actores (Receptor, Coord, WFM) rechaza, el flujo termina y el turno original se mantiene.

---

## 4. Diagrama de Relaciones (Texto UML)

Para visualizar la estructura, aquí describo las relaciones clave:

```uml
(Operador) -- (Solicitar Intercambio de Turno)
(Solicitar Intercambio de Turno) ..> (Validar Disponibilidad) : <<include>>
(Solicitar Intercambio de Turno) <.. (Aprobar Cambio WFM) : <<extend>> (Si requiere WFM)

(Cron) -- (Importar Datos Operativos)
(Importar Datos Operativos) ..> (Validar Formato CSV) : <<include>>

(Cron) -- (Calcular Adherencia Diaria)
(Generar Informe de Adherencia) ..> (Calcular Adherencia Diaria) : <<use>> (Consume datos de)
```
