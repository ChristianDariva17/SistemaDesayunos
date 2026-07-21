# Stock Movements: Current State

SistemaDesayunos has a dedicated stock movement ledger integrated with order stock changes, manual inventory operations, product editing, and administrator reporting.

This document describes the current implementation. Historical commits explain how the ledger evolved, but they are not used as current verification evidence.

## Implemented Schema

### `stock_movimientos`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `producto_id` | unsigned bigint | Required; references `productos.id` and restricts deletion |
| `pedido_id` | unsigned bigint nullable | Optional order reference; set to null when the order is deleted |
| `pedido_numero` | string nullable | Snapshot of the order number so the ledger retains its business reference |
| `user_id` | unsigned bigint nullable | Optional actor reference; set to null when the user is deleted |
| `tipo` | string | Enum-backed movement type |
| `cantidad` | unsigned integer | Positive quantity moved |
| `stock_anterior` | integer | Stock before the movement |
| `stock_nuevo` | integer | Stock after the movement |
| `motivo` | string nullable | Human-readable reason |
| `created_at` | timestamp | Movement date and time |
| `updated_at` | timestamp | Laravel timestamp |

The initial table indexes `producto_id`, `pedido_id`, `tipo`, `created_at`, and the composite pair `producto_id, created_at`. A later reporting migration adds the composite indexes `tipo, created_at` and `user_id, created_at`. Supported databases also enforce valid movement types, positive quantities, and non-negative previous and resulting stock.

## Movement Types

`StockMovimientoTipo` and `StockMovimiento::TIPOS` define these values:

| Type | Meaning | Current use |
|---|---|---|
| `entrada` | Stock increased | Dedicated administrator stock entry flow |
| `salida` | Stock decreased | Order creation, duplication, reactivation, and optional reservation consumption |
| `ajuste` | Manual correction | Dedicated adjustment, product editing, and legacy manual stock flows |
| `devolucion` | Stock returned | Defined, but not used by a production return workflow |
| `cancelacion` | Order stock restored | Order cancellation, deletion, and stock release flows |

## Domain Model and Registration

`App\Models\StockMovimiento` provides typed casts, fillable ledger fields, and these relationships:

- `producto()`
- `pedido()`
- `user()`

The inverse `stockMovimientos()` relationship exists on `Producto`, `Pedido`, and `User`.

`App\Actions\Stock\RegisterStockMovementAction` validates the persisted product, recognized movement type, positive quantity, and non-negative stock values before creating the ledger row. It stores both `pedido_id` and the `pedido_numero` snapshot when an order is present. It does not open its own transaction; callers own the transaction boundary.

The dedicated entry and adjustment Actions open a transaction, lock the product row with `lockForUpdate()`, update stock, and register the ledger row atomically:

- `RegisterStockEntryAction` records administrator `entrada` movements.
- `RegisterStockAdjustmentAction` records administrator `ajuste` movements.

Order creation, duplication, reactivation, cancellation, and deletion provide enclosing transactions around `ReserveProductoStockAction` and `ReleaseProductoStockAction`. Order creation locks products in canonical product-ID order and retries the outer transaction on concurrency errors. These flows record `salida` or `cancelacion` movements through the central registration Action.

`StockReservation::consume()` has its own transaction and may register a `salida` movement when a movement Action is supplied. Registration is optional at that API boundary; no production caller currently proves that integration is active.

Product editing routes stock changes through `RegisterStockAdjustmentAction`. Product creation still stores its submitted initial stock directly and does not create a ledger movement.

## Administrator Interface

The administrator area provides forms for stock entries and adjustments and a paginated movement report. These routes are protected by the `auth`, `valid.role`, and `rol:administrador` middleware group.

The stock-entry and stock-adjustment POST requests additionally authorize through `ProductoPolicy::updateStock()` in their FormRequests. The legacy product stock endpoint performs instance-level policy authorization explicitly. The dedicated GET forms and movement report rely on the administrator route middleware.

The movement report displays product, order number snapshot, actor, type, quantity, previous/new stock, reason, and timestamp. It supports validated filters for:

- product
- date range
- movement type
- user/actor

Product, client, and order CSV exports are streamed with a UTF-8 BOM and bounded chunk processing. Product export supports the configured low-stock filter. There is currently no dedicated stock movement export route.

## Reason Normalization

Entry and adjustment requests trim ASCII whitespace, Unicode separator characters, and `U+FEFF` from the edges of a reason before validation. An empty result becomes `null`. Entry reasons are optional, while adjustment reasons remain required after normalization.

The legacy product stock endpoint applies the same edge normalization. This behavior does not perform Unicode NFC/NFKC normalization or collapse internal whitespace.

## Low-Stock Semantics

Operational product listings, dashboards, the low-stock PDF, and filtered product CSV exports use each product's configured threshold. Alerts are enabled only when `stock_minimo > 0`, and a product is low on stock when `stock <= stock_minimo`.

The older `tiene_stock_bajo` accessor and `stockBajo()` scope still use the fixed `InventoryLimits::LOW_STOCK_THRESHOLD` value of 10. New code must not treat these two definitions as interchangeable.

## Verification Coverage

Focused repository tests cover the ledger and its integrations, including:

- model constants, casts, relationships, and registration validation
- policy-backed FormRequest authorization and administrator route protection
- ASCII and Unicode edge-whitespace normalization for reasons
- stock bounds, no-op adjustments, active-reservation floors, and stale model state
- order stock decreases and restoration movements
- transaction rollback without orphaned movement rows
- separate-process contention, deterministic lock ordering, and deadlock retry
- preservation and backfill of `pedido_numero`
- administrator-only report access
- report rendering and filters by product, type, actor, and date range
- configurable per-product low-stock thresholds
- streamed, BOM-prefixed, bounded product, client, and order CSV exports

The latest focused verification recorded on 2026-07-21 reported:

- 35 tests and 141 assertions for stock authorization and reason normalization
- 73 tests and 302 assertions for the atomic stock-adjustment core and product integration

These focused runs passed, and their bounded review receipts were approved. The full application suite was not rerun as part of this documentation update, so this document makes no current full-suite claim.

## Authorization Boundary

Administrator route middleware is the first authorization boundary. The stock-entry and stock-adjustment POST flows also use `ProductoPolicy::updateStock()` through their FormRequests. The legacy product stock endpoint authorizes the bound product instance directly. The dedicated GET forms and stock movement report do not perform an additional policy check and currently rely on administrator route middleware.

## Future Work

The following capabilities are not implemented as part of the current stock movement ledger:

- dedicated export for filtered stock movements
- purchase and supplier management with receiving flows
- product return workflow using `devolucion`
- predictive or movement-history-based stock alerts
- inventory reconciliation workflows and variance reports

Configurable low-stock reporting already exists independently of movement history. A future change should also consolidate the remaining fixed-threshold accessor and scope with the operational `stock_minimo` definition.

## Residual Risks

- `ReserveProductoStockAction` and `ReleaseProductoStockAction` rely on callers to provide an enclosing transaction. A future direct caller could weaken atomicity and row-lock effectiveness.
- Concurrency tests exercise SQLite contention, but they do not prove identical behavior under the production database engine and isolation level.
- `ReleaseProductoStockAction` does not enforce `InventoryLimits::MAX_STOCK_LEVEL`; overflow protection depends on the surrounding flow and database behavior.
- Stock reservation consumption accepts movement registration as an optional dependency and has no proven production caller.
- Product creation can establish non-zero stock without recording an initial ledger movement.
- The legacy product stock endpoint reports success for a zero-quantity or otherwise unchanged adjustment even though no movement is recorded.
- `StockAdjustmentController` does not translate an active-reservation floor failure into a validation response.
- CSV exports use bounded offset-based chunks, so concurrent inserts or updates can produce a non-snapshot result across chunk boundaries.
- The fixed threshold of 10 in the legacy accessor and scope can diverge from operational `stock_minimo` behavior.
