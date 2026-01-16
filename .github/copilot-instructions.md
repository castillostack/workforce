# Copilot Instructions for WFM App

## Project Overview
This is a Workforce Management (WFM) system built with Laravel 11.x and PostgreSQL, focused on contact center operations. It ingests raw data from Cisco UCCX, forecasts demand using Erlang-C, optimizes schedules, and tracks real-time adherence.

## Architecture
- **Data Layers**: Raw (crude Cisco data), Operational (HR/schedules), Analytical (metrics/reports), Quality/Forecasting (evaluations/simulations).
- **Key Components**: Eloquent models in `app/Models/`, controllers in `app/Http/Controllers/`, migrations in `database/migrations/`.
- **Boundaries**: Monolithic with modular services; use jobs/queues for ETL from raw tables to analytical.
- **Data Flow**: Raw data → Operational aggregation → Analytical dashboards; all dates in UTC, 15-min intervals.

## Critical Workflows
- **Setup**: `composer install`, `npm install && npm run dev`, `php artisan migrate:fresh` (resets DB).
- **Development**: `php artisan serve` for local server; use `php artisan tinker` for DB exploration.
- **Testing**: Run with `./vendor/bin/pest` (Pest framework); config in `phpunit.xml`.
- **Debugging**: Logs in `storage/logs/`, use `dd()` or Laravel Debugbar; check `.env` for DB config.

## Conventions & Patterns
- **Dates/Times**: Always UTC in DB; convert to local in UI. Use Carbon for handling.
- **Migrations**: Use `bigIncrements()` for IDs; add soft deletes (`$table->softDeletes()`) to critical tables.
- **Models**: Define relationships in Eloquent (e.g., `Employee` belongsTo `User`, hasMany `Schedules`).
- **Permissions**: Use Spatie Permission package; roles/permissions in `database/migrations/2026_01_15_195725_create_permission_tables.php`.
- **Raw Tables**: Store Cisco data as-is; index on agent/time fields (e.g., `calls_raw` has indexes on `hora_de_inicio`, `nombre_del_agente`).
- **Forecasting**: Account for shrinkage in Erlang-C calculations; AHT = (Talk + Hold + Work) / Calls.
- **Security**: RBAC on all endpoints; agents see only their data.

## Integrations
- **Database**: PostgreSQL with JSON fields; foreign keys for integrity (e.g., `employees.username` links to raw tables).
- **External**: Cisco UCCX for raw logs; potential Google OR-Tools for scheduling solver.
- **Frontend**: Laravel Blade + Alpine.js; build with Vite (`npm run build`).

## Key Files
- `docs/DATA_MODEL.md`: 27-table schema overview.
- `docs/DDL.md`: SQL for table creation.
- `.github/instructions/wfm-rpt.instructions.md`: Detailed WFM rules.
- `database/migrations/`: All schema changes; run in order.</content>
<parameter name="filePath">/home/ferncastillo/Proyectos/php/wfm-app/.github/copilot-instructions.md