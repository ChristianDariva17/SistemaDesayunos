# Backend Improvement Plan

This document tracks the backend and database improvement roadmap for SistemaDesayunos. The 2026-07-20 evidence review confirmed substantial implementation across Sections 1 through 8, completion of the dependency high/critical security gate, decimal-safe money handling in the covered admin reports, consolidation of both legacy product stock paths, contextual authorization plus Unicode reason normalization for dedicated stock requests, bounded iteration for the three routed admin CSV exports, and approved production hardening for all four admin PDF reports. Worker export bounding, broader authorization/controller cleanup, production-equivalent concurrency verification, deployment-environment readiness, and lower-severity dependency maintenance remain open where noted. The prioritized current assessment is in [`backend-architecture-feedback.md`](backend-architecture-feedback.md).

## Quick Path

1. Preserve the completed report-money, report production-hardening, stock-path, and dedicated stock-authorization/Unicode-normalization boundaries and their regression coverage.
2. Complete deployment-environment checks and the PsySH security-update decision without treating them as current code blockers.
3. Track worker export, broader controller/query cleanup, and conditional snapshot/queue architecture independently.

## Current Status

The main pedido, inventory, cash, authorization, reporting, integrity, and observability foundations are published. Dedicated stock entry and adjustment now retain administrator route middleware while adding contextual `ProductoPolicy::updateStock` checks in their Form Requests. Invalid product IDs remain validation-owned, and dedicated adjustment reasons normalize Unicode separator/whitespace and FEFF before required/max validation. This closes the scoped stock gap without claiming that authorization is complete across the application. Decimal storage, core pedido/cash arithmetic, and covered admin report money calculations are decimal-safe. Both legacy `ProductoController` stock paths now delegate to `RegisterStockAdjustmentAction`, including race-safe active-reservation bounds. The routed admin product, client, and order CSV exports now retrieve data inside streamed callbacks, use configurable fixed-size chunks with stable ID tie-break ordering, preserve existing filters and CSV contracts, and eager-load pedido relationships per chunk. All four admin PDFs now use generic public `Throwable` error boundaries with structured internal logging and configurable synchronous limits defaulting to 250 rows and 31 days; oversized requests are rejected during validation/preflight. The sales-by-client PDF is restored, discoverable, decimal-safe, and covered for access, data, and rendering. The worker order CSV, broader controller error-boundary cleanup, and deployment-environment checks remain separate follow-ups. The dependency high/critical security gate is complete; the locked graph now reports one medium PsySH advisory and three low Symfony YAML advisories.

## Progress Snapshot

| Section | Area | Status |
|---|---|---|
| 1 | Authorization Hardening | Partially completed application-wide; dedicated stock requests completed |
| 2 | Order Use Case Extraction | Completed |
| 3 | Monetary Data Correctness | Completed for current money storage and covered calculation flows |
| 4 | Concurrency and Rollback Testing | Implemented; MySQL concurrency proof pending if required |
| 5 | Observability and Business Events | Completed |
| 6 | Reporting and Dashboard Scalability | Admin CSV and four PDF routes bounded; worker CSV and snapshot/queue architecture remain follow-ups |
| 7 | Database Integrity Follow-up | Completed |
| 8 | Domain Boundary Cleanup | Legacy stock-path consolidation completed; broader cleanup remains incremental |

## Priority Summary

| Priority | Current scope | Goal |
|---|---|---|
| P0 | None verified | Reserve for an active exploit, confirmed data corruption, or an outage requiring immediate containment. |
| P1 | None pending | The dependency high/critical security gate is complete; lower-severity follow-up continues as routine maintenance. |
| P2 | Worker export follow-up and broader controller error boundaries | Continue verified operational hardening without reopening completed admin report work. |
| P3 | Controller/static cleanup and conditional MySQL/InnoDB concurrency proof | Improve maintainability incrementally; add the concurrency harness only when measured risk justifies it. |

## 0. Dependency Security

### Current Evidence

Before remediation, `composer audit --locked --format=json` reported 24 advisories across 13 packages: four high, 15 medium, four low, and one unspecified, with no critical advisories. The completed work unit updated 73 locked packages within existing `composer.json` constraints, left `composer.json` unchanged, and reduced the current audit to four advisories across two packages: zero critical, zero high, one medium in PsySH, and three low in Symfony YAML.

`composer prohibits php 8.2 --locked` reports no installed package incompatible with PHP 8.2. Native high-risk review lineage `review-fcad4a3aadb105e0` approved the corrected `composer.lock` candidate after detecting and correcting five Symfony 8.1 packages that required PHP >=8.4.1; the receipt exists.

### Acceptance Checklist

- [x] Update 73 locked packages within the existing Composer constraints without changing `composer.json`.
- [x] Review and approve the corrected lockfile through native high-risk lineage `review-fcad4a3aadb105e0`.
- [x] Resolve all critical and high advisories.
- [x] Pass `composer validate --strict`, `composer analyse -- --no-progress`, `composer test` (439 tests, 2206 assertions), and `git diff --check -- composer.lock`.
- [ ] Resolve or document the remaining one medium and three low advisories through ongoing dependency maintenance.

## 1. Authorization Hardening

### Problem

Route middleware and role checks help, but they are not enough. Sensitive operations should be authorized at the resource/action level.

### Recommended Work

- Create Policies for:
  - `Pedido`
  - `Producto`
  - `Cliente`
  - `Empleado`
  - `DailyCashClosure`
- Replace generic `authorize(): true` in Form Requests with contextual checks.
- Call `$this->authorize(...)` inside controllers or Actions where needed.
- Add tests proving unauthorized users cannot create, update, delete, close, or reactivate sensitive records.

### Acceptance Checklist

- [x] `StoreStockEntryRequest` and `StoreStockAdjustmentRequest` no longer authorize blindly; both enforce `ProductoPolicy::updateStock`.
- [x] Policies exist for core business models.
- [x] Dedicated stock entry and adjustment preserve `auth`, `valid.role`, and `rol:administrador` middleware and add contextual FormRequest/Policy enforcement.
- [ ] Complete application-wide authorization review; this stock work unit does not prove that every sensitive Controller/Action flow enforces a Policy.
- [x] Feature tests cover representative allowed and denied access, including administrator-only stock reporting.

The dedicated stock defense-in-depth gap is closed: route middleware remains the first boundary, while both Form Requests call the same administrator-only Policy ability. `ProductoPolicy::updateStock` supports class-level and existing instance-level checks without changing legacy aliases, and invalid `producto_id` values continue to reach validation rather than becoming premature 403 responses.

Dedicated adjustment reasons now normalize Unicode separator/whitespace and FEFF at the request boundary before `required`/`max` validation. Blank input becomes `null`; meaningful padded text persists normalized. Focused verification passed 35 tests with 141 assertions, production PHPStan, explicit Pint over all five changed files, and scoped diff checks. Native review `review-8f67966afa8fabf3` approved the 195-authored-line work unit with zero findings.

## 2. Order Use Case Extraction

### Problem

Order logic is one of the most important parts of the system. Keeping too much orchestration inside models or controllers makes future changes risky.

### Recommended Work

Create focused Actions:

- `CreatePedidoAction`
- `UpdatePedidoAction`
- `CancelPedidoAction`
- `DuplicatePedidoAction`
- `ReactivatePedidoAction`

Each Action should own one business use case and handle:

- transaction boundaries
- stock/reservation checks
- state transitions
- audit-safe updates
- domain exceptions

### Acceptance Checklist

- [x] Pedido mutation/write business orchestration is delegated to Actions.
- [x] `Pedido` model keeps relationships, casts, scopes, and small domain helpers only.
- [x] Stock mutations happen inside explicit transactions.
- [x] Existing tests still pass after refactor.

Pedido controllers still own query coordination, view preparation, exports, and HTTP response selection; those responsibilities are outside this completed write-flow extraction claim.

## 3. Monetary Data Correctness

### Problem

Money should not be stored or calculated with floating-point types. Floating-point rounding errors can create incorrect totals, reports, and closures.

### Recommended Work

Choose one strategy:

| Option | Pros | Tradeoff |
|---|---|---|
| `DECIMAL(10,2)` | Simple, readable, common in Laravel/MySQL apps. | Requires careful casting and validation. |
| Integer cents | Most robust for calculations. | Requires conversion at input/output boundaries. |

Recommended for this project: start with `DECIMAL(10,2)` unless the system will later support complex discounts, taxes, or multiple currencies.

Fields to review:

- product prices
- order item prices
- order totals
- daily cash closure totals
- payment/revenue summaries

### Acceptance Checklist

- [x] No business money columns use float/double.
- [x] Core pedido and daily closure arithmetic uses decimal-safe helpers.
- [x] Covered admin report calculations avoid `floatval` and float accumulation.
- [x] Tests cover core decimal totals and rounding boundaries.
- [x] Historical prices remain stable after product price changes.
- [x] Daily closure totals match completed orders exactly.

Completed evidence: admin report index, inventory, low-stock, sales, and the restored sales-by-client PDF use canonical decimal strings/integer cents, exact line/grand totals, half-up averages, gross-preserving 18% tax splits, and `0.00` empty values. Native review `review-5b8f34432bb9d766` approved the original money work unit; the later production-hardening review `review-52a17874423871e1` approved the restored sales-by-client route and its access, data, and rendering coverage.

## 4. Concurrency and Rollback Testing

### Problem

The system already uses transactions and locks in important areas. The next step is proving those guarantees under failure and simultaneous writes.

### Recommended Tests

- Two simultaneous order creations against the same product cannot oversell stock.
- Active reservations reduce available stock during order creation and reactivation.
- Reservation release/consume/cancel operations remain idempotent.
- Duplicate daily cash closures return a domain error, not a raw database exception.
- If an order operation fails halfway, stock and audit records do not become inconsistent.

### Acceptance Checklist

- [x] Canonical product lock ordering and bounded transaction retry are implemented.
- [x] Simulated concurrency failures and transaction rollback behavior are covered.
- [x] Tests cover duplicate closure race behavior.
- [x] Tests prove audit failures do not corrupt business operations.
- [ ] Add real concurrent MySQL/InnoDB coverage only if production risk requires it.

SQLite proves deterministic ordering, retry, and rollback behavior; it does not reproduce real InnoDB deadlocks.

## 5. Observability and Business Events

### Problem

Auditing records what changed, but production support also needs clear operational signals when something important happens or fails.

### Recommended Work

- Add domain events for:
  - order created
  - order cancelled
  - order completed
  - stock reserved
  - stock released
  - stock consumed
  - product price changed
  - daily cash closure created
- Add structured logs for failed business operations.
- Include useful context in logs:
  - model ID
  - user ID
  - business date
  - operation name
  - exception class/message

### Acceptance Checklist

- [x] Critical operations emit events or structured logs.
- [x] Logs do not expose sensitive data.
- [x] Failed operations are diagnosable without manually reconstructing every table.

## 6. Reporting and Dashboard Scalability

### Problem

Direct `count`, `sum`, and dashboard queries are fine with small data. As the app grows, repeated aggregation can become slow.

### Recommended Work

- Review dashboard/report queries for N+1 and repeated aggregation.
- Add indexes for common report filters:
  - date
  - status
  - payment method
  - product/category
- Consider cached summaries or materialized daily aggregates for heavy reports.

### Acceptance Checklist

- [x] Main dashboard queries are eager-loaded where needed.
- [x] Report filters have supporting indexes for the principal equality/range paths.
- [x] Dashboard aggregate queries are consolidated and cached for 60 seconds.
- [x] The three routed admin CSV exports for products, clients, and orders query inside streamed callbacks and use configurable fixed-size chunks with stable ID tie-break ordering.
- [x] All four admin PDF routes enforce configurable synchronous limits, defaulting to 250 source rows and 31 days, with validation/preflight rejection before full fetch or DomPDF rendering.
- [x] Restore and expose the sales-by-client PDF without weakening decimal-safe totals, with access, data, and rendering coverage.
- [x] Return generic public failures and emit structured internal `Throwable` logs for data and rendering failures on all four admin PDF routes.

The completed admin CSV work unit preserves existing filters and CSV column/value contracts, and the pedido export eager-loads cliente, empleado, and productos relationships per chunk. Focused new tests passed 3 tests with 35 assertions; the relevant combined suite passed 72 tests with 413 assertions. Pint passed over five owned paths, scoped PHPStan passed over the three production controllers, and scoped diff checks passed. Native review lineage `review-d9feb10b8721f04a` covered 458 authored changed lines at high tier/full 4R and reached terminal approval with no blocking findings; its receipt was materialized.

The fixed-size CSV implementation uses OFFSET-based chunks. This bounds memory but does not provide snapshot consistency under concurrent inserts, deletes, or sort-key changes; a future snapshot/keyset strategy may be considered if concurrent-export consistency becomes a requirement. The PDF preflight also has a count-then-fetch race: a concurrent insert after `COUNT` can marginally exceed the configured row cap. Snapshot or queue architecture would close that race but is deferred as a documented non-blocking residual risk.

## 7. Database Integrity Follow-up

### Problem

Recent migrations improved integrity significantly. The next step is consistency and maintainability.

### Recommended Work

- Extract duplicated migration helper logic for check constraints if more constraint migrations are added.
- Keep adding database-level protection for business invariants.
- Document which invariants are enforced by app validation, database constraints, or both. See [`database-integrity-invariants.md`](database-integrity-invariants.md).

### Acceptance Checklist

- [x] Repeated migration constraint helpers are centralized or intentionally accepted.
- [x] Every critical invariant has a clear enforcement layer.
- [x] Migration failures include actionable error messages when preflight checks fail.

## 8. Domain Boundary Cleanup

### Problem

The project can become harder to change if inventory, orders, audit, and reporting keep depending directly on each other without clear boundaries.

### Recommended Work

Organize business logic around modules or clear namespaces:

- Orders
- Inventory
- Products/Pricing
- Cash Closures
- Audit
- Reporting

This does not require a full rewrite. Start by placing new Actions and Services in clearer folders.

### Acceptance Checklist

- [x] New business logic has a clear module/home.
- [x] Both identified legacy product stock writes happen through `RegisterStockAdjustmentAction`; `ProductoController::update` and `actualizarStock` no longer own stock mutation or ledger orchestration.
- [x] Tests describe business behavior, not implementation details.

The canonical Action owns typed operations, its transaction and product row lock, `InventoryLimits` bounds, race-safe active-reservation floor enforcement, persistence, exactly-one adjustment ledger registration, no-op behavior, a typed result, and rollback. Product edit remains atomically coupled to non-stock updates and image compensation. Native review `review-35fbc358416abded` approved the corrected work unit.

## Suggested Implementation Order

1. [x] Complete the locked dependency high/critical security gate.
2. [x] Remove floating-point arithmetic from the covered admin report flows.
3. [x] Consolidate the legacy `ProductoController::update` and `actualizarStock` paths into the inventory Action boundary.
4. [x] Complete Policy/FormRequest authorization and Unicode reason normalization for dedicated stock entry and adjustment.
5. [x] Bound the three routed admin CSV exports while preserving filters, ordering, CSV contracts, and per-chunk pedido relationships.
6. [x] Pedido write-flow Actions, decimal storage, rollback coverage, observability, dashboard caching, and database integrity.
7. [x] Bound all four admin DomPDF report paths with configurable 250-row/31-day defaults and validation/preflight rejection.
8. [x] Restore the sales-by-client PDF and harden generic public/structured internal error boundaries across all four admin reports.
9. [ ] Continue broad authorization/controller cleanup and baseline reduction in small slices.
10. [ ] Add MySQL concurrency coverage only if justified by deployment risk.
11. [ ] Continue tracking the one medium and three low dependency advisories without weakening the completed security gate; decide the compatible PsySH v0.12.10 to v0.12.24 update.
12. [ ] Verify `APP_DEBUG=false`, production secrets/database configuration, configuration caching, and the backup/migration/rollback procedure in the deployment environment.

## Next Recommended Work

The next work is deployment follow-up rather than a blocker in the current code: verify `APP_DEBUG=false`, production secrets/database configuration and config caching, and a documented backup/migration/rollback procedure. Decide the compatible PsySH v0.12.10 to v0.12.24 security update; until patched, do not run Tinker/PsySH from attacker-writable directories. No deployment-environment checks are claimed by this document update.

The integrated report production-hardening verification passed 39 focused tests with 218 assertions, the full 494-test/2505-assertion suite, PHPStan over 105 files, Pint over 207 files, seven JavaScript tests, and an isolated Vite build. Native review `review-52a17874423871e1` approved the work, and post-apply receipt validation allowed delivery. Continue separately with worker export changes, broad authorization/controller cleanup, worker cliente query/stat reuse, producto statistics extraction, incremental reduction of the 28-finding PHPStan baseline, conditional MySQL-specific proof, lower-severity dependency maintenance, and the documented count-then-fetch residual race. Do not introduce an API, repositories, DTOs, queues, or dynamic permission packages without a concrete consumer or measured complexity threshold; snapshot/queue report architecture remains deferred.
