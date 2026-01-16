## Estructura Organizacional
- 1 Director
- 2 Jefes de Servicios (Gerentes de Área)
	- cada jefe tiene a su cargo `n` cantidad de coordinadores (jefes de equipos)
		- cada coordinador tiene a su cargo `n` cantidad de operadores
- 1 jefe de control y monitoreo (Workforce managment)
	- analistas de control y monitoreo (WFA)
	- ingeniería (QA)
- Recursos humanos

## Horarios
### Control y monitoreo: 
- Genera los Horarios de entrada, almuerzo y descanso
- Se programan capacitaciones, docencias, retroalimentaciones de QA.
- Se excluyen vacaciones, licencias, Permisos, 

### Operadores
- Reciben notificaciones del horario establecido
- solicitan cambio de turno por uno o mas días, el cambio de turno debe ser :
	- aprobado por los operadores involucrados
	- notificado a sus coordinadores
	- aprobado por control y monitoreo
- Solicitar permisos aprobados por supervisor:
	- Trimestrales: hasta 8 horas disponibles renovables cada tres meses calendario
	- Tiempo Compensatorio: tiempo acumulado por horas extras
- Informar de:
	- Citas medicas
	- acompañamiento.

### Supervisores
- Recibe horario de su equipo
- recibe notificacion de cambios de turno
- aprueba permisos de su equipo
- Gestion de ausencias y tardanzas
- Registro de justificantes

### Jefe 
- Recibe horarios de sus equipos

## Reportería

**CISCO** exporta archivos de data en formato *CSV* y los ubica en un directorio con esta estructura:

```
.
├── aht
├── chat
├── inbound
├── not_ready
└── schedule
```

El sistema debe ser capaz de:
- Importar los archivos CSV que se encuentren en estos directorios
- si existe un nuevo archivo, se importa a nuestra base de datos.
- una vez importado, se elimina el archivo csv, **SOLO EL ARCHIVO QUE HAYA SIDO IMPORTADO**
- genere una vez al dia (hora a definir en configuracion o variables de entorno) un informe por operador o agente o usuario que contenga:
	- **Adherencia - Horarios vs NotReady**
		- Hora de entrada programada vs Hora de entrada real
		- Hora de almuerzo programado vs hora de almuerzo total
		- total de tiempo de almuerzo utilizado (max 45 minutos)
		- Hora de descanso programado vs hora de descanso total
		- total de tiempo de descanso utilizado (max 15 minutos)
	- **Actividades - NotReady**
		- Obtiene informacion  estadistica de los tiempos productivos y no productivos segun `agent_state` (Logged-In, Not Ready, Logout, Ready, Reserved, Talking,Work)
		- Obtiene Informacion estadistica de los motivos de estado.
		- calcula estadisticas sobre:
			- Tiempo total conectado (suma de todos los tiempos)
			- Timepo productivo (Ready, Reserved, Talking, Work)
			- Tiempos no productivos (Not Ready, Logged-In), el not Ready debe coincidir con la suma de los motivos de notready.
			- Productividad sobre el tiempoConectado.
			- Productividad sobre el tiempo programado.
			- Identifica periodos con tiempos extendidos (no programados) 
	- **AHT + Llamadas**
		- Obtiene Estadisticas de llamdas y tiempo hablado por cada cola de atencion
		- define los intervalos con la mayor cantidad de llamadas
		- define los intervalos con menor cantidad de llamadas
		- Identifica llamadas cortas o llamadas largas
		- compara estadisticas de tiempo en llamadas con tiempos meta por cola de atencion
Este analisis se debe hacer :
- individual por operador (operativo)
- por coordinacion (operativo)
- por jefatura (ejecutivo)
- General (ejecutivo)
cada tipo de informe debe ser enviado a sus respectivos destinatarios con frecuencias
	- Diario
	- semanal
	- mensual
Debe incluir comparativos coherentes

## Estructura de datos importados
### **AHT**
```csv
nombre_del_agente,ID_de_conexión_del_agente,agent_ext,hora_de_inicio_de_llamada,hora_de_fin_de_llamada,duración_de_llamada,número_llamado,ani_de_llamada,llamada_dirigida_por_csq,other_csq,call_skill,tiempo_de_conversación,tiempo_en_espera,tiempo_de_cierre,call_type
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:07:08 EST 2025,Fri Nov 07 15:08:01 EST 2025,53,378901,65424826,CSQ_FARMACIA,,Farmacia,51,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:08:07 EST 2025,Fri Nov 07 15:11:34 EST 2025,207,378901,2690612,CSQ_CENTRO_CONTACTO,,Centro de Contacto,205,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:11:39 EST 2025,Fri Nov 07 15:13:30 EST 2025,111,378901,62197532,CSQ_INFORMACION_GENERAL,,Informacion General,108,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:13:36 EST 2025,Fri Nov 07 15:15:14 EST 2025,98,378901,65555239,CSQ_INFORMACION_GENERAL,,Informacion General,97,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:15:20 EST 2025,Fri Nov 07 15:16:19 EST 2025,59,378901,65026350,CSQ_FARMACIA,,Farmacia,57,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:16:25 EST 2025,Fri Nov 07 15:17:06 EST 2025,41,378901,2250533,CSQ_CENTRO_CONTACTO,,Centro de Contacto,40,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:17:11 EST 2025,Fri Nov 07 15:20:09 EST 2025,178,378901,69296889,CSQ_CENTRO_CONTACTO,,Centro de Contacto,175,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:20:15 EST 2025,Fri Nov 07 15:21:04 EST 2025,49,378901,5382447,CSQ_CANCELACION_CITAS,,Cancelacion Citas,47,0,5,Inbound ACD
Aidil Yimara Graell,aigraell,37567,Fri Nov 07 15:21:09 EST 2025,Fri Nov 07 15:23:33 EST 2025,144,378901,65552071,CSQ_CENTRO_CONTACTO,,Centro de Contacto,142,0,5,Inbound ACD
```

### **Llamadas**
```csv
id_de_sesión,n.º_de_secuencia,hora_de_inicio,hora_de_fin,disposición_de_contacto,csq_name,nombre_del_agente,n.º_del_autor,n.º_de_destino,número_llamado,tiempo_de_conversación,tiempo_de_timbre,tiempo_de_trabajo,tiempo_en_cola
2.9000365534E10,0,Fri Oct 31 16:00:03 EST 2025,Fri Oct 31 16:01:40 EST 2025,1,,,63225013,378112,378901,0,,,
2.9000365536E10,0,Fri Oct 31 16:00:03 EST 2025,Fri Oct 31 16:02:37 EST 2025,1,,,69178712,378233,378901,0,,,
2.9000365538E10,0,Fri Oct 31 16:00:04 EST 2025,Fri Oct 31 16:01:02 EST 2025,1,,,65663067,378113,378901,0,,,
2.9000365542E10,0,Fri Oct 31 16:00:07 EST 2025,Fri Oct 31 16:01:42 EST 2025,2,CSQ_INFORMACION_GENERAL*,Luis Alberto Martinez Lagos,63927173,378234,378901,50,1,3,11
2.9000365547E10,0,Fri Oct 31 16:00:11 EST 2025,Fri Oct 31 16:02:40 EST 2025,1,,,2533621,378213,378901,0,,,
2.900036555E10,0,Fri Oct 31 16:00:16 EST 2025,Fri Oct 31 16:01:24 EST 2025,1,,,67376350,378214,378901,0,,,
2.9000365551E10,0,Fri Oct 31 16:00:17 EST 2025,Fri Oct 31 16:02:33 EST 2025,1,,,2663472,378215,378901,0,,,
2.9000365552E10,0,Fri Oct 31 16:00:19 EST 2025,Fri Oct 31 16:02:22 EST 2025,1,,,2620834,378216,378901,0,,,
2.9000365556E10,0,Fri Oct 31 16:00:20 EST 2025,Fri Oct 31 16:02:39 EST 2025,1,,,3874058,378217,378901,0,,,
```

### **NotReady**
```csv
agent_login_id,transition_time,agent_state,reason_code,duration
vtejada,Thu Nov 06 17:03:30 EST 2025,Logout ,Connection Failure,0
vtejada,Thu Nov 06 17:02:28 EST 2025,Not Ready,Llamada Saliente,62
glchanis,Thu Nov 06 17:02:26 EST 2025,Logout ,Agent Initiated,0
genemoreno,Thu Nov 06 17:02:25 EST 2025,Logout ,Agent Initiated,0
glchanis,Thu Nov 06 17:02:21 EST 2025,Not Ready,Capacitación,5
glchanis,Thu Nov 06 17:02:18 EST 2025,Ready ,,3
glchanis,Thu Nov 06 17:02:13 EST 2025,Work ,,5
vtejada,Thu Nov 06 17:01:56 EST 2025,Ready ,,32
vtejada,Thu Nov 06 17:01:51 EST 2025,Work ,,5
```

### **Chat**
```csv
Nombre del agente,ID de agente,hora_de_inicio_de_conversación,hora_de fin_de_conversación,duración_de_conversación,autor_de_conversación,destino_de_conversación,chat_conversation,tiempo_de_conversación,hora_de_aceptación,tipo_de_conversación,chat_source,chat_rating
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:16:55 EST 2025,Fri Nov 07 14:18:16 EST 2025,81,5FC013BD1000019A0001485B0A0B1857,,Chat WhatsApp,78,2,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:15:47 EST 2025,Fri Nov 07 14:16:31 EST 2025,44,5FBF0AD91000019A000148540A0B1857,,Chat WhatsApp,27,16,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:15:01 EST 2025,Fri Nov 07 14:15:41 EST 2025,40,5FBE57911000019A0001484D0A0B1857,,Chat WhatsApp,35,4,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:14:11 EST 2025,Fri Nov 07 14:14:51 EST 2025,40,5FBD93B71000019A000148460A0B1857,,Chat WhatsApp,38,1,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:12:42 EST 2025,Fri Nov 07 14:16:36 EST 2025,234,5FBC39EB1000019A0001483F0A0B1857,,Chat WhatsApp,230,2,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:11:01 EST 2025,Fri Nov 07 14:18:26 EST 2025,445,5FBAB00F1000019A000148380A0B1857,,Chat WhatsApp,441,2,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:08:48 EST 2025,Fri Nov 07 14:12:46 EST 2025,238,5FB8A7E91000019A000148310A0B1857,,Chat WhatsApp,235,1,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:08:37 EST 2025,Fri Nov 07 14:09:25 EST 2025,48,5FB87CB31000019A0001482A0A0B1857,,Chat WhatsApp,42,5,One-to-One,Bubble Chat,--
Ariadna Sanchez,ariasanchez,Fri Nov 07 14:03:12 EST 2025,Fri Nov 07 14:06:45 EST 2025,213,5FB388831000019A000148230A0B1857,,Chat WhatsApp,209,2,One-to-One,Bubble Chat,--
```
