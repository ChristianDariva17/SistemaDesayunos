# Database Integrity Invariants

This document maps critical business invariants to their enforcement layer. Application validation gives users helpful feedback before writes; database constraints protect data when writes come from migrations, imports, jobs, or future code paths.

| Invariant | Application enforcement | Database enforcement |
|---|---|---|
| Product stock cannot be negative. | Stock mutation flows validate available stock before consuming or reserving it. | `productos_stock_non_negative_check` on `productos.stock`. |
| Product prices cannot be negative. Historical price rows should also remain non-negative through application validation. | Product validation and price history writes validate non-negative decimal prices. | `productos_precio_non_negative_check` protects current product prices; current database constraints do not enforce historical price rows. Money columns are migrated to `DECIMAL(10,2)`. |
| Pedido totals and tax cannot be negative. | Pedido actions calculate totals from validated line items. | `pedidos_total_non_negative_check`, `pedidos_impuesto_non_negative_check`; money columns are `DECIMAL(10,2)`. |
| Pedido status must be a known lifecycle state. | Pedido actions own status transitions. | `pedidos_estado_check`. |
| Pedido line quantities must be positive and money values non-negative. | Pedido creation/update validation requires positive quantities and calculates subtotals. | `pedido_producto_cantidad_positive_check`, `pedido_producto_precio_unitario_non_negative_check`, `pedido_producto_subtotal_non_negative_check`. |
| Stock movement type must be known and quantities/stock snapshots must be valid. | Stock movement services record known domain movement types. | `stock_movimientos_tipo_check`, `stock_movimientos_cantidad_positive_check`, `stock_movimientos_stock_anterior_non_negative_check`, `stock_movimientos_stock_nuevo_non_negative_check`. |
| Stock reservations must have a known status and positive quantity. | Reservation services transition active, released, consumed, and cancelled states. | `stock_reservations_status_check`, `stock_reservations_cantidad_positive_check`. |
| Daily cash closures cannot duplicate a business date and cannot contain negative counts or revenue. | Daily closure action validates duplicate closures and calculates totals from pedidos. | Unique `daily_cash_closures.business_date`; non-negative count/revenue check constraints. |
| Report/dashboard filters stay index-friendly as data grows. | Reporting services filter by indexed date/status/payment columns. | Reporting indexes added by scalability migrations. |

## Migration Safety

- Reusable check-constraint SQL is centralized in `App\Support\Database\CheckConstraints`.
- Preflight validation for prerequisite tables/columns is centralized in `App\Support\Database\MigrationPreflight`.
- Unsupported check-constraint drivers are treated as an intentional no-op in migrations so SQLite-based tests can still run, while direct helper failures include supported drivers and remediation guidance.
- Existing-data preflight failures list the invalid fields and instruct maintainers to fix the data before rerunning `php artisan migrate`.
