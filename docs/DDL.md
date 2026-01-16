```sql
CREATE TABLE "users" (
  "id" integer PRIMARY KEY,
  "username" varchar UNIQUE NOT NULL,
  "email" varchar UNIQUE NOT NULL,
  "full_name" varchar,
  "avatar" varchar,
  "is_active" boolean DEFAULT true,
  "created_at" timestamp,
  "updated_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "roles" (
  "id" integer PRIMARY KEY,
  "name" varchar UNIQUE NOT NULL,
  "description" varchar,
  "created_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "permissions" (
  "id" integer PRIMARY KEY,
  "code" varchar UNIQUE NOT NULL,
  "name" varchar,
  "created_at" timestamp
);

CREATE TABLE "user_roles" (
  "user_id" integer,
  "role_id" integer
);

CREATE TABLE "role_permissions" (
  "role_id" integer,
  "permission_id" integer
);

CREATE TABLE "departments" (
  "id" integer PRIMARY KEY,
  "name" varchar NOT NULL,
  "created_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "teams" (
  "id" integer PRIMARY KEY,
  "name" varchar NOT NULL,
  "department_id" integer,
  "created_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "employees" (
  "id" integer PRIMARY KEY,
  "user_id" integer UNIQUE,
  "employee_code" varchar UNIQUE,
  "team_id" integer,
  "supervisor_id" integer,
  "position" varchar,
  "extension" varchar,
  "hire_date" date,
  "status" varchar,
  "created_at" timestamp,
  "updated_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "shift_templates" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "start_time" time,
  "end_time" time,
  "total_minutes" integer,
  "is_overnight" boolean DEFAULT false,
  "is_active" boolean DEFAULT true,
  "created_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "shift_breaks" (
  "id" integer PRIMARY KEY,
  "shift_template_id" integer,
  "break_type" varchar,
  "duration_minutes" integer,
  "offset_minutes" integer,
  "is_paid" boolean
);

CREATE TABLE "schedule_status" (
  "id" integer PRIMARY KEY,
  "code" varchar UNIQUE,
  "description" varchar
);

CREATE TABLE "schedules" (
  "id" integer PRIMARY KEY,
  "employee_id" integer,
  "schedule_date" date,
  "shift_template_id" integer,
  "status_id" integer,
  "notes" varchar,
  "created_at" timestamp,
  "updated_at" timestamp,
  "deleted_at" timestamp
);

CREATE TABLE "schedule_versions" (
  "id" integer PRIMARY KEY,
  "schedule_id" integer,
  "change_type" varchar,
  "changed_by" integer,
  "changed_at" timestamp
);

CREATE TABLE "request_types" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "requires_hr" boolean,
  "max_days" integer
);

CREATE TABLE "approval_workflows" (
  "id" integer PRIMARY KEY,
  "request_type_id" integer,
  "department_id" integer,
  "name" varchar,
  "is_active" boolean
);

CREATE TABLE "workflow_steps" (
  "id" integer PRIMARY KEY,
  "workflow_id" integer,
  "step_order" integer,
  "approver_role" varchar,
  "is_required" boolean
);

CREATE TABLE "requests" (
  "id" integer PRIMARY KEY,
  "employee_id" integer,
  "request_type_id" integer,
  "workflow_id" integer,
  "start_date" date,
  "end_date" date,
  "reason" varchar,
  "status" varchar,
  "created_at" timestamp,
  "submitted_at" timestamp,
  "completed_at" timestamp
);

CREATE TABLE "request_approvals" (
  "id" integer PRIMARY KEY,
  "request_id" integer,
  "workflow_step_id" integer,
  "approver_id" integer,
  "decision" varchar,
  "comment" varchar,
  "decided_at" timestamp
);

CREATE TABLE "metrics_daily" (
  "id" integer PRIMARY KEY,
  "employee_id" integer,
  "metric_date" date,
  "scheduled_minutes" integer,
  "worked_minutes" integer,
  "adherence_pct" numeric,
  "total_calls" integer,
  "calculated_at" timestamp
);

CREATE TABLE "reports" (
  "id" integer PRIMARY KEY,
  "type" varchar,
  "parameters" json,
  "file_path" varchar,
  "generated_by" integer,
  "generated_at" timestamp
);

CREATE TABLE "imports" (
  "id" integer PRIMARY KEY,
  "filename" varchar,
  "source_type" varchar,
  "status" varchar,
  "uploaded_by" integer,
  "processed_at" timestamp
);

CREATE TABLE "audit_logs" (
  "id" integer PRIMARY KEY,
  "user_id" integer,
  "action" varchar,
  "target_table" varchar,
  "target_id" integer,
  "details" json,
  "created_at" timestamp
);

CREATE TABLE "notifications" (
  "id" integer PRIMARY KEY,
  "recipient_id" integer,
  "message" varchar,
  "type" varchar,
  "status" varchar,
  "created_at" timestamp
);

CREATE TABLE aht_calls_raw (
    id BIGSERIAL PRIMARY KEY,

    nombre_del_agente                VARCHAR(150),
    id_de_conexion_del_agente        VARCHAR(50),
    agent_ext                        VARCHAR(20),

    hora_de_inicio_de_llamada        TIMESTAMP,
    hora_de_fin_de_llamada           TIMESTAMP,

    duracion_de_llamada              INTEGER,

    numero_llamado                   VARCHAR(50),
    ani_de_llamada                   VARCHAR(50),

    llamada_dirigida_por_csq         VARCHAR(100),
    other_csq                        VARCHAR(100),

    call_skill                       VARCHAR(100),

    tiempo_de_conversacion           INTEGER,
    tiempo_en_espera                 INTEGER,
    tiempo_de_cierre                 INTEGER,

    call_type                        VARCHAR(50),

    created_at                       TIMESTAMP DEFAULT NOW()
);


CREATE TABLE calls_raw (
    id BIGSERIAL PRIMARY KEY,

    id_de_sesion                VARCHAR(30),
    numero_de_secuencia         INTEGER,

    hora_de_inicio              TIMESTAMP,
    hora_de_fin                 TIMESTAMP,

    disposicion_de_contacto     INTEGER,

    csq_name                    VARCHAR(120),
    nombre_del_agente           VARCHAR(150),

    numero_del_autor            VARCHAR(50),
    numero_de_destino           VARCHAR(50),
    numero_llamado              VARCHAR(50),

    tiempo_de_conversacion      INTEGER,
    tiempo_de_timbre            INTEGER,
    tiempo_de_trabajo           INTEGER,
    tiempo_en_cola              INTEGER,

    created_at                  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE agent_state_notready_raw (
    id BIGSERIAL PRIMARY KEY,

    agent_login_id        VARCHAR(50),

    transition_time       TIMESTAMP,

    agent_state           VARCHAR(50),

    reason_code           VARCHAR(150),

    duration              INTEGER,

    created_at            TIMESTAMP DEFAULT NOW()
);


CREATE TABLE chat_conversations_raw (
    id BIGSERIAL PRIMARY KEY,

    agent_name                     VARCHAR(150),
    agent_id                       VARCHAR(50),

    conversation_start_time        TIMESTAMP,
    conversation_end_time          TIMESTAMP,

    conversation_duration          INTEGER,

    conversation_author_id         VARCHAR(100),
    conversation_destination       VARCHAR(100),

    chat_conversation              VARCHAR(100),

    talk_time                      INTEGER,

    acceptance_time                INTEGER,

    conversation_type              VARCHAR(50),

    chat_source                    VARCHAR(100),

    chat_rating                    VARCHAR(20),

    created_at                     TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_chat_agent
ON chat_conversations_raw (agent_id);

CREATE INDEX idx_chat_start_time
ON chat_conversations_raw (conversation_start_time);

CREATE INDEX idx_chat_type
ON chat_conversations_raw (conversation_type);

CREATE INDEX idx_chat_source
ON chat_conversations_raw (chat_source);


CREATE INDEX idx_notready_agent
ON agent_state_notready_raw (agent_login_id);

CREATE INDEX idx_notready_time
ON agent_state_notready_raw (transition_time);

CREATE INDEX idx_notready_state
ON agent_state_notready_raw (agent_state);



CREATE INDEX idx_calls_raw_session
ON calls_raw (id_de_sesion);

CREATE INDEX idx_calls_raw_start_time
ON calls_raw (hora_de_inicio);

CREATE INDEX idx_calls_raw_csq
ON calls_raw (csq_name);

CREATE INDEX idx_calls_raw_agent
ON calls_raw (nombre_del_agente);


CREATE INDEX idx_aht_agent_login 
ON aht_calls_raw (id_de_conexion_del_agente);

CREATE INDEX idx_aht_call_start 
ON aht_calls_raw (hora_de_inicio_de_llamada);

CREATE INDEX idx_aht_csq 
ON aht_calls_raw (llamada_dirigida_por_csq);


CREATE UNIQUE INDEX ON "user_roles" ("user_id", "role_id");

CREATE UNIQUE INDEX ON "role_permissions" ("role_id", "permission_id");

CREATE UNIQUE INDEX ON "schedules" ("employee_id", "schedule_date");

CREATE UNIQUE INDEX ON "metrics_daily" ("employee_id", "metric_date");

ALTER TABLE "user_roles" ADD CONSTRAINT "user_roles_user" FOREIGN KEY ("user_id") REFERENCES "users" ("id");

ALTER TABLE "user_roles" ADD CONSTRAINT "user_roles_role" FOREIGN KEY ("role_id") REFERENCES "roles" ("id");

ALTER TABLE "role_permissions" ADD CONSTRAINT "role_permissions_role" FOREIGN KEY ("role_id") REFERENCES "roles" ("id");

ALTER TABLE "role_permissions" ADD CONSTRAINT "role_permissions_permission" FOREIGN KEY ("permission_id") REFERENCES "permissions" ("id");

ALTER TABLE "teams" ADD CONSTRAINT "teams_department" FOREIGN KEY ("department_id") REFERENCES "departments" ("id");

ALTER TABLE "employees" ADD CONSTRAINT "employee_user" FOREIGN KEY ("user_id") REFERENCES "users" ("id");

ALTER TABLE "employees" ADD CONSTRAINT "employee_team" FOREIGN KEY ("team_id") REFERENCES "teams" ("id");

ALTER TABLE "employees" ADD CONSTRAINT "employee_supervisor" FOREIGN KEY ("supervisor_id") REFERENCES "employees" ("id");

ALTER TABLE "shift_breaks" ADD CONSTRAINT "shift_breaks_template" FOREIGN KEY ("shift_template_id") REFERENCES "shift_templates" ("id");

ALTER TABLE "schedules" ADD CONSTRAINT "schedules_employee" FOREIGN KEY ("employee_id") REFERENCES "employees" ("id");

ALTER TABLE "schedules" ADD CONSTRAINT "schedules_shift" FOREIGN KEY ("shift_template_id") REFERENCES "shift_templates" ("id");

ALTER TABLE "schedules" ADD CONSTRAINT "schedules_status" FOREIGN KEY ("status_id") REFERENCES "schedule_status" ("id");

ALTER TABLE "schedule_versions" ADD CONSTRAINT "schedule_versions_schedule" FOREIGN KEY ("schedule_id") REFERENCES "schedules" ("id");

ALTER TABLE "schedule_versions" ADD CONSTRAINT "schedule_versions_user" FOREIGN KEY ("changed_by") REFERENCES "users" ("id");

ALTER TABLE "approval_workflows" ADD CONSTRAINT "workflow_request_type" FOREIGN KEY ("request_type_id") REFERENCES "request_types" ("id");

ALTER TABLE "approval_workflows" ADD CONSTRAINT "workflow_department" FOREIGN KEY ("department_id") REFERENCES "departments" ("id");

ALTER TABLE "workflow_steps" ADD CONSTRAINT "workflow_steps_workflow" FOREIGN KEY ("workflow_id") REFERENCES "approval_workflows" ("id");

ALTER TABLE "requests" ADD CONSTRAINT "requests_employee" FOREIGN KEY ("employee_id") REFERENCES "employees" ("id");

ALTER TABLE "requests" ADD CONSTRAINT "requests_type" FOREIGN KEY ("request_type_id") REFERENCES "request_types" ("id");

ALTER TABLE "requests" ADD CONSTRAINT "requests_workflow" FOREIGN KEY ("workflow_id") REFERENCES "approval_workflows" ("id");

ALTER TABLE "request_approvals" ADD CONSTRAINT "approvals_request" FOREIGN KEY ("request_id") REFERENCES "requests" ("id");

ALTER TABLE "request_approvals" ADD CONSTRAINT "approvals_step" FOREIGN KEY ("workflow_step_id") REFERENCES "workflow_steps" ("id");

ALTER TABLE "request_approvals" ADD CONSTRAINT "approvals_user" FOREIGN KEY ("approver_id") REFERENCES "users" ("id");

ALTER TABLE "metrics_daily" ADD CONSTRAINT "metrics_employee" FOREIGN KEY ("employee_id") REFERENCES "employees" ("id");

ALTER TABLE "reports" ADD CONSTRAINT "reports_user" FOREIGN KEY ("generated_by") REFERENCES "users" ("id");

ALTER TABLE "imports" ADD CONSTRAINT "imports_user" FOREIGN KEY ("uploaded_by") REFERENCES "users" ("id");

ALTER TABLE "audit_logs" ADD CONSTRAINT "audit_user" FOREIGN KEY ("user_id") REFERENCES "users" ("id");

ALTER TABLE "notifications" ADD CONSTRAINT "notifications_user" FOREIGN KEY ("recipient_id") REFERENCES "users" ("id");

```