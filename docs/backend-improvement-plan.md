# Backend Improvement Plan

This document tracks the backend and database improvement roadmap for SistemaDesayunos. Sections 1 through 8 have been implemented: authorization, order write-flow actions, monetary correctness, concurrency/rollback coverage, observability, reporting scalability, database integrity follow-up, and domain boundary cleanup.

## Quick Path

1. Treat Sections 1 through 8 as completed baseline work.
2. Continue new backend work from this completed baseline.
3. Keep new business logic in clear module/action/service homes instead of adding orchestration to controllers or models.

## Progress Snapshot

| Section | Area | Status |
|---|---|---|
| 1 | Authorization Hardening | Completed |
| 2 | Order Use Case Extraction | Completed |
| 3 | Monetary Data Correctness | Completed |
| 4 | Concurrency and Rollback Testing | Completed |
| 5 | Observability and Business Events | Completed |
| 6 | Reporting and Dashboard Scalability | Completed |
| 7 | Database Integrity Follow-up | Completed |
| 8 | Domain Boundary Cleanup | Completed |

## Priority Summary

| Priority | Area | Goal |
|---|---|---|
| Critical | Authorization | Ensure every sensitive action is checked by policy, not only by routes or roles. |
| Critical | Money storage | Remove floating-point risk from prices, totals, and payment calculations. |
| High | Order architecture | Keep controllers and models thinner by moving use cases into Actions. |
| High | Concurrency | Prove stock and cash flows remain correct under simultaneous operations. |
| Medium | Observability | Make production incidents easier to diagnose. |
| Medium | Reporting | Avoid slow dashboards as data grows. |
| Later | Domain modules | Separate inventory, orders, audit, and reporting boundaries more clearly. |

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

- [x] Critical Form Requests no longer authorize blindly.
- [x] Policies exist for core business models.
- [x] Controller/Action flows enforce policies.
- [x] Feature tests cover allowed and denied access.

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

- [x] Controllers only validate, authorize, call Actions, and return responses.
- [x] `Pedido` model keeps relationships, casts, scopes, and small domain helpers only.
- [x] Stock mutations happen inside explicit transactions.
- [x] Existing tests still pass after refactor.

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
- [x] Tests cover decimal totals and rounding boundaries.
- [x] Historical prices remain stable after product price changes.
- [x] Daily closure totals match completed orders exactly.

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

- [x] Tests cover concurrent stock pressure.
- [x] Tests cover transaction rollback behavior.
- [x] Tests cover duplicate closure race behavior.
- [x] Tests prove audit failures do not corrupt business operations.

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
- [x] Report filters use indexed columns.
- [x] Expensive aggregate queries are cached or precomputed when necessary.

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
- [x] Cross-module writes happen through Actions, not random model calls.
- [x] Tests describe business behavior, not implementation details.

## Suggested Implementation Order

1. [x] Policies and authorization hardening.
2. [x] Money migration to `DECIMAL(10,2)`.
3. [x] Pedido write-flow Actions refactor.
4. [x] Concurrency and rollback tests.
5. [x] Observability with events and structured logs.
6. [x] Report query optimization.
7. [x] Database integrity follow-up.
8. [x] Domain boundary cleanup.

## Recommended Next Slice

Backend improvement plan Sections 1 through 8 are complete.

Reason: orders now coordinate inventory stock mutations through explicit Inventory Actions, preserving behavior while giving new cross-module business logic a clear module home.
