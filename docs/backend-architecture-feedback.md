# Backend Architecture Audit and Roadmap

This document is the authoritative backend assessment for SistemaDesayunos as of 2026-07-20. It replaces assumption-based recommendations with evidence from the current Laravel code, routes, tests, static analysis, dependency lock, and the existing improvement plan.

## Executive Summary

The backend is a Laravel 12.28.1, PHP 8.2+ Blade application with a pragmatic layered architecture: MVC plus Form Requests, Policies, Actions, Query Objects, Services, Enums, and Eloquent. This remains the right direction. A Clean/Hexagonal rewrite, repositories for every model, DTOs by default, or an API layer without a real consumer would add ceremony without solving a current problem.

The strongest boundaries are the pedido and inventory transaction flows. Pedido creation reserves products in canonical key order, retries concurrency failures, records stock movements in the same transaction, and dispatches events after commit. Database money columns are decimal, core reports use eager loading or aggregate queries, dashboard summaries are cached, and the project has broad feature coverage plus a passing Larastan/PHPStan level 5 gate.

The dependency-security P1 gate is complete: the locked graph now reports four advisories across two packages, with no critical or high findings; the remaining one medium PsySH advisory and three low Symfony YAML advisories stay tracked as lower-severity maintenance. Report money correctness and both legacy product stock-path consolidations are also complete: report flows now use canonical decimal strings/integer cents, and `ProductoController::update` and `actualizarStock` delegate stock mutation to the canonical adjustment Action. Dedicated stock entry and adjustment requests now enforce `ProductoPolicy::updateStock` in addition to administrator-only route middleware, and adjustment reasons are normalized for Unicode separator/whitespace and FEFF edge cases before validation. The three routed admin CSV exports for products, clients, and orders now retrieve data inside streamed callbacks and iterate in configurable fixed-size chunks with stable ID tie-break ordering. Report production hardening is complete for the four admin PDF routes: unexpected `Throwable` failures receive generic public responses and structured internal logging, sales-by-client PDF is restored and discoverable with decimal-safe totals, and configurable synchronous limits default to 250 rows and 31 days with validation/preflight rejection. The worker order CSV, broader controller error-boundary cleanup, and deployment-environment checks remain separate follow-ups.

## Audit Scope and Evidence

| Area | Evidence reviewed |
|---|---|
| Request boundary | `routes/web.php`, `php artisan route:list --path=stock --json`, role middleware, Form Requests, Policies, controllers |
| Architecture | Controllers, Actions, Query Objects, Services, Enums, Eloquent models and call paths |
| Correctness | Money helpers/casts, migrations, stock ledger, transaction and locking code |
| Performance | Listing queries, eager loading, aggregates, cache usage, pagination, CSV/PDF exports |
| Operations | Structured business logs, events, exception paths, queue placeholder |
| Quality | Pest tests, `composer analyse`, `phpstan.neon.dist`, `phpstan-baseline.neon` |
| Supply chain | `composer.lock`, installed package versions, `composer audit --locked --format=json` |

The completed dependency-security work unit changed only `composer.lock`; `composer.json` and application source, tests, migrations, and configuration remained unchanged.

## Current Architecture

| Area | Current state | Assessment |
|---|---|---|
| Delivery | Server-rendered Blade with progressive JavaScript | Appropriate for the current product |
| HTTP boundary | Controllers plus Form Requests and route middleware | Strong overall; dedicated stock writes now have route and contextual Policy defense in depth |
| Authorization | Explicit Policies registered in `AppServiceProvider`; fixed admin/worker roles | Appropriate while permissions remain static |
| Use cases | Pedido, inventory, stock, cash, and status Actions | Good direction; both legacy product stock paths now delegate to the canonical adjustment Action |
| Reads | `PedidoQuery`, `ProductoQuery`, `ClienteQuery`, reporting/statistics Services | Useful extractions justified by shared filtering or aggregate complexity |
| Persistence | Eloquent, decimal casts, relationships, scopes, checks, foreign keys, indexes | Strong baseline with driver-specific verification limits |
| Observability | Business events, after-commit dispatch, structured failure logger, controller logs | Partially standardized |
| Static analysis | Larastan 3.10/PHPStan 2.2, `app/`, level 5, 28-finding baseline | Passing gate with a bounded legacy backlog |
| API | No verified API-first surface | Do not add Resources/Sanctum until a consumer exists |

## Strengths

- `AppServiceProvider::boot` explicitly maps Policies for Pedido, Producto, Cliente, Empleado, and DailyCashClosure.
- Core Form Requests use contextual Policy checks, including `StorePedidoRequest::authorize` and `UpdatePedidoRequest::authorize`.
- `CreatePedidoAction::handle` owns the transaction, uses three attempts, and delegates stock writes to explicit inventory Actions.
- `HandlesPedidoProductStock::attachProductsAndReserveStock` sorts product IDs before `ReserveProductoStockAction` takes row locks.
- Stock updates and `StockMovimiento` ledger writes share transaction boundaries in the pedido, stock entry, and stock adjustment Actions.
- `MoneyDecimal` performs cents-based multiplication and summation; Eloquent money casts use `decimal:2`.
- Admin report index, inventory, low-stock, and sales flows prepare canonical decimal strings/integer cents, exact line/grand totals, half-up averages, gross-preserving 18% tax splits, and `0.00` empty values without business-money float arithmetic.
- `RegisterStockAdjustmentAction` owns typed operations, locking, bounds including active reservations, persistence, no-op behavior, exactly-one ledger registration, typed results, and rollback for both legacy product stock paths.
- `PedidoQuery::paginate` eager-loads cliente/empleado and uses `withCount('productos')`; stock reports eager-load their displayed relationships.
- `DashboardSummaryService::summary` consolidates aggregates and caches the dashboard summary for 60 seconds.
- `ClienteStatsService::indexSummary` replaced four admin cliente count queries with one conditional aggregate query.
- Feature tests cover role access, Policies, stock ledger behavior, rollback, pedido transitions, decimal arithmetic, migration checks, reports, and deadlock mitigation.
- `composer analyse -- --no-progress` passes with no errors outside the committed baseline.

## Prioritized Findings

No P0 issue was verified. P0 remains reserved for an active exploit, confirmed data corruption, or an outage requiring immediate containment.

| ID | Priority | Status | Finding |
|---|---|---|---|
| F-01 | P1 | Completed | The high/critical dependency-security gate is closed; one medium and three low advisories remain tracked as lower-severity maintenance. |
| F-02 | P2 | Completed | Admin report index, inventory, low-stock, and sales money calculations use canonical decimal strings/integer cents with exact totals and defined rounding. |
| F-03 | P2 | Completed | `ProductoController::update` and `actualizarStock` delegate stock mutation and ledger orchestration to `RegisterStockAdjustmentAction`. |
| F-04 | P2 | Completed | Dedicated stock entry/adjustment routes remain admin-only and their Form Requests now enforce `ProductoPolicy::updateStock`. |
| F-05 | P2 | Completed for admin scope | The three routed admin CSV exports use bounded chunks, and all four admin PDFs enforce configurable synchronous row/date limits; the worker CSV remains separate follow-up. |
| F-06 | P2 | Completed for report scope | All four admin PDF routes return generic public failures and log structured internal `Throwable` context; broader controller cleanup remains incremental. |
| F-07 | P3 | Conditional | Deadlock ordering/retry is tested on SQLite and simulated SQLSTATE failures; real concurrent MySQL/InnoDB coverage is warranted only if deployment risk, incidents, or volume justify it. |
| F-08 | P3 | In progress | Product/worker-client statistics and some query preparation remain duplicated in controllers. |
| F-09 | P3 | In progress | Static analysis passes, but 28 findings remain baselined and analysis covers only `app/` at level 5. |
| F-10 | P3 | In progress | Enums exist, but state transitions and comparisons still mix enum values with string literals. |

### F-01: Dependency Security Gate

**Evidence**

- Before remediation, `composer audit --locked --format=json` reported 24 advisories across 13 packages: four high, 15 medium, four low, and one unspecified, with no critical advisories.
- Remediation updated 73 locked packages within the existing `composer.json` constraints; `composer.json` remained unchanged.
- The current audit reports four advisories across two packages: zero critical, zero high, one medium in PsySH, and three low in Symfony YAML.
- `composer prohibits php 8.2 --locked` reports no installed package incompatible with PHP 8.2.
- Native high-risk review lineage `review-fcad4a3aadb105e0` approved the corrected `composer.lock` candidate after five Symfony 8.1 packages requiring PHP >=8.4.1 were detected and corrected; the review receipt exists.

**Risk**

The high/critical security gate is closed, but the dependency graph is not advisory-free. The remaining medium and low findings require continued tracking and upgrade/reachability review rather than a claim of complete dependency remediation.

**Recommendation**

Keep the one medium and three low advisories visible in routine dependency maintenance. Resolve them when compatible updates are available, or document any reachability exception with an owner and expiry; do not weaken the completed high/critical security gate.

### F-02: Report Money Correctness

**Evidence**

- The admin report index, inventory, low-stock, and sales controller/view flows no longer perform business-money float arithmetic.
- Report data uses canonical decimal strings/integer cents for exact line and grand totals, half-up averages, gross-preserving 18% tax splits, and `0.00` empty values.
- Native review lineage `review-5b8f34432bb9d766` approved the completed report-money work unit; the final combined suite later passed 452 tests with 2267 assertions.
- The restored and discoverable sales-by-client PDF preserves the same decimal-safe total boundary and has access, data, and rendering coverage.

**Risk**

The verified report money paths now preserve decimal correctness at calculation and rendering boundaries, including the restored sales-by-client PDF. Synchronous generation remains intentionally bounded rather than suitable for arbitrary report volume.

**Recommendation**

Preserve the canonical decimal-string/integer-cent boundary and the sales-by-client access, data, and rendering regression coverage. Keep larger export architecture conditional on measured demand.

### F-03: Legacy Stock Write Consolidation

**Evidence**

- `app/Http/Controllers/Admin/ProductoController.php:update` and `actualizarStock` now delegate stock changes to `RegisterStockAdjustmentAction`.
- The Action owns typed increment/decrement/set operations, its transaction, a product row lock, `InventoryLimits` bounds, active-reservation floor enforcement, persistence, exactly-one adjustment ledger registration, no-op behavior, a typed result, and rollback.
- Product edit remains atomically coupled to non-stock updates and image compensation. The same product lock makes the active-reservation floor check race-safe.
- Native review lineage `review-35fbc358416abded` approved the corrected work unit after review identified and correction fixed stock being lowered below active reservations.

**Risk**

The identified controller/Action invariant drift is closed. The later dedicated stock-request work unit also closed the separate contextual authorization gap without moving stock invariants back into controllers.

**Recommendation**

Keep both legacy product routes on the canonical Action boundary and preserve the reservation-floor regression tests. Keep authorization at the route middleware and FormRequest/Policy boundaries without moving stock invariants back into controllers.

### F-04: Dedicated Stock Authorization Defense in Depth

**Evidence**

- `StoreStockEntryRequest::authorize` and `StoreStockAdjustmentRequest::authorize` now delegate to `ProductoPolicy::updateStock` rather than returning `true`.
- `ProductoPolicy::updateStock` supports class-level checks before a valid product is resolved and existing instance-level checks without changing administrator-only semantics or the legacy role aliases.
- Route inspection confirms both flows continue to use `web`, `auth`, `valid.role`, and `rol:administrador`; the FormRequest/Policy check is an additional boundary, not a middleware replacement.
- An invalid `producto_id` remains owned by validation rather than producing a premature authorization 403.
- Dedicated adjustment reasons normalize Unicode separator/whitespace and FEFF at the request boundary before `required`/`max` validation: blank input becomes `null`, while meaningful padded text persists normalized.
- Focused verification passed 35 tests with 141 assertions, production PHPStan, explicit Pint over the five changed files, and scoped diff checks. Native review `review-8f67966afa8fabf3` approved the 195-authored-line work unit with zero findings.

**Risk**

The verified dedicated HTTP paths now have defense in depth. This completion does not prove that every sensitive operation across the application has contextual authorization, so broader authorization review remains incremental work rather than part of this claim.

**Recommendation**

Preserve administrator route middleware as the first boundary and `ProductoPolicy::updateStock` in both dedicated Form Requests as the contextual boundary. Keep invalid product IDs validation-owned and retain regression coverage for class-level authorization, existing instances, legacy aliases, and Unicode reason normalization.

### F-05: Export and PDF Memory Use

**Evidence**

- `Admin\ProductoController::exportar`, `Admin\ClienteController::exportar`, and `Admin\PedidoController::exportar` now build their queries inside streamed callbacks and iterate in configurable fixed-size chunks rather than materializing complete collections before streaming.
- Existing filters and CSV column/value contracts are preserved. User-selected ordering gains a stable ID tie-break, and each pedido chunk eager-loads its required cliente, empleado, and productos relationships.
- Focused new tests passed 3 tests with 35 assertions; the relevant combined suite passed 72 tests with 413 assertions. Pint passed over five owned paths, scoped PHPStan passed over the three production controllers, and scoped diff checks passed.
- Native review lineage `review-d9feb10b8721f04a` covered 458 authored changed lines at high tier/full 4R and reached terminal approval with no blocking findings; its receipt was materialized.
- `ReporteController::inventario`, `ventas`, `stockBajo`, and `ventasPorCliente` enforce configurable synchronous limits that default to 250 source rows and 31 days. Validation and preflight counts reject oversized requests before fetching full report data or invoking DomPDF.
- `Trabajador\PedidoController::exportar` remains outside this completed admin report scope.

**Risk**

The completed admin CSV paths bound application memory per chunk, but OFFSET-based chunks do not provide snapshot consistency: concurrent inserts, deletes, or sort-key changes can cause omissions or duplicates. The bounded synchronous PDF preflight has a smaller count-then-fetch race: a concurrent insert after `COUNT` can marginally exceed the configured row cap. Snapshot or queue architecture would close that race but is deferred as a non-blocking residual risk.

**Recommendation**

Preserve the bounded admin CSV and PDF behavior, including filters, ordering, relationship loading, preflight rejection, and regression coverage. Consider snapshot/keyset or queued report generation only if concurrent consistency or larger-volume requirements justify the operational cost.

### F-06: Inconsistent Error Boundary

**Evidence**

- All four admin PDF report routes catch unexpected `Throwable` failures, return stable generic public feedback, and use `BusinessOperationLogger::failure` for structured internal context.
- Focused tests cover data-query and PDF-rendering failures without exposing exception messages, files, or line numbers.
- `Admin\PedidoController` and `Trabajador\PedidoController` still append `$e->getMessage()` to several user-facing errors outside the completed report scope.

**Risk**

The four admin PDF routes now separate validation feedback from unexpected infrastructure failures. Other broad controller catches may still disclose internals and remain incremental cleanup rather than a blocker for the approved report work.

**Recommendation**

Preserve the report boundary and extend the same validation/domain-versus-unexpected-failure distinction to other controllers in small, independently reviewed slices. Centralize only where repetition justifies it; do not create an abstract exception framework for its own sake.

### F-07: Production Concurrency Proof Is Incomplete

**Evidence**

- `HandlesPedidoProductStock::attachProductsAndReserveStock` orders product IDs before `lockForUpdate()`.
- `CreatePedidoAction::handle` sets `attempts: 3` on the transaction.
- `PedidoCreationDeadlockMitigationTest` covers deterministic order, rollback, and simulated SQLSTATE `40001` retry.
- The test environment uses SQLite and cannot reproduce InnoDB row-lock scheduling or real deadlocks.

**Recommendation**

Treat the mitigation as implemented and unit/feature verified, not production-concurrency proven. Add a MySQL/InnoDB two-connection harness only if deployment risk, incident history, or transaction volume justifies the maintenance cost.

### F-08 to F-10: Maintainability Backlog

**Evidence**

- `Trabajador\ClienteController::index` duplicates filtering, sorting, pagination, and four aggregates instead of using `ClienteQuery` and `ClienteStatsService`.
- Product index/statistics aggregates remain in admin/worker controllers; `ProductoController::estadisticas` executes multiple independent aggregates.
- `phpstan-baseline.neon` contains 28 findings, including relation/pivot typing, return types, and controller/model type mismatches.
- `UpdatePedidoAction` and other flows still compare literal states even though `PedidoStatus`, `ProductoEstado`, `PaymentMethod`, and `StockMovimientoTipo` exist.

**Recommendation**

Extract only shared or genuinely complex behavior. Reuse `ClienteQuery`/`ClienteStatsService` across roles, introduce a `ProductoStatsService` if both controllers need the same summary, and remove baseline entries in small behavior-preserving slices. Do not add repositories or DTOs unless persistence substitution or transport complexity creates a concrete need.

## Recommended Architecture Direction

```text
Laravel MVC
+ Form Requests for input and contextual authorization
+ Policies for resource/action permissions
+ Actions for transactional use cases and business state changes
+ Query Objects for shared filtering/listing/export semantics
+ Services for reusable aggregate/reporting coordination
+ Enums for closed business state sets
+ API Resources only when a real API consumer exists
```

Controllers should authorize, validate, invoke a use case/query, and select the response. Models should retain relationships, casts, scopes, and small state helpers. Transaction boundaries belong in the Action that owns the complete write invariant.

## Incremental Roadmap

| Slice | Priority | Scope | Completion signal |
|---|---|---|---|
| 1. Dependency security gate (completed) | P1 | Maintain the corrected lock within existing constraints and track four lower-severity advisories | No critical/high advisories; audit and full quality gates pass; medium/low follow-up remains explicit |
| 2. Money/report correctness (completed) | P2 | Preserve decimal-safe report aggregation and rendering | Report precision tests pass; no business-money float accumulation remains in the covered flows |
| 3. Inventory write consolidation (completed) | P2 | Keep both legacy product stock write paths on one inventory Action | Both legacy paths use one Action with shared bounds, lock, ledger, transaction, no-op, and rollback behavior |
| 4. Dedicated stock authorization (completed) | P2 | Preserve route middleware plus contextual FormRequest/Policy checks and Unicode reason normalization | Stock requests enforce `updateStock`; invalid IDs remain validation-owned; normalized reasons satisfy required/max semantics |
| 5. Routed admin CSV bounding (completed) | P2 | Preserve configurable chunking, stable ordering, filters, CSV contracts, and per-chunk pedido eager loading | Product, client, and order admin CSVs do not materialize complete result sets before streaming |
| 6. PDF export safety (completed) | P2 | Preserve configurable 250-row/31-day defaults and validation/preflight rejection across all four admin PDFs | Oversized synchronous requests are rejected before full fetch or DomPDF rendering |
| 7. Report public-error hardening (completed) | P2 | Preserve generic public PDF failures and structured internal `Throwable` logging | Data and rendering exceptions are logged internally and not exposed publicly |
| 8. Controller/static cleanup | P3 | Share worker cliente/product stats and reduce baseline incrementally | No duplicate role query logic; baseline count decreases without exclusions |
| 9. Production concurrency harness | Conditional P3 | Add MySQL/InnoDB concurrent-session coverage | Required only by measured risk or production-equivalent verification need |

## Conditional Libraries and External Integrations

These are evaluation candidates, not an installation backlog. The dependency high/critical gate is complete; review the four remaining lower-severity advisories before expanding the dependency graph, and adopt each item only when its acceptance condition is demonstrated.

### Database Access Decision

Keep **Eloquent as the only ORM**. Use Eloquent for domain persistence and relationships, Laravel Query Builder for reports and aggregate-heavy reads, and raw SQL only for database constraints or specific queries that cannot be expressed clearly or efficiently through those two APIs.

Do not add Doctrine, a MongoDB integration, or another ORM without a demonstrated persistence problem. A second mapping model would duplicate configuration, transactions, casts, and team knowledge while weakening the current Eloquent-based invariants. A different database or ORM is justified only by measured requirements that the existing relational model and Laravel data-access stack cannot meet.

### Framework and Operations Libraries

| Candidate | Condition and relative priority | Acceptance signal |
|---|---|---|
| Redis + Laravel Horizon | After the completed high/critical security gate, when exports, jobs, or notifications must leave the request lifecycle | Redis is available and operated in every required environment; worker deployment, supervision, retries, failed-job handling, and monitoring have owners |
| Laravel Pulse | First observability option when Laravel-centric operational metrics are needed | The team needs visibility into throughput, slow requests, queues, or application performance and has defined retention and production access controls |
| Sentry | First observability option when centralized error tracking is needed | The team needs exception grouping, release correlation, alerting, and actionable stack traces; sensitive-data scrubbing is configured |
| Laravel Sanctum | Only when a real SPA, mobile application, or token API consumer exists | The consumer and authentication flow are documented, including abilities, expiration/revocation, rate limits, and tests |
| Laravel Excel | Only when XLSX generation, XLSX parsing, or spreadsheet imports are product requirements | A concrete workbook/import contract exists; simple CSV exports continue to use native streamed responses |
| Laravel Telescope | Local and development diagnostics only | Access is restricted, sensitive entries are filtered, retention is bounded, and it is never publicly exposed |
| Laravel Pennant | Only when feature flags or gradual rollouts are required | Flag ownership, targeting, defaults, rollback, and stale-flag removal are defined |

Pulse and Sentry solve different observability problems: Pulse provides operational application metrics, while Sentry tracks and groups errors. Use one or both only after defining the missing signal; neither replaces structured application logs.

### Conditional Business APIs

| API/provider | Adopt only when | Required boundary |
|---|---|---|
| WhatsApp Cloud API | Order-status messaging becomes an approved product requirement with customer consent | Notification gateway with template/version handling, delivery-status processing, opt-out rules, and idempotent sends |
| Mercado Pago | Online payment collection and webhook-driven payment state are required | Payment gateway with server-side amount verification, signed webhook validation, idempotent state transitions, and reconciliation |
| Postmark, Resend, or Mailgun | Transactional email needs delivery infrastructure beyond the configured Laravel mail transport | Mail provider adapter selected from measured deliverability, regional, cost, and support requirements; process bounces and complaints |
| Google Maps | Delivery, routing, geocoding, or address-validation requirements appear | Maps gateway with usage limits, caching where permitted, cost monitoring, and a fallback for provider failure |

### Integration Rules

Every external provider must follow the same boundary rules:

- Place the provider SDK or HTTP client behind an application-owned Adapter/Gateway interface; domain and use-case code must not depend on provider types.
- Store secrets in environment variables exposed through `config/*`; never read `env()` outside configuration or commit credentials.
- Set explicit connection and request timeouts. Apply bounded retries with exponential backoff and jitter only to retry-safe failures.
- Move slow or failure-prone calls to queued jobs when they do not need to complete the current request.
- Define idempotency keys and duplicate-handling behavior for outbound commands and inbound webhooks.
- Validate webhook signatures against the raw request payload, reject stale or replayed events where the provider supports it, and acknowledge only accepted events.
- Emit structured logs and useful metrics without credentials, payment data, tokens, or unnecessary personal information.
- Test through interface fakes, including timeout, retry, duplicate webhook, invalid signature, and provider-unavailable paths.
- Never keep a database transaction open during an external network call. Persist intent, commit, and then dispatch the integration; process the result in a separate transaction.

### Adoption Order

1. Preserve the completed dependency high/critical gate and continue tracking the one medium and three low advisories before adding packages.
2. Add the minimum observability capability that closes a defined operational gap: Pulse for operational metrics, Sentry for error tracking, or both when both gaps exist.
3. Add Redis and Horizon only when measured or required asynchronous work justifies worker operations.
4. Add business APIs only from an approved product requirement, never as speculative infrastructure.

### Dependency Acceptance Checklist

- [ ] The requirement, consumer, owner, and measurable success condition are documented.
- [ ] Existing Laravel or PHP capabilities were evaluated and are insufficient for the requirement.
- [ ] Security advisories, maintenance activity, license, Laravel/PHP compatibility, and transitive dependencies were reviewed.
- [ ] Operational costs are accepted: credentials, workers, dashboards, alerts, quotas, retention, upgrades, and incident ownership.
- [ ] The integration has an application-owned interface and does not leak provider types into business logic.
- [ ] Timeout, retry, idempotency, webhook security, logging/metrics, and failure behavior are defined where applicable.
- [ ] Tests use fakes and cover provider failure and duplicate delivery without requiring the live service.
- [ ] A removal or replacement path exists, including data migration and feature-flag cleanup where applicable.
- [ ] Installation is a separate reviewed work unit and does not weaken the existing security gate.

## Acceptance and Verification

### Security and Authorization

- [x] `composer audit --locked` has no unresolved critical or high advisory.
- [ ] Resolve or document the remaining one medium and three low advisories through ongoing dependency maintenance.
- [x] Dedicated stock entry and adjustment requests enforce `ProductoPolicy::updateStock` in addition to route middleware.
- [x] Guest and worker denial is covered for stock entry and adjustment routes.
- [x] Login requests are rate limited and unsupported roles are rejected.

### Correctness and Integrity

- [x] Business money columns and casts use decimal semantics.
- [x] Pedido totals and daily cash closure sums use `MoneyDecimal`.
- [x] Covered admin report totals avoid binary floating-point accumulation and use canonical decimal values.
- [x] Core pedido/inventory writes use transactions, row locks, ledger records, and after-commit events.
- [x] Both legacy manual product stock routes use the canonical adjustment Action, bounds, lock, ledger, transaction, and reservation floor.

### Performance and Operations

- [x] Interactive pedido and stock movement listings paginate and eager-load displayed relations.
- [x] Dashboard aggregates are consolidated and cached for 60 seconds.
- [x] The three routed admin CSV exports for products, clients, and orders iterate in configurable fixed-size chunks inside streamed callbacks.
- [x] All four admin PDF routes reject requests above configurable synchronous limits, defaulting to 250 rows and 31 days, before full fetch or DomPDF rendering.
- [x] Unexpected exceptions on all four admin PDF routes are logged internally without exposing message/file/line details to users.
- [ ] Verify `APP_DEBUG=false`, production secrets/database configuration, configuration caching, and the backup/migration/rollback procedure in the deployment environment.
- [ ] Decide the compatible PsySH v0.12.10 to v0.12.24 security update; until patched, do not run Tinker/PsySH from attacker-writable directories.

### Quality Gates

- [x] `composer analyse` passes at level 5 with the current 28-finding baseline.
- [ ] Baseline entries decrease over time and are not increased for new code.
- [x] Focused tests cover the completed dependency, report-money, legacy stock-path, dedicated stock-authorization/Unicode-normalization, and bounded routed-admin-CSV slices.
- [x] The integrated report production-hardening verification passed focused and full test suites, static analysis, formatting, JavaScript tests, and an isolated Vite build.

## Verification Snapshot

| Check | Result |
|---|---|
| `composer validate --strict` | Passed |
| `composer prohibits php 8.2 --locked` | Passed: no installed package is incompatible with PHP 8.2 |
| `composer analyse -- --no-progress` | Passed: no errors |
| Pint on scoped implementation paths | Passed |
| Scoped implementation diff checks | Passed |
| Baseline inspection | 28 findings |
| `composer audit --locked --format=json` | Four advisories across two packages: 0 critical, 0 high, 1 medium (PsySH), 3 low (Symfony YAML) |
| `php artisan route:list --path=stock --json` | Confirmed stock routes use `auth`, `valid.role`, and `rol:administrador` |
| Historical full suite after report-money/legacy stock work | Passed: 452 tests, 2267 assertions |
| `git diff --check -- composer.lock` | Passed |
| Native high-risk review | `review-fcad4a3aadb105e0` approved the corrected `composer.lock` candidate; receipt exists |
| Native report-money review | `review-5b8f34432bb9d766` approved the completed report-money work unit |
| Native stock-consolidation review | `review-35fbc358416abded` approved the corrected legacy stock-path work unit |
| Focused stock authorization/Unicode verification | Passed: 35 tests, 141 assertions; production PHPStan clean; explicit Pint over five files and scoped diff checks passed |
| Native stock authorization/Unicode review | `review-8f67966afa8fabf3` approved the 195-authored-line work unit with zero findings |
| Focused bounded admin CSV tests | Passed: 3 tests, 35 assertions |
| Relevant bounded admin CSV combined suite | Passed: 72 tests, 413 assertions |
| Bounded admin CSV implementation checks | Pint passed over five owned paths; scoped PHPStan passed over three production controllers; scoped diff checks passed |
| Native bounded admin CSV review | `review-d9feb10b8721f04a`; 458 authored changed lines; high tier/full 4R; terminal approved with no blocking findings; receipt materialized |
| Focused report production-hardening tests | Passed: 39 tests, 218 assertions |
| Full Pest suite | Passed: 494 tests, 2505 assertions |
| Report production-hardening implementation gates | PHPStan passed over 105 files; Pint passed over 207 files; JavaScript passed 7 tests; isolated Vite build passed |
| Native report production-hardening review | `review-52a17874423871e1` approved; post-apply receipt validation allowed delivery |
| Historical deadlock slice evidence | 50 focused tests/304 assertions; full suite then 368 tests/1819 assertions |

The dependency-security rows belong to the completed lockfile work unit. The stock authorization/Unicode work unit contributed its focused 35-test/141-assertion result, production PHPStan, Pint/scoped diff checks, and native review receipt. The bounded admin CSV work unit contributed its focused 3-test/35-assertion result, relevant combined 72-test/413-assertion suite, scoped implementation gates, and native review receipt. The newer integrated report production-hardening verification is the current full-suite snapshot and includes the isolated Vite build. This documentation-only update did not rerun application quality gates or perform deployment-environment checks.

## Risks and Limitations

- The remaining one medium and three low dependency advisories were verified against the lockfile, but exploit reachability was not tested individually.
- SQLite tests do not prove real MySQL/InnoDB deadlock behavior or production query plans.
- No production data volume, latency, memory, slow-query log, or incident telemetry was available; export/report scalability findings are code-path risks, not measured outages.
- OFFSET-based admin CSV chunks bound memory but do not provide snapshot consistency under concurrent inserts, deletes, or sort-key changes; a future snapshot/keyset strategy may be considered if that consistency is required.
- The PDF preflight count and subsequent fetch are not a database snapshot; a concurrent insert can marginally exceed the row cap. Snapshot/queue architecture is deferred as a non-blocking residual risk.
- CodeGraph coverage hints are useful for navigation but are not treated as proof of missing tests; named test files and executable quality gates take precedence.
- No deployment-environment checks were performed for `APP_DEBUG`, secrets, database connectivity, configuration caching, or backup/migration/rollback readiness.
- PsySH remains at v0.12.10 pending a compatible v0.12.24 security update decision; until patched, Tinker/PsySH must not be run from attacker-writable directories.
- This documentation update did not rerun application tests, builds, load tests, mutation tests, or coverage instrumentation; the latest results shown above come from the completed implementation work units.

## Next Recommended Slice

Treat deployment readiness as the next operational follow-up: verify `APP_DEBUG=false`, production secrets/database configuration and config caching, and a documented backup/migration/rollback procedure. Decide the compatible PsySH v0.12.10 to v0.12.24 security update, retaining the attacker-writable-directory restriction until patched. Keep worker export changes, broader authorization/controller cleanup, baseline reduction, snapshot/queue report architecture, and conditional MySQL-specific proof as separate non-blocking work.

Keep API Resources, Sanctum, Redis/Horizon, dynamic permission packages, repositories, and DTO expansion conditional on a demonstrated consumer or complexity threshold.
