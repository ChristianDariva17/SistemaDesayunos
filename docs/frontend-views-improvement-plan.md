# Frontend Views Improvement Plan

This document captures the current state of the Laravel Blade views and the recommended path to improve maintainability, performance, accessibility, UI usefulness, and rendering speed.

## Quick Path

1. Keep Laravel Blade as the main frontend architecture; do not switch to a SPA unless the product becomes interaction-heavy enough to justify it.
2. Centralize repeated admin UI patterns into Blade components and shared Vite assets.
3. Keep AJAX partial refresh for admin lists and extend it carefully to other dense screens.
4. Add lightweight libraries only when they solve a specific workflow: Alpine.js for small UI state and Chart.js/ApexCharts for decision-oriented dashboards.
5. Keep future UI work slice-based and backed by focused rendering, feature, or JavaScript tests.

## Current Status

Frontend Slices 1 through 7 are complete through searchable order-flow selects, report/chart loading cleanup is complete, progressive AJAX filters were added for admin productos/clientes/pedidos, and unsafe sticky side-panel behavior was corrected on the affected admin pages.

Completed baseline:

- Layout data cleanup, shared layouts, Blade components, and Vite-managed shared assets.
- Accessibility baseline: skip links/main landmarks, accessible names for icon-only controls, alert/live-region semantics, labeled dynamic order quantity controls, and focused rendering tests.
- Table/modal performance: reusable admin product modals, eager-loaded order listings, responsive card-table behavior, and focused regression coverage.
- Functional enhancement: Tom Select searchable product/user selects in admin and worker order flows, with idempotent dynamic-row initialization and focused rendering tests.
- Report/chart loading cleanup: Chart.js stays out of shared app assets; report pages currently rely on static/PDF-safe chart markup and do not render Chart.js hooks.
- Progressive enhancement: admin list filtering/pagination can update partial content without full-page reloads.
- Sticky side-panel fixes: side cards are constrained to desktop-safe sticky behavior and should not cover page content while scrolling.
- Product image lifecycle: create, replace, and delete operations use transactional compensation and focused regression coverage without adding a media-library dependency.

## Latest Frontend Assessment

The frontend is a good fit for a **Laravel Blade + Vite + progressive enhancement** approach. The current system does not need React, Vue, Inertia, or a full SPA layer yet.

The highest-value improvement is not changing frameworks. It is making the current Blade frontend more reusable, consistent, accessible, and measurable.

| Area | Assessment | Next action |
|------|------------|-------------|
| Frontend architecture | Blade-first with progressive JavaScript is appropriate | Keep this direction |
| Interactivity | AJAX filters are useful and should stay | Extract repeatable patterns |
| Visual consistency | Functional but uneven across admin pages | Standardize shared components |
| Performance | Acceptable, but inline/per-page assets create future cost | Move repeated CSS/JS to Vite |
| Accessibility | Good basics exist, but needs full pass on dense screens | Audit filters, tables, modals, buttons |
| Dashboards | Useful but can become more actionable | Add focused charts only where they help decisions |

## Executive Summary

The current UI is functional and has a clear domain split between admin, worker, auth, profile, and components. The completed foundation reduced the original layout, asset, component, and rendering duplication; the remaining work is concentrated in list scalability, image delivery, and incremental visual consistency.

The main risk is no longer the absence of a shared frontend foundation. It is allowing new screens to bypass the established Blade components, Vite assets, progressive-enhancement patterns, and accessibility guardrails.

## View Map

| Area | Path | Notes |
|------|------|-------|
| Main layout | `resources/views/layouts/app.blade.php` | Large shared layout with navigation, alerts, inline styles/scripts, and layout-level data logic. |
| Guest/auth layout | `resources/views/layouts/guest.blade.php` | Simpler layout using Vite. |
| Navigation | `resources/views/layouts/navigation.blade.php` | Breeze-style navigation. |
| Admin views | `resources/views/admin/**` | Dashboards, products, orders, clients, employees, reports. |
| Worker views | `resources/views/trabajador/**` | Worker dashboards, products, and order flows. |
| Auth views | `resources/views/auth/**` | Auth pages; avoid touching unrelated auth Blade files unless specifically working on auth. |
| Components | `resources/views/components/**` | Useful foundation, but currently underused for repeated UI patterns. |

## Strengths

- Clear separation by user area: `admin`, `trabajador`, `auth`, `profile`.
- Existing use of Blade components in parts of the app.
- Pagination exists in important listing pages.
- Empty states, badges, alerts, KPI cards, tables, and modal patterns are already present.
- Order flows show useful business guidance: totals, product quantities, and contextual summaries.

## Findings and Remaining Issues

The first five findings below describe the original baseline and are retained as architectural context. Their recommended fixes have been completed unless a section explicitly says otherwise.

### Resolved: Database Queries in Blade

Some layout-level values are calculated directly in Blade, especially in `resources/views/layouts/app.blade.php`.

Examples of risky patterns:

- Low-stock product counts from the layout.
- Pending order counts from the layout.
- Notification data calculated while rendering the view.

Why this matters:

- Every page using the layout pays for these queries.
- It hides data dependencies from controllers/tests.
- It makes caching and performance tuning harder.

Recommended fix:

- Move this data into a view composer, a small service, or controller-provided view data.
- Consider short-lived caching for global navigation counters if they are expensive.

Possible structure:

```php
View::composer('layouts.app', function ($view) {
    $view->with('navigationStats', app(NavigationStatsService::class)->get());
});
```

### Resolved: Duplicated Layouts and Full-Page Shells

Several admin and worker views define full HTML documents, CDNs, styles, and scripts independently, even though shared layouts exist.

Affected examples:

- `resources/views/layouts/app.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/trabajador/dashboard.blade.php`
- Several files under `resources/views/trabajador/**`

Why this matters:

- Navigation, styles, scripts, and responsive fixes must be updated in multiple places.
- Inconsistencies become likely.
- The browser receives repeated CSS/JS patterns.

Recommended fix:

```txt
resources/views/layouts/app.blade.php
resources/views/layouts/admin.blade.php
resources/views/layouts/trabajador.blade.php
resources/views/partials/sidebar.blade.php
resources/views/partials/navbar.blade.php
resources/views/components/alert.blade.php
resources/views/components/page-header.blade.php
resources/views/components/stat-card.blade.php
resources/views/components/table-actions.blade.php
```

### Resolved: Inline CSS and JavaScript

Many views include large inline `<style>` and `<script>` blocks.

Why this matters:

- Inline code is harder to reuse and test.
- Browser caching is less effective.
- Repeated behavior becomes inconsistent across pages.

Recommended fix:

- Move shared CSS to `resources/css/app.css`.
- Move shared JavaScript to `resources/js/app.js`.
- Keep only truly page-specific initialization in Blade.
- Use Vite as the primary asset pipeline.

### Resolved: Repeated CDN Dependencies

Several views load Bootstrap, Font Awesome, Animate.css, SweetAlert2, jQuery, or similar dependencies independently.

Why this matters:

- Duplicated network cost.
- Harder dependency control.
- More global JavaScript and CSS conflicts.

Recommended fix:

- Centralize common dependencies in Vite.
- Load page-specific libraries only on pages that need them.
- Prefer Alpine.js or vanilla JS over jQuery for small interactions.

### Baseline Complete: Accessibility Gaps

Common issues found:

- Icon-only buttons without reliable accessible names.
- Dropdown controls implemented with non-button elements.
- Missing or inconsistent `aria-label` usage.
- Missing `<main>` landmark in shared layouts.
- No skip link for keyboard users.
- Dynamic totals and notifications without consistent `aria-live` behavior.
- Some close buttons or dynamic controls missing accessible labels.

Examples to review:

- Sidebar toggle button.
- Notification bell.
- User dropdown trigger.
- Product/order table action buttons.
- Plus/minus quantity controls in order forms.
- Dynamic totals in order creation.

Recommended fix:

```blade
<button type="button" aria-label="Edit product">
    <i class="fas fa-edit" aria-hidden="true"></i>
</button>
```

Also add:

- `<main id="main-content">` around page content.
- A skip link before repeated navigation.
- `role="alert"` or `aria-live="polite"` for status messages.
- Visible focus styles for keyboard users.

### Medium: Placeholder Links and Dead UI

Some controls use `href="#"` for actions that are not implemented yet.

Examples:

- Export buttons such as Excel/PDF/CSV in some screens.

Why this matters:

- Users think the feature exists.
- Reviewers cannot tell whether the feature is pending or broken.

Recommended fix:

- Hide actions until implemented, or connect them to real routes.
- If an action is intentionally disabled, render it as disabled with a clear reason.

### Medium: Per-Row Modals Inflate the DOM

Some listing pages generate modals inside each row, especially product image or stock modals.

Why this matters:

- Large pages produce many hidden modal nodes.
- DOM size grows quickly with pagination size.
- Mobile performance and memory usage can degrade.

Recommended fix:

- Use one reusable modal and fill it with `data-*` attributes or fetched content.
- Use dedicated pages for heavy actions when needed.

## Performance Improvement Plan

### 1. Remove Queries From Views

Move layout counters, notifications, and expensive calculations into services or view composers.

Acceptance checklist:

- [x] `layouts/app.blade.php` does not call Eloquent models directly for layout counters.
- [x] Navigation counters are passed as view data through `App\View\Composers\AppLayoutComposer`.
- [x] Counter loading is centralized in `App\Services\NavigationStatsService`; caching was intentionally deferred because the counts are cheap and should remain immediately accurate.

### 2. Normalize Layouts

Create shared shells for admin and worker pages.

Acceptance checklist:

- [x] Admin dashboard extends a shared admin layout.
- [x] Worker dashboard extends a shared worker layout.
- [x] Navbar/sidebar are partials or components.
- [x] No repeated full HTML document shells in child views.

### 3. Move CSS/JS to Vite

Centralize reusable styles and scripts.

Acceptance checklist:

- [x] Shared CSS lives in `resources/css/app.css` or imported modules.
- [x] Shared JS lives in `resources/js/app.js` or page modules.
- [x] CDNs are removed or limited to intentional exceptions.
- [x] Chart/report scripts are loaded only where needed or deferred until the report workflow is improved.

### 4. Optimize Tables and Lists

Keep server-side pagination for large data.

Acceptance checklist:

- [ ] Controllers eager-load relationships used by views.
- [ ] Views avoid relationship queries inside loops.
- [ ] Large tables have server-side filters/search.
- [ ] Mobile layouts avoid unreadable dense tables where possible.

### 5. Optimize Images

Improve product/public image loading.

Acceptance checklist:

- [ ] Product images use explicit width/height where possible.
- [ ] Below-the-fold images use `loading="lazy"`.
- [ ] Images are compressed and preferably served as WebP/AVIF.
- [ ] External image dependencies are reviewed.

## Accessibility Improvement Plan

### Priority Fixes

- [x] Add accessible names to icon-only buttons.
- [x] Use native `<button>` elements for interactive controls.
- [x] Add `<main id="main-content">` to layouts.
- [x] Add a skip link to bypass repeated navigation.
- [x] Standardize alert/toast rendering with `role="alert"` or `aria-live`.
- [x] Add labels or `aria-label` to dynamic quantity inputs.
- [x] Ensure destructive actions have clear labels and confirmation text.
- [x] Preserve visible keyboard focus states.

### Example Skip Link

```blade
<a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>

<main id="main-content">
    @yield('content')
</main>
```

## SEO Guidance

SEO should be prioritized only for public pages.

Important areas:

- `resources/views/welcome.blade.php`
- Public landing pages.
- Public product or business pages if added later.

Do not over-optimize authenticated admin/worker pages for SEO. Those should usually be private and may even need `noindex` depending on deployment.

Public-page checklist:

- [ ] Unique `<title>`.
- [ ] Useful meta description.
- [ ] Canonical URL where appropriate.
- [ ] Open Graph metadata for sharing.
- [ ] Optimized public images.
- [ ] Logical heading hierarchy.

## Recommended Libraries and Tools

| Library / Tool | Use | Tradeoff |
|----------------|-----|----------|
| Blade components | Buttons, cards, alerts, badges, table actions, empty states | No external dependency, but requires refactor work. |
| Alpine.js | Sidebar, dropdowns, tabs, modals, toggles, filter panels, small reactive interactions | Good fit for Blade; avoid mixing too much with jQuery or turning Blade into a pseudo-SPA. |
| Tom Select or Choices.js | Searchable product/client selects | Useful when lists are large; unnecessary for small datasets. |
| Chart.js | Simple dashboards and reports | Lightweight enough for focused charts; load only on pages that render charts. |
| ApexCharts | More visual, interactive dashboards | Heavier than Chart.js; use only if richer dashboard UX is needed. |
| Blade Icons | Replace or reduce Font Awesome payload | Requires choosing and standardizing an icon set. |
| Spatie Laravel Medialibrary | Future image conversions, responsive variants, or a media catalog | Current transactional image handling does not require it; add only if those capabilities become necessary. |
| HTMX | Partial updates, filters, status changes without SPA complexity | Introduce after cleaning layouts/components. |
| DataTables / simple-datatables | Rich table interactions and exports | Must decide client-side vs server-side; can conflict with Laravel pagination if used carelessly. |
| IntersectionObserver API | Lazy-load charts, dashboard blocks, images, or secondary panels | Native browser API; requires careful fallback only for older browsers if needed. |
| Lighthouse | Performance, accessibility, SEO, and best-practice audits | Measures symptoms; still requires developer judgment to fix root causes. |
| axe-core | Automated accessibility checks | Finds many issues, but does not replace keyboard/screen-reader manual testing. |
| SweetAlert2 | High-risk confirmations and readable alerts | Use sparingly; routine actions should not all become modal interruptions. |

## Useful View Ideas

### Dashboard

- Add compact KPI cards for today's orders, pending orders, low stock, revenue, and cancelled orders.
- Add focused charts only where they help decisions: orders by status, sales by day, top products, and low-stock trend.
- Lazy-load chart blocks so the initial dashboard stays fast.

### Products

- Keep server-side pagination and AJAX filtering.
- Add clearer stock badges: low, out, available.
- Add image thumbnails only after image dimensions, lazy loading, and delivery formats are standardized.
- Consider bulk actions only when the workflow needs them.

### Clients

- Keep quick search and AJAX pagination.
- Add visible status filters and last-order indicators if useful for daily work.
- Preserve the `Clientes varios` workflow without overcomplicating client selection.

### Pedidos

- Keep the current AJAX filtering direction.
- Improve status badges and action visibility by current state.
- Add a compact order timeline only if users need to understand order history quickly.
- Avoid changing business status rules unless explicitly requested.

### Reports

- Keep static/PDF-safe report output as the baseline.
- Add Chart.js or ApexCharts only to rendered report pages that actually expose chart data.
- Load report charts page-by-page, not globally in `app.js`.

## What Not To Add Yet

- Do not add React, Vue, Inertia, or a full SPA layer yet.
- Do not add more animation libraries.
- Do not add another toast library before standardizing alerts.
- Do not add DataTables until table strategy is clear.

Reason: the project needs a cleaner Blade foundation first. Adding heavier tools now would multiply the existing duplication.

## Suggested Implementation Slices

### Slice 1: Layout Data Cleanup

Goal: remove queries and business data loading from Blade layouts.

- [x] Move layout counters into a service/view composer.
- [x] Add focused tests if counters affect behavior.
- [x] Keep visual output unchanged.

### Slice 2: Shared Layouts

Goal: reduce duplicated page shells.

- [x] Create admin and worker layouts.
- [x] Extract navbar/sidebar partials.
- [x] Convert dashboards to extend the shared layouts.

### Slice 3: Blade Components

Goal: standardize repeated UI.

- [x] Create components for alerts, page headers, stat cards, badges, table actions, and empty states.
- [x] Replace duplicated markup gradually in admin/worker dashboard patterns and admin product table actions/session alerts.
- [x] Keep visual output and backend behavior unchanged; component extraction only.

### Slice 4: Asset Pipeline Cleanup

Goal: reduce duplicated CSS/JS and centralize assets.

- [x] Move shared CSS/JS into Vite.
- [x] Remove repeated CDNs from shared admin/worker layouts.
- [x] Load page-specific scripts conditionally through existing Blade stacks.

### Slice 5: Accessibility Baseline

Goal: make the interface more usable with keyboard and assistive technology.

- [x] Add accessible names to icon controls.
- [x] Add landmarks and skip link.
- [x] Standardize alerts and dynamic status messages.
- [x] Fix labels on dynamic form controls.

Completed in Slice 5:

- [x] Shared admin and worker layouts expose skip links and `main-content` landmarks.
- [x] Admin navigation icon-only controls and product table action controls have accessible names.
- [x] Shared alerts and order totals expose alert/live-region semantics.
- [x] Admin and worker order quantity controls expose generated accessible labels.
- [x] Shared focus-visible styles preserve keyboard focus indication.

### Slice 6: Table and Modal Performance

Goal: reduce DOM weight and improve list scalability.

- [x] Replace per-row admin product image and stock modals with one reusable modal per behavior, populated from trigger `data-*` attributes.
- [x] Verify eager loading for relationships shown in loops; admin/worker order indexes now eager-load rendered customer/employee fields and use `withCount('productos')` for product totals.
- [x] Improve mobile table/card behavior for admin product/order and worker product/order listing tables with shared responsive card-table CSS.
- [x] Add focused regression coverage for reusable product modals and order product counts.

### Slice 7: Functional Enhancements

Goal: add libraries where they solve real product problems.

- [x] Added Tom Select-powered searchable selects for order-flow client and product selectors in admin create/edit orders and worker create/edit order views, using explicit `data-enhance="searchable-select"` hooks and idempotent initialization for dynamically added product rows. Worker edit route coverage is intentionally absent because `trabajador.pedidos.edit` is not registered; rendered worker coverage currently exercises the create route instead of scanning Blade source.
- [x] Keep Chart.js out of shared report assets until a rendered page exposes real chart hooks/config.
- [x] Standardize transactional product image create, replace, and delete handling with focused regression coverage.
- [ ] Add Medialibrary only if responsive conversions, thumbnails, or a broader media catalog become product requirements.

## Review Guardrails

- Keep changes surgical and slice-based.
- Do not modify unrelated auth Blade files unless the slice explicitly targets auth.
- Preserve backend behavior while refactoring views.
- Prefer server-side pagination/filtering for large datasets.
- Add frontend libraries only when the specific workflow justifies them.

## Next Step

Frontend Slices 1 through 7, report/chart loading cleanup, progressive admin filters, sticky-panel corrections, and transactional product image handling are complete. The next recommended work is **table/list scalability and image delivery optimization**: finish the unchecked eager-loading and server-side filtering checks, then add explicit image dimensions, lazy loading, and modern formats where measurements justify them.
