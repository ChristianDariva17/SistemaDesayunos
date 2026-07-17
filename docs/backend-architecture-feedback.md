# Backend Architecture Feedback

This document captures the current backend architecture assessment and the recommended improvement path for SistemaDesayunos. Use it as a practical roadmap before adding larger backend features.

## Current Diagnosis

The backend is a **Laravel 12 MVC web application** with a partial move toward **Action/Service-oriented layering** for important business flows.

It is **not currently a Clean Architecture or Hexagonal Architecture implementation**. It is also **not API-first** yet. The system is mainly a Blade-driven Laravel backend with controllers, Eloquent models, Form Requests, Policies, and selected Actions.

## Architecture Observed

| Area | Current state |
|------|---------------|
| Framework | Laravel 12 with PHP 8.2+ |
| Main architecture | Traditional Laravel MVC |
| Secondary pattern | Partial layered architecture using Actions and Services |
| UI delivery | Blade views with progressive JavaScript enhancements |
| Authorization | Policies and role-based route groups |
| Validation | Form Request classes |
| Persistence | Eloquent models, relationships, scopes, casts, and migrations |
| Testing | Pest feature/unit tests |
| API boundary | No clear API-first surface verified yet |

## Strengths

- Form Requests are used for validation and authorization boundaries.
- Policies centralize access control for important resources.
- Business-critical flows are starting to move into Actions.
- Pedido and stock operations use transactions where appropriate.
- Stock reservation uses pessimistic locking with `lockForUpdate()`, which is good for inventory consistency.
- Eloquent relationships and scopes are used instead of raw SQL-heavy code.
- The project has meaningful Pest coverage around business flows.
- Recent UI behavior was protected with PHP and JavaScript tests.

## Main Risks and Gaps

### 1. Controllers still contain too much logic

Some controllers still coordinate filtering, statistics, transactions, logging, view data, and response decisions directly.

Priority areas:

- `Admin\PedidoController`
- `Trabajador\PedidoController`
- `Admin\ProductoController`
- `Admin\ClienteController`

### 2. Pedido logic is duplicated between roles

Admin and trabajador pedido flows share similar list filtering, stats, create/show data preparation, and update behavior.

This should be extracted into shared query/services where the controller only applies role-specific authorization and response handling.

### 3. Business states are still string-based

Repeated string states such as `pendiente`, `procesando`, `completado`, `cancelado`, `activo`, and `inactivo` are easy to mistype and hard to refactor safely.

### 4. There is no formal API boundary yet

The backend currently behaves as a web-first Blade application. That is fine for the current system, but if a mobile app, SPA, external dashboard, or third-party integration is planned, the API boundary should be designed explicitly.

### 5. Static analysis now protects new changes

Larastan/PHPStan analyzes `app/` at level 5. A generated baseline preserves the current legacy backlog while making new findings fail the quality gate.

## Recommended Architecture Direction

Do not jump directly into full Clean Architecture. For this system, the best next step is a pragmatic Laravel layered architecture:

```text
Laravel MVC
+ Form Requests
+ Policies
+ Actions per use case
+ Query Objects for filters and listings
+ Services for coordination/statistics/reporting
+ Enums for business states
+ API Resources only when API endpoints are introduced
```

Recommended structure:

```text
app/
├── Actions/
│   ├── Pedido/
│   ├── Producto/
│   └── Inventory/
├── Queries/
│   ├── PedidoQuery.php
│   ├── ProductoQuery.php
│   └── ClienteQuery.php
├── Services/
│   ├── PedidoStatsService.php
│   ├── ReportService.php
│   └── InventoryService.php
├── Enums/
│   ├── PedidoStatus.php
│   ├── ProductoEstado.php
│   └── MetodoPago.php
├── DTO/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
└── Models/
```

## Prioritized Improvement Plan

### 1. Extract pedido filtering and statistics

Pedido list filtering and statistics now use shared classes in both admin and trabajador controllers. Shared mutation Actions also cover the core create, update, delete, and duplicate flows.

Implemented classes:

- `App\Queries\PedidoQuery`
- `App\Services\PedidoStatsService`

Remaining opportunity: extract repeated create/edit view-data preparation if it continues to grow.

### 2. Introduce PHP Enums for business states

The repeated pedido, producto, payment, and stock movement states now have enums.

Implemented enums:

- `PedidoStatus`
- `ProductoEstado`
- `PaymentMethod`
- `StockMovimientoTipo`

Example:

```php
enum PedidoStatus: string
{
    case Pendiente = 'pendiente';
    case Procesando = 'procesando';
    case Completado = 'completado';
    case Cancelado = 'cancelado';
}
```

### 3. Slim down product and client controllers

Product and client list filtering now use query objects, and their admin status transitions now use explicit Actions. Statistics and the remaining business decisions still need to move out of the controllers incrementally.

Implemented classes:

- `App\Queries\ProductoQuery`
- `App\Queries\ClienteQuery`
- `App\Actions\Producto\ToggleProductoStatusAction`
- `App\Actions\Cliente\ToggleClienteStatusAction`

### 4. Harden multi-product pedido creation against deadlocks

Pedido creation now acquires product row locks in canonical ascending `producto_id` order and retries the complete transaction up to three times when Laravel detects a concurrency error.

Implemented changes:

- `HandlesPedidoProductStock` sorts normalized product reservations before calling `lockForUpdate()`.
- `CreatePedidoAction` uses three transaction attempts.
- `PedidoCreationDeadlockMitigationTest` covers deterministic ordering, rollback, and a simulated SQLSTATE `40001` retry.

Verification completed:

- Focused suite: 50 tests, 304 assertions.
- Full suite: 368 tests, 1819 assertions.
- Bounded 4R review: approved without findings under `review-1626dde0cfbb8e26`.

Limitation: SQLite verifies ordering and retry behavior but does not reproduce real parallel MySQL/InnoDB lock contention. Add a MySQL concurrency harness only if production-equivalent proof becomes necessary.

### 5. Add Larastan/PHPStan

Static analysis is installed through `larastan/larastan` 3.x and runs against `app/` at level 5.

```bash
composer analyse
```

Adopted strategy:

- `phpstan.neon.dist` loads the Larastan and Carbon extensions.
- `phpstan-baseline.neon` records 30 existing findings; new findings must be fixed rather than added to the baseline.
- Baseline entries should be removed as the corresponding legacy findings are remediated, then the analysis level can increase incrementally.

Verification completed:

- Static analysis: 100 files analyzed, no errors outside the baseline.
- Full suite: 368 tests, 1819 assertions.

### 6. Add an API boundary only when there is a real consumer

If the system needs a mobile app, SPA, or external integration, add:

- `routes/api.php`
- versioned endpoints, such as `/api/v1/...`
- Laravel Sanctum authentication
- API Resources
- API rate limiting

Do not add this only for aesthetics. Add it when the product needs it.

### 7. Move heavy reporting/export work to queues

If reports, exports, notifications, or stock calculations become slow, move them to jobs.

Recommended stack:

- Redis
- Laravel Queues
- Laravel Horizon

### 8. Upgrade permissions only if roles become dynamic

Current policies are enough for simple fixed roles like admin/trabajador.

Use `spatie/laravel-permission` only if the system needs database-managed permissions or admin-configurable roles.

## Useful APIs and Libraries

| Tool | Use when | Why it helps |
|------|----------|--------------|
| Larastan / PHPStan | Now | Finds type, relation, and framework mistakes before runtime |
| Laravel API Resources | When exposing JSON APIs | Keeps API responses stable and avoids returning raw models |
| Laravel Sanctum | When adding mobile, SPA, or token API auth | Simple first-party API authentication for Laravel |
| Spatie Laravel Query Builder | When API/list filters grow | Standardizes filtering, sorting, includes, and pagination |
| Redis + Laravel Queues | When work becomes slow | Moves reports, exports, notifications, and long tasks to background jobs |
| Laravel Horizon | When using Redis queues | Gives visibility into jobs, retries, failures, and throughput |
| Laravel Telescope | During development/debugging | Inspects requests, queries, jobs, exceptions, and mail |
| Laravel Pulse | For operational monitoring | Helps observe application performance and behavior |
| Spatie Laravel Permission | Only if permissions become dynamic | Manages roles and permissions in the database |
| OpenAPI / L5 Swagger | If third parties consume the API | Documents API contracts for external consumers |

## API Roadmap

Only apply this if the project needs mobile, SPA, or third-party integrations.

### Suggested API modules

- Auth/session/token API
- Productos API
- Clientes API
- Pedidos API
- Stock API
- Reportes API

### Recommended API conventions

- Prefix routes with `/api/v1`.
- Use Sanctum for authentication.
- Use API Resources for response formatting.
- Use Form Requests for validation.
- Use rate limiting for public or token endpoints.
- Keep API controllers thin.
- Reuse existing Actions and Services instead of duplicating business logic.

## Implementation Checklist

- [x] Create enums for pedido/producto/payment/stock states.
- [x] Extract pedido list filters into a query object.
- [x] Extract pedido statistics into a service.
- [x] Share pedido query, statistics, and core mutation Actions between admin/trabajador controllers.
- [x] Extract producto and cliente filtering into query objects.
- [x] Extract admin producto and cliente status transitions into Actions.
- [x] Mitigate multi-product pedido deadlocks with canonical lock ordering and bounded transaction retries.
- [ ] Continue extracting producto and cliente statistics and remaining business decisions.
- [x] Add Larastan/PHPStan and document the command.
- [ ] Decide whether an API is really needed.
- [ ] If API is needed, introduce Sanctum, API Resources, and `/api/v1` routes.
- [ ] Move heavy reports/exports to queued jobs if performance becomes a problem.
- [ ] Consider Spatie Permission only if roles/permissions become dynamic.

## Final Recommendation

The next backend quality jump should not be adding more features. It should be **separating responsibilities**.

The current backend works and has good foundations, but maintainability will improve significantly by moving repeated query/stat/business logic out of controllers and into explicit Actions, Queries, Services, and Enums.

## Continuation Point

Larastan/PHPStan adoption is implemented and verified at level 5 for `app/`, with the current 30-finding legacy backlog captured in a generated baseline. No commit or push has been performed.

Next session, continue extracting **producto and cliente statistics and remaining business decisions** from their controllers. Treat both baseline reduction and the MySQL concurrency harness as incremental follow-up work rather than blockers.
