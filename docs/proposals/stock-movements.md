# Stock Movements Proposal

Add a dedicated stock movement ledger so every inventory change is traceable, auditable, and easier to debug.

## Problem

The system currently stores the current stock value on `productos`, but the database does not preserve a complete history of why stock changed.

That is enough for a simple catalog, but it becomes weak when the system needs to explain inventory differences, manual adjustments, cancelled orders, returns, purchases, or stock corrections.

## Goal

Create a `stock_movimientos` table that records every stock change with enough context to answer:

- Which product changed?
- Why did it change?
- Who caused the change?
- What was the previous stock?
- What is the new stock?
- Is the change linked to a pedido?

## Proposed Schema

### `stock_movimientos`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `producto_id` | foreign id | Required, references `productos.id` |
| `pedido_id` | foreign id nullable | Optional, references `pedidos.id` when the movement comes from an order |
| `user_id` | foreign id nullable | Optional, references `users.id` for the actor that caused the movement |
| `tipo` | string / enum-like value | `entrada`, `salida`, `ajuste`, `devolucion`, `cancelacion` |
| `cantidad` | unsigned integer | Amount moved |
| `stock_anterior` | integer | Stock before the movement |
| `stock_nuevo` | integer | Stock after the movement |
| `motivo` | string nullable | Human-readable reason, useful for manual adjustments |
| `created_at` | timestamp | Movement date/time |
| `updated_at` | timestamp | Usually not edited, but kept for Laravel consistency |

## Movement Types

| Type | Meaning | Example |
|---|---|---|
| `entrada` | Stock increased | New product units were added |
| `salida` | Stock decreased | A pedido reserved or consumed stock |
| `ajuste` | Manual correction | Admin fixes an inventory mismatch |
| `devolucion` | Stock returned | Product returned to available stock |
| `cancelacion` | Pedido cancellation | Cancelled pedido restores stock |

## Business Rules

- Every pedido creation that decreases stock should create one `salida` movement per product.
- Every pedido cancellation that restores stock should create one `cancelacion` movement per product.
- Manual stock edits should create an `ajuste` movement.
- Future purchase/receiving flows should create `entrada` movements.
- Stock should not change silently. If `productos.stock` changes, there should be a matching movement.

## Recommended Laravel Changes

### Migration

Create a migration for `stock_movimientos` with foreign keys to:

- `productos`
- `pedidos` nullable
- `users` nullable

Recommended indexes:

- `producto_id`
- `pedido_id`
- `tipo`
- `created_at`
- composite index: `producto_id, created_at`

### Model

Create `App\Models\StockMovimiento` with relationships:

- `producto()`
- `pedido()`
- `user()`

### Application Logic

Prefer centralizing movement creation in an action/service instead of scattering it through controllers.

Suggested action:

```txt
app/Actions/Stock/RegisterStockMovementAction.php
```

Responsibilities:

- Validate stock transition data.
- Store the movement.
- Keep the operation inside the same database transaction as the stock update.

## Acceptance Criteria

- Creating a pedido records stock `salida` movements.
- Cancelling a pedido records stock `cancelacion` movements.
- Manual stock changes record `ajuste` movements.
- Stock movement records include previous and new stock values.
- Tests prove that stock changes and movement records stay consistent.

## Suggested Tests

- Pedido creation decreases product stock and creates movement rows.
- Pedido cancellation restores product stock and creates movement rows.
- A failed pedido creation does not create stock movements.
- A manual product stock update creates an adjustment movement.
- Movement records preserve the correct `stock_anterior` and `stock_nuevo` values.

## Out of Scope for This First Slice

- Purchase management.
- Supplier management.
- Product returns UI.
- Full inventory dashboard.
- Exporting movement reports.

## Future Extensions

- Add stock movement report filters by product, date, type, and user.
- Add purchase receiving flow that creates `entrada` movements.
- Add low-stock alerts based on movement patterns.
- Add inventory reconciliation reports.
- Add permissions for who can perform manual stock adjustments.

## Implementation Order

1. Create migration and model.
2. Add relationships to `Producto`, `Pedido`, and `User` if needed.
3. Add stock movement registration action/service.
4. Integrate pedido creation and cancellation flows.
5. Integrate manual product stock adjustment flow.
6. Add feature tests.
7. Run migrations and full test suite.

## Verification Commands

The user should run these commands after implementation:

```powershell
php artisan migrate:fresh --seed
php artisan test
```
