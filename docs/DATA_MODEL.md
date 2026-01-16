# Diagrama de Paquetes de Datos
1.  **Core & Auth:** Usuarios, Jerarquía, Roles.
2.  **WFM Planning:** Horarios, Actividades, Permisos, Intercambios.
3.  **CISCO Telemetry:** Datos crudos importados (AHT, Chat, Llamadas, Logs).
4.  **Analytics:** Tablas de hechos agregados para reportes rápidos.

---

```python
# app/models.py
from datetime import datetime
from app import db
from sqlalchemy.orm import backref

# ==========================================
# 1. CORE & AUTH (Estructura Organizacional)
# ==========================================

class OrganizationRole(db.Model):
    """
    Catálogo de roles para manejar permisos (Director, Jefe, Coord, WFM, QA, Operador).
    """
    __tablename__ = 'roles'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(50), unique=True, nullable=False)
    description = db.Column(db.String(255))
    
    # Relaciones
    users = db.relationship('User', backref='role_obj', lazy='dynamic')

class User(db.Model):
    """
    El núcleo del sistema. Representa a cualquier empleado.
    Maneja la recursividad para la jerarquía (Jefe -> Coord -> Operador).
    """
    __tablename__ = 'users'

    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(64), unique=True, index=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(128)) # Almacenado de forma segura
    
    full_name = db.Column(db.String(120), nullable=False)
    
    # Identificadores para cruce con CISCO
    # Se usa para vincular los CSVs (campos: 'agent_login_id', 'ID_de_conexión', 'ID de agente')
    cisco_id = db.Column(db.String(50), unique=True, index=True) 
    cisco_extension = db.Column(db.String(20)) # Para mapeo de extensiones telefónicas
    
    # Claves Foráneas
    role_id = db.Column(db.Integer, db.ForeignKey('roles.id'))
    supervisor_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True)
    
    # Relaciones de Jerarquía
    subordinates = db.relationship('User', 
                                   backref=db.backref('supervisor', remote_side=[id]),
                                   lazy='dynamic')

    # Auditoría
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    is_active = db.Column(db.Boolean, default=True)

# ==========================================
# 2. WFM PLANNING (Lo Planificado)
# ==========================================

class Schedule(db.Model):
    """
    La cabecera del turno diario de un agente.
    """
    __tablename__ = 'schedules'

    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    date = db.Column(db.Date, nullable=False, index=True)
    
    # Horario Global del Turno (ej: 08:00 a 17:00)
    scheduled_start = db.Column(db.DateTime, nullable=False)
    scheduled_end = db.Column(db.DateTime, nullable=False)
    
    is_day_off = db.Column(db.Boolean, default=False) # Vacaciones, Libres
    
    # Relaciones
    activities = db.relationship('ScheduleActivity', backref='schedule', cascade="all, delete-orphan")
    
    __table_args__ = (db.UniqueConstraint('user_id', 'date', name='_user_date_uc'),)

class ScheduleActivity(db.Model):
    """
    Detalle intra-turno: Almuerzos, Breaks, Capacitaciones, QA.
    Esencial para calcular Adherencia.
    """
    __tablename__ = 'schedule_activities'

    id = db.Column(db.Integer, primary_key=True)
    schedule_id = db.Column(db.Integer, db.ForeignKey('schedules.id'), nullable=False)
    
    # Tipos: 'LUNCH', 'BREAK_1', 'BREAK_2', 'TRAINING', 'QA_FEEDBACK'
    activity_type = db.Column(db.String(50), nullable=False) 
    
    start_time = db.Column(db.DateTime, nullable=False)
    end_time = db.Column(db.DateTime, nullable=False)
    duration_minutes = db.Column(db.Integer)

class ShiftSwapRequest(db.Model):
    """
    Gestión de Intercambio de Turnos.
    Implementa el flujo de aprobación de 3 niveles.
    """
    __tablename__ = 'shift_swap_requests'

    id = db.Column(db.Integer, primary_key=True)
    
    # Actores
    requester_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    target_user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    
    # Fechas involucradas
    original_date = db.Column(db.Date, nullable=False) # Fecha que A no puede trabajar
    swap_date = db.Column(db.Date, nullable=True)      # Fecha que A tomará de B (si aplica)
    
    # Estado del Flujo
    # Estados: 'PENDING_PEER', 'PENDING_COORD', 'PENDING_WFM', 'APPROVED', 'REJECTED'
    status = db.Column(db.String(20), default='PENDING_PEER')
    
    # Trazabilidad de Aprobaciones
    peer_accepted_at = db.Column(db.DateTime)
    coord_approved_at = db.Column(db.DateTime)
    coord_user_id = db.Column(db.Integer, db.ForeignKey('users.id')) # Qué coord aprobó
    wfm_approved_at = db.Column(db.DateTime)
    wfm_user_id = db.Column(db.Integer, db.ForeignKey('users.id')) # Qué analista WFM aprobó
    
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class LeaveRequest(db.Model):
    """
    Gestión de Permisos (Citas médicas, Trimestrales, Compensatorios).
    """
    __tablename__ = 'leave_requests'

    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    
    # Tipos: 'MEDICAL', 'TRIMESTRAL', 'COMPENSATORY', 'ACCOMPANIMENT'
    leave_type = db.Column(db.String(50), nullable=False)
    
    start_time = db.Column(db.DateTime, nullable=False)
    end_time = db.Column(db.DateTime, nullable=False)
    total_hours = db.Column(db.Float)
    
    justification = db.Column(db.Text)
    evidence_file_path = db.Column(db.String(255)) # Ruta al archivo PDF/Img
    
    status = db.Column(db.String(20), default='PENDING_SUPERVISOR')
    approved_by = db.Column(db.Integer, db.ForeignKey('users.id'))

class LeaveBalance(db.Model):
    """
    Control de saldos (ej: 8 horas trimestrales).
    """
    __tablename__ = 'leave_balances'
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    
    balance_type = db.Column(db.String(50)) # 'TRIMESTRAL', 'COMPENSATORY'
    period_start = db.Column(db.Date) # Inicio del trimestre
    period_end = db.Column(db.Date)   # Fin del trimestre
    
    hours_allocated = db.Column(db.Float, default=8.0)
    hours_used = db.Column(db.Float, default=0.0)

# ==========================================
# 3. CISCO TELEMETRY (Datos Importados - Raw)
# ==========================================
# Estas tablas reflejan la estructura de los CSV para facilitar el volcado.

class CiscoNotReadyLog(db.Model):
    """ Origen: NotReady.csv """
    __tablename__ = 'cisco_not_ready_logs'
    
    id = db.Column(db.Integer, primary_key=True)
    agent_login_id = db.Column(db.String(50), index=True) # Enlace con User.cisco_id
    
    transition_time = db.Column(db.DateTime, nullable=False, index=True)
    agent_state = db.Column(db.String(50)) # Login, Logout, Not Ready, Ready, Work
    reason_code = db.Column(db.String(100)) # Almuerzo, Baño, Capacitación
    duration = db.Column(db.Integer) # Segundos
    
    import_batch_id = db.Column(db.String(50)) # Para rastrear qué archivo trajo este dato

class CiscoCallRecord(db.Model):
    """ Origen: Llamadas.csv """
    __tablename__ = 'cisco_call_records'

    # Se asume session_id + sequence_no es único
    session_id = db.Column(db.String(50), primary_key=True) 
    sequence_no = db.Column(db.Integer, primary_key=True) 
    
    agent_name = db.Column(db.String(120)) # Dato redundante pero viene en CSV
    # Nota: El CSV Llamadas NO trae el ID de agente (solo nombre), 
    # tendremos que inferir o cruzar por nombre, o usar el CSV AHT.
    
    start_time = db.Column(db.DateTime, index=True)
    end_time = db.Column(db.DateTime)
    
    csq_name = db.Column(db.String(100)) # Cola
    contact_disposition = db.Column(db.Integer) # 1=Handled, 2=Abandoned?
    
    talk_time = db.Column(db.Integer)
    queue_time = db.Column(db.Integer)
    ring_time = db.Column(db.Integer)
    work_time = db.Column(db.Integer)
    
    calling_number = db.Column(db.String(50)) # ANI
    called_number = db.Column(db.String(50))  # DNIS

class CiscoAhtRecord(db.Model):
    """ Origen: AHT.csv """
    __tablename__ = 'cisco_aht_records'
    
    id = db.Column(db.Integer, primary_key=True)
    agent_connection_id = db.Column(db.String(50), index=True) # ID crucial
    agent_ext = db.Column(db.String(20))
    
    call_start = db.Column(db.DateTime)
    call_end = db.Column(db.DateTime)
    
    csq_name = db.Column(db.String(100))
    call_skill = db.Column(db.String(100))
    call_type = db.Column(db.String(50)) # 'Inbound ACD'
    
    talk_time = db.Column(db.Integer)
    hold_time = db.Column(db.Integer)
    work_time = db.Column(db.Integer) # tiempo_de_cierre

class CiscoChatRecord(db.Model):
    """ Origen: Chat.csv """
    __tablename__ = 'cisco_chat_records'
    
    conversation_id = db.Column(db.String(100), primary_key=True)
    agent_id = db.Column(db.String(50), index=True)
    
    start_time = db.Column(db.DateTime)
    end_time = db.Column(db.DateTime)
    duration = db.Column(db.Integer)
    
    chat_source = db.Column(db.String(50)) # 'Bubble Chat' / WhatsApp
    rating = db.Column(db.String(10))

# ==========================================
# 4. ANALYTICS (Reportes Procesados)
# ==========================================

class DailyAdherenceMetric(db.Model):
    """
    Tabla de hechos (Fact Table) generada por el proceso nocturno.
    Cruza Schedule vs CiscoNotReadyLog.
    """
    __tablename__ = 'daily_adherence_metrics'
    
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    date = db.Column(db.Date, index=True)
    
    # 1. Adherencia de Entrada
    scheduled_entry = db.Column(db.DateTime)
    actual_entry = db.Column(db.DateTime)
    lateness_minutes = db.Column(db.Integer) # >0 es tardanza
    
    # 2. Adherencia de Almuerzo (Max 45m)
    scheduled_lunch_duration = db.Column(db.Integer) # usualmente 45
    actual_lunch_duration = db.Column(db.Integer)
    lunch_deviation = db.Column(db.Integer) # Exceso en minutos
    
    # 3. Adherencia de Descansos (Max 15m)
    actual_break_duration = db.Column(db.Integer)
    break_deviation = db.Column(db.Integer)
    
    # 4. Productividad General
    total_logged_seconds = db.Column(db.Integer)
    total_not_ready_seconds = db.Column(db.Integer)
    total_ready_seconds = db.Column(db.Integer)
    occupancy_rate = db.Column(db.Float) # (Talk + Work) / Logged

    __table_args__ = (db.UniqueConstraint('user_id', 'date', name='_daily_adh_uc'),)

class DailyProductivityMetric(db.Model):
    """
    Métricas de AHT y eficiencia por canal (Voz/Chat).
    """
    __tablename__ = 'daily_productivity_metrics'
    
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    date = db.Column(db.Date, index=True)
    
    # Métricas de Voz
    calls_handled = db.Column(db.Integer, default=0)
    avg_handle_time_voice = db.Column(db.Float, default=0.0) # AHT
    avg_talk_time = db.Column(db.Float)
    avg_hold_time = db.Column(db.Float)
    
    # Métricas de Chat
    chats_handled = db.Column(db.Integer, default=0)
    avg_handle_time_chat = db.Column(db.Float, default=0.0)
    
    # Calidad (si hubiera datos en el futuro)
    avg_rating = db.Column(db.Float)
```

### Notas de Arquitectura RUP sobre el Modelo

1.  **Desacoplamiento Datos Externos (Landing Zone):** Las tablas bajo el módulo *CISCO TELEMETRY* (`CiscoNotReadyLog`, etc.) no tienen Foreign Keys estrictas hacia `User`.
    *   *Razón:* Un CSV puede contener datos de un agente nuevo que RRHH aún no ha creado en la tabla `User`. Si pusiéramos `ForeignKey` estricta, la importación del CSV fallaría para *todos* los registros.
    *   *Solución:* La vinculación se hace a nivel lógico durante la generación del reporte (`DailyAdherenceMetric`), donde buscamos al usuario.

2.  **Manejo de Tiempos:** Todos los campos `start_time`, `end_time` son `DateTime`. Es responsabilidad del **Servicio de Importación** convertir el string `"Fri Nov 07 15:07:08 EST 2025"` a un objeto Python `datetime` compatible con la base de datos (idealmente UTC o Local Timezone consistente).

3.  **Indices de Rendimiento:** Se han añadido índices (`index=True`) en `cisco_id`, `date` y `start_time`. Estos son los campos que usaremos en las cláusulas `WHERE` de los procesos batch y reportes visuales.

4.  **Auditoría de Cambios de Turno:** La tabla `ShiftSwapRequest` almacena *quién* (ID de usuario) y *cuándo* se aprobó en cada nivel. Esto es crucial para la trazabilidad requerida en el Documento de Visión.