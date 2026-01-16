# Documento de Visión
**Proyecto:** Sistema de Gestión de Fuerza Laboral y Analítica de Adherencia (WFM-Analytics)  
**Versión:** 1.0  
**Fase RUP:** Inicio  
**Fecha:** 24 de Mayo, 2024

---

## 1. Introducción
El propósito de este documento es recopilar, analizar y definir las necesidades de alto nivel y las características del **Sistema de Gestión de Fuerza Laboral (WFM)**. Este sistema centralizará la gestión de horarios, automatizará flujos de aprobación de novedades y procesará datos de telemetría (CISCO) para generar reportes de adherencia y productividad.

## 2. Posicionamiento

### 2.1 Declaración del Problema
| Elemento | Descripción |
| :--- | :--- |
| **El problema de** | La desconexión entre la planificación de horarios (WFM) y la ejecución real operativa (Telemetría CISCO), sumado a la gestión manual de cambios de turno. |
| **Afecta a** | El equipo de WFM, Supervisores, Jefes de Servicio y la Dirección. |
| **El impacto es** | Falta de visibilidad sobre la adherencia real, tiempos improductivos no detectados, burocracia en la gestión de permisos y dificultad para la toma de decisiones basada en datos. |
| **Una solución exitosa sería** | Un sistema integrado que importe automáticamente la data operativa, cruce la información contra lo planificado para calcular adherencia y digitalice el flujo de aprobaciones de novedades. |

### 2.2 Declaración de Posicionamiento del Producto
Para la organización que requiere optimizar su Centro de Contacto, el **WFM-Analytics** es un sistema de información que automatiza la inteligencia de negocios y la gestión de personal. A diferencia de la gestión actual basada en hojas de cálculo y correos aislados, nuestro producto integra el ciclo completo: Planificación -> Ejecución (Data Importada) -> Análisis (Reportes).

## 3. Descripciones de los Interesados (Stakeholders) y Usuarios

### 3.1 Resumen de Interesados
| Nombre | Descripción | Responsabilidades |
| :--- | :--- | :--- |
| **Dirección / Gerencia** | Ejecutivos de alto nivel. | Consumidores de reportes consolidados (KPIs, rentabilidad operativa). |
| **Control y Monitoreo (WFM)** | Equipo encargado de la planificación. | Generar horarios, validación final de cambios de turno, monitoreo de adherencia. |
| **Recursos Humanos** | Gestión de personal. | Proveer inputs de vacaciones, licencias y validación normativa. |
| **Ingeniería (QA)** | Aseguramiento de calidad. | Programar retroalimentaciones que afectan la disponibilidad de los operadores. |

### 3.2 Resumen de Usuarios (Actores del Sistema)
| Actor | Rol en el Sistema |
| :--- | :--- |
| **Operador** | Usuario final. Visualiza su horario, solicita cambios de turno (swaps), reporta citas médicas. |
| **Coordinador (Supervisor)** | Aprueba permisos de primer nivel, visualiza horarios de su equipo, gestiona justificaciones de tardanzas. |
| **Analista WFM** | Administrador funcional. Carga mallas horarias, aprueba cambios complejos, configura parámetros de reportes. |
| **Sistema CISCO (Actor Externo)** | Fuente de datos pasiva. Provee archivos CSV (`aht`, `chat`, `not_ready`, etc.). |

## 4. Resumen del Producto

### 4.1 Perspectiva del Producto
El sistema operará como una aplicación web intranet con un motor de procesamiento en segundo plano (Backend). Interactuará directamente con el sistema de archivos del servidor para la ingesta de datos provenientes de la central telefónica CISCO.

### 4.2 Resumen de Capacidades (Features)

#### 4.2.1 Gestión de Horarios y Novedades
*   **Visualización de Horarios:** Vista de calendario con turnos, breaks y almuerzos.
*   **Flujo de Cambio de Turno (Triple Validación):** Mecanismo que requiere: (1) Solicitud del operador A, (2) Aceptación del operador B, (3) VoBo del Coordinador, (4) Aprobación final WFM.
*   **Gestión de Permisos:** Control de bolsas de horas (ej: trimestrales renovables, tiempo compensatorio).

#### 4.2.2 Motor de Integración de Datos (ETL)
*   **Monitor de Directorios:** Detección automática de nuevos archivos CSV en rutas específicas (`/aht`, `/schedule`, etc.).
*   **Importación y Limpieza:** Parsing de CSVs a Base de Datos y eliminación segura del archivo origen tras éxito ("Delete on success").

#### 4.2.3 Analítica y Adherencia (Core Business)
*   **Cálculo de Adherencia:** Algoritmo que compara `Hora Programada` vs `Hora Real (Log)` para detectar desviaciones en entradas, almuerzos (>45min) y descansos (>15min).
*   **Análisis de Productividad:** Clasificación automática de estados (`Ready`, `Talking`, `Work` vs `NotReady`) y cálculo de porcentajes de eficiencia.
*   **Métricas AHT:** Análisis estadístico de duración de llamadas, tiempos de espera y volumen por cola.

#### 4.2.4 Reportería Automatizada
*   Generación batch (diaria) de informes.
*   Distribución jerárquica: Detallado (Operador/Coord) vs Consolidado (Jefatura/Dirección).

## 5. Restricciones y Requisitos No Funcionales

### 5.1 Restricciones de Diseño
*   **Formato de Entrada:** El sistema debe adherirse estrictamente a la estructura de columnas de los CSVs de CISCO provistos. Cualquier cambio en el `csv header` requerirá mantenimiento.
*   **Integridad de Datos:** No se debe eliminar un archivo CSV si la transacción de base de datos falla.

### 5.2 Rendimiento
*   El proceso de generación de reportes diarios debe ejecutarse en ventanas de tiempo nocturnas o de baja carga para no afectar la operatividad de la consulta en tiempo real.

### 5.3 Seguridad
*   Seguridad basada en roles (RBAC) estricta para respetar la jerarquía (un Coordinador no debe ver datos de otro equipo a menos que se le delegue).

## 6. Precedencia y Prioridad
Para la **Fase de Elaboración**, se priorizarán los siguientes casos de uso arquitectónicamente significativos:
1.  **Importar Datos CISCO:** Riesgo técnico alto por dependencia de formato de archivos.
2.  **Calcular Adherencia:** Riesgo de complejidad de negocio alto (algoritmo de comparación).
3.  **Solicitar Cambio de Turno:** Riesgo de lógica de flujo de trabajo complejo.

---
**Aprobado por:**  
_________________________  
Director del Proyecto (Cliente)

_________________________  
Arquitecto de Software (Equipo RUP)