# Frontend Views Audit and Improvement Roadmap

This is the authoritative frontend/views assessment for SistemaDesayunos as of **2026-07-21**. It updates the earlier implementation plan against current repository evidence. Historical work is retained below, but completion claims are limited to behavior that the current code and tests prove.

## Executive Assessment

**Decision:** continue with **Laravel Blade + Vite + progressive enhancement**. The current workflows are server-rendered CRUD, reports, and bounded dynamic order forms. There is no verified interaction, offline, client-state, or independent-API requirement that justifies React, Vue, Inertia, a SPA rewrite, or a broad Livewire migration.

The frontend has a useful tested foundation: shared admin and worker dashboard layouts, reusable Blade primitives, Vite entry points, progressive GET filters, server pagination, responsive list treatments, accessible navigation basics, and focused rendering tests. It is not, however, as fully consolidated as the previous roadmap stated. Six worker pages still own complete document shells, 31 Blade files contain `<style>` blocks, 24 contain `<script>` blocks, the login page still loads duplicated CDN assets, and the global bundle carries compatibility libraries on every Vite-backed page.

The dependency security gate is now clear. Composer remediation was committed as `7da3f53`, with zero Composer advisories, clean PHPStan analysis, and 494 passing tests with 2,505 assertions. Frontend dependency remediation was committed as `c229dbf` without changing `package.json`; full and production npm audits report zero vulnerabilities, all 7 JavaScript tests pass, and the Vite 7.3.6 production build completes with 136 modules. The remaining `glob@10.5.0` deprecation is a dev-only transitive dependency through `tailwindcss` and `sucrase`, not an active npm vulnerability. The `caniuse-lite` age warning is separate non-blocking maintenance.

### Scorecard

| Area | Score | Assessment | Priority |
|---|---:|---|---|
| Architecture and separation | 6/10 | Direction is appropriate; admin is mostly layout-based, but worker/auth shells and substantial page behavior remain duplicated | P1 |
| Component reuse and consistency | 6/10 | Useful primitives exist; adoption is uneven and two competing stat-card implementations remain | P2 |
| Asset pipeline and dependencies | 5/10 | Dependency audits are clear and Vite builds successfully, but global payload, CDN exceptions, mixed Tailwind generations, and unused dependencies need action | P2 |
| JavaScript robustness | 6/10 | Progressive fallbacks, abortable GET filters, and focused unit tests are good; legacy script execution and focus/status handling remain weak | P2 |
| Responsive/mobile UX | 7/10 | Major lists have tested card-table behavior and sticky panels fall back safely; worker shells and dense forms still need real-device validation | P2 |
| WCAG 2.2 accessibility | 5/10 | Skip links, landmarks, names, and visible focus exist; reduced motion, dialog semantics, errors, table semantics, AJAX focus, and timing remain incomplete | P1 |
| Forms and UI states | 6/10 | Labels, server validation, empty states, and submit feedback are common; error associations and consistent loading/recovery are not | P2 |
| Frontend security | 6/10 | Dependency audits are clear and Blade escaping is generally used, but executable auth-page CDN resources remain unresolved | P1 |
| Testability and maintenance | 6/10 | Strong focused Blade rendering coverage; only one JS unit file and no browser-level accessibility/responsive suite | P2 |

No P0 issue was verified. P0 is reserved for a confirmed active exploit, unusable critical workflow, or production outage.

## Audit Scope and Evidence

| Area | Current evidence |
|---|---|
| Views and layouts | `resources/views/**`, including layouts, partials, components, admin, worker, auth, profile, and report output |
| Assets | `resources/css/app.css`, `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/js/ajax-filter-helpers.js` |
| Build and lock | `package.json`, `package-lock.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` |
| HTTP/view boundary | `routes/web.php`, relevant controllers, `AppLayoutComposer`, navigation statistics, Policies, and Form Requests through CodeGraph/source inspection |
| Tests | Focused accessibility, asset, component, layout, searchable-select, table/modal, and JavaScript helper tests |
| Backend alignment | `docs/backend-architecture-feedback.md` for dependency security, authorization, API, queue, export, error, and observability boundaries only |

## Verified Strengths

- In the 2026-07-18 verification snapshot, `vite.config.js` had two explicit Laravel entries—`resources/css/app.css` and `resources/js/app.js`—and an isolated production build completed successfully.
- Shared Bootstrap, Font Awesome, Animate.css, SweetAlert2, Alpine, jQuery, and Tom Select assets are available through Vite for the main application surfaces.
- `resources/js/ajax-filter-helpers.js` limits generic interception to GET forms without CSRF or method-spoofing controls, preserving normal form behavior as the fallback.
- `resources/js/app.js` aborts superseded list requests, updates browser history, falls back to navigation on failure, and marks replaced regions `aria-busy` while loading.
- Admin product/client/order filters expose progressive AJAX hooks, while server routes remain canonical and usable without JavaScript.
- `resources/views/layouts/app.blade.php` and `resources/views/layouts/trabajador.blade.php` provide skip links and main landmarks; admin and worker navigation have accessible names.
- Product list modals are reused rather than rendered per row, and their current-state reset behavior is covered by a Node-backed feature test.
- Important lists use server pagination and tested responsive card-table metadata; report and list empty states are present.
- Order forms retain normal POST submission, labels, old input, server-side validation, live totals, and accessible names for dynamically generated quantity controls.
- Dashboard/layout data is supplied outside Blade through the existing composer/service boundary; no direct Eloquent calls were found in the shared layout.
- The 2026-07-18 focused verification passed 42 PHP tests with 390 assertions and 7 JavaScript tests.

## Prioritized Findings

### P0

No P0 issue was verified.

### P1: Required Before Further Frontend Expansion

| ID | Finding | Evidence | Required outcome |
|---|---|---|---|
| FE-01 | Resolved: frontend dependency security gate | Remediation commit `c229dbf` left `package.json` unchanged and produced zero vulnerabilities in full and production npm audits, 7 passing JavaScript tests, and a successful Vite 7.3.6 production build with 136 modules | Keep dependency changes isolated and rerun the relevant audits, tests, and build when the lockfile or manifest changes. Treat the dev-only transitive `glob@10.5.0` deprecation and `caniuse-lite` age warning as maintenance rather than active vulnerabilities |
| FE-02 | Layout and asset centralization is only partial | `resources/views/trabajador/productos/index.blade.php:1-34` is a complete HTML shell; the same pattern exists in worker product detail, client list, and order create/index/detail pages. Only the worker dashboard extends `layouts.trabajador`. Current source inspection found 31 Blade `<style>` blocks and 24 `<script>` blocks | Move each worker page onto `layouts.trabajador` without behavior changes; extract repeated shell CSS/JS incrementally. Mark the historical layout/asset slices complete only after no child page owns a duplicate document shell |
| FE-03 | The authentication surface bypasses Vite and executes third-party CDN assets without integrity metadata | `resources/views/auth/login.blade.php:27-37,694` loads Bootstrap, Font Awesome, Animate.css, Google Fonts, and Bootstrap JS externally; no `integrity` attributes are present. The versions also differ from the lockfile (`bootstrap` 5.3.0 CDN vs 5.3.8 installed, Font Awesome 6.5.1 vs 6.7.2) | Put login assets behind the existing Vite/guest-layout boundary or document a strict exception with pinned SRI and CSP compatibility. Preserve auth behavior and run focused login/password tests |
| FE-04 | WCAG 2.2 coverage stops at a baseline and misses key interaction contracts | No `prefers-reduced-motion` rule exists; `resources/views/components/modal.blade.php:17-77` has focus code but no `role="dialog"`, `aria-modal`, or label relationship; no rendered form uses `aria-invalid`/`aria-describedby`; AJAX pagination replacement can remove the focused link without restoring focus or announcing completion | Add reduced-motion handling, complete dialog semantics/focus restoration, associate field errors, and define AJAX focus/status behavior. Validate with keyboard, NVDA, 200% zoom, forced colors, reduced motion, and axe/Lighthouse on representative screens |

### P2: Required Maintainability and UX Work

| ID | Finding | Evidence | Required outcome |
|---|---|---|---|
| FE-05 | The global payload is broad and not page-oriented | The isolated 2026-07-18 Vite build emitted JavaScript at 380.72 kB/124.42 kB gzip and CSS entries at 387.88 kB/67.63 kB gzip plus 15.60 kB/3.27 kB gzip. `resources/js/app.js:1-8` globally imports Bootstrap, jQuery, Tom Select, Alpine, and SweetAlert2; Font Awesome emits large font assets | Measure representative pages, remove unused global imports, and introduce page-level dynamic imports only when they materially reduce transfer/parse cost. Establish a reviewed budget before claiming optimization |
| FE-06 | The legacy execution bridge makes CSP and modular testing harder | `resources/views/layouts/app.blade.php:587-589` stores page scripts in a template; `resources/js/app.js:94-109` recreates executable `<script>` elements. Inline handlers also remain across navigation, products, orders, reports, and employee filters | Replace page scripts one workflow at a time with data hooks and imported modules. Remove the bridge only after all stack consumers migrate; do not perform a big-bang rewrite |
| FE-07 | Design primitives and visual language are fragmented | `resources/views/components/stat-card.blade.php` and `stats-card.blade.php` implement competing APIs/visual systems; `app.blade.php` uses Inter, worker pages use Poppins, guest uses Figtree, and the welcome page uses Instrument Sans. Admin and worker layouts duplicate card/badge/color rules | Choose one role-aware token set and one stat-card contract, document exceptions, and migrate only touched screens. Preserve domain clarity rather than forcing identical admin/worker presentation |
| FE-08 | Dynamic status, error, and timing behavior is inconsistent | `resources/js/app.js:429-433` auto-closes all `.alert` elements after 5 seconds; SweetAlert toasts use 3-second timers; `admin/productos/index.blade.php:625-638` injects server text into `innerHTML` and removes the alert after 3 seconds; AJAX failures immediately navigate away | Use text insertion for server-provided messages, distinguish persistent errors from transient success, provide an accessible status region, and keep fallback navigation deterministic |
| FE-09 | Table semantics and responsive validation are incomplete | Repository-wide view search found no `<caption>` or `scope="col|row"`; responsive tests assert `data-label` values but not accessible table names, reading order, 200% zoom, or horizontal overflow | Add accessible names/captions where context is not already explicit, scope headers, and manually validate dense tables at mobile widths and 200% zoom before generalizing the card-table pattern |
| FE-10 | Frontend tests are strong structurally but shallow behaviorally | Six focused feature files passed, but `tests/js/ajax-filter-helpers.test.js` is the only JS unit file. PHP rendering tests do not execute browser focus, Bootstrap, Alpine, AJAX DOM replacement, screen-reader announcements, or CSS layout | Add a minimal browser-level smoke/a11y suite for login, navigation, one list filter/pagination flow, one modal, and one dynamic order form. Keep rendering tests for cheap structural contracts |

### P3: Conditional or Lower-Risk Cleanup

| ID | Finding | Evidence | Conditional outcome |
|---|---|---|---|
| FE-11 | Dependency intent is unclear | `chart.js` is installed but deliberately absent from `app.js`; `@tailwindcss/vite@4` is installed but `vite.config.js` does not register it, while `tailwindcss@3` runs through PostCSS; jQuery exists mainly for legacy compatibility | Remove unused packages in an isolated cleanup if no current consumer is found. Do not add charts until a decision-oriented chart requirement and data contract exist. Choose Tailwind 3/PostCSS or a planned Tailwind 4 migration, not both |
| FE-12 | External font providers add avoidable privacy/availability variance | Main, worker, guest, auth, and welcome surfaces use different Google/Bunny font requests | Self-host one or two approved families only if branding, privacy policy, and measured loading justify the work; otherwise use a documented system stack |
| FE-13 | The public welcome page is still framework scaffolding | `resources/views/welcome.blade.php` contains Laravel/Laracasts/Cloud links and a large generated inline Tailwind block | Replace only when a real public landing-page requirement exists. Until then, keep authenticated pages `noindex` and do not spend SEO effort on private workflows |
| FE-14 | Visual regression and contrast are not measured | Current tests assert markup and strings, not screenshots or computed contrast | Add screenshot/visual checks only after tokens and representative states stabilize; manually verify contrast first |

## Historical Slice Status Corrected

| Earlier slice | Current status | Current proof or correction |
|---|---|---|
| 1. Layout data cleanup | Verified | Shared admin navigation counters are outside Blade; rendering tests cover dashboard values |
| 2. Shared layouts | Partial | Dashboards share layouts, but six worker workflow pages still own full shells |
| 3. Blade components | Partial | Alerts, headers, badges, empty states, actions, and stat cards exist; adoption and stat-card APIs remain inconsistent |
| 4. Asset pipeline cleanup | Partial | Shared entries build through Vite, but inline blocks, the legacy bridge, worker shells, and login CDN assets remain |
| 5. Accessibility baseline | Verified baseline, not conformance | Skip links, landmarks, names, focus visibility, and live totals are tested; FE-04 remains |
| 6. Table/modal performance | Verified for audited screens | Reusable product modals, eager counts, responsive metadata, and empty states are tested; semantics and browser validation remain |
| 7. Searchable selects/progressive enhancement | Verified for current order/list flows | Tom Select initialization and GET filter helpers are tested; global loading and AJAX focus behavior remain |
| Image and report follow-ups | Partially verified | Thumbnail/list behavior and static report strategy are covered by prior tests; WebP/AVIF and charts remain conditional |

## Required Improvements

1. Keep the cleared dependency security gate as an acceptance criterion for future library or lockfile changes.
2. Finish worker/auth shell consolidation and eliminate duplicate external runtime assets.
3. Complete WCAG 2.2 interaction contracts for dialogs, errors, dynamic replacement, motion, timing, tables, and focus.
4. Migrate legacy inline behavior into small Vite modules and reduce always-loaded dependencies using measurements.
5. Add browser-level verification for the workflows that static rendering tests cannot prove.

## Optional Enhancements

- Add charts only when a dashboard/report question, data owner, accessible alternative, and bundle budget are documented.
- Evaluate WebP/AVIF only with representative image byte/quality measurements and deployment support.
- Add HTMX, Livewire, or Alpine patterns only for a concrete interaction that is materially harder with existing progressive JavaScript.
- Add visual regression testing after shared tokens and representative states stabilize.
- Consider local fonts or a smaller icon strategy only after bundle/network measurement.

## Resource and Dependency Inventory

### Current and Justified

| Resource | Current role | Direction |
|---|---|---|
| Blade components/layouts | Server-rendered structure and reusable UI | Keep; finish adoption |
| Vite + Laravel Vite plugin | Two application entries and production bundling | Keep audited and measure output |
| Bootstrap 5 | Dominant layout/component system | Keep as the primary component framework for current screens |
| Alpine.js | Shared Breeze-style modal/dropdown state | Keep only where currently used; do not duplicate Bootstrap behavior |
| Tom Select | Searchable order-flow selects | Keep scoped to large/searchable selects |
| SweetAlert2 | Existing confirmations/toasts | Keep temporarily; reduce use for routine status messages |
| Native Fetch API | Progressive filters and state changes | Prefer for new small interactions |
| Server pagination/filtering | Large lists and reports | Keep as canonical behavior |

### Duplicated or Legacy

| Resource | Evidence | Direction |
|---|---|---|
| jQuery and Bootstrap jQuery bridge | Global compatibility in `app.js` | Remove after inline consumers migrate; do not add new jQuery code |
| Inline Blade CSS/JS | 31 style blocks and 24 script blocks | Extract repeated behavior/styles by workflow |
| Complete worker page shells | Six worker workflow pages | Move to `layouts.trabajador` |
| Login CDN Bootstrap/Font Awesome/Animate.css | `auth/login.blade.php` | Replace with Vite/guest layout or secure a documented exception |
| Dual stat-card implementations | `stat-card.blade.php`, `stats-card.blade.php`, `App\View\Components\StatsCard` | Consolidate after usage mapping |
| Multiple font providers/families | Admin, worker, guest, auth, welcome | Standardize conditionally |

### Recommended Conditionally

| Candidate | Adopt only when | Validation |
|---|---|---|
| Chart.js | A specific decision-oriented chart and accessible data/table alternative exist | Page-only import, bundle delta, keyboard/contrast review |
| Livewire | A server-owned interaction has complex repeated round trips that existing progressive JS cannot maintain cleanly | One bounded pilot; latency, accessibility, and test cost reviewed |
| HTMX | Multiple HTML-fragment workflows justify a shared declarative convention | One list/detail pilot with history, errors, focus, and no-JS fallback |
| Browser automation + axe | The team can own a small stable critical-path suite | CI time and flake budget accepted |
| Blade Icons or a reduced icon subset | Font Awesome payload is measured as material | Visual coverage and accessible-name checks pass |

### Avoid or Remove

- Avoid React, Vue, Inertia, or SPA infrastructure without a real client-state/API requirement.
- Avoid combining Bootstrap, Tailwind component abstractions, and another UI framework on the same surface.
- Avoid DataTables while Laravel pagination/filtering remains canonical.
- Avoid a second toast, modal, select, or chart library.
- Remove `chart.js` if no current consumer is found.
- Remove unused `@tailwindcss/vite` unless a separately designed Tailwind 4 migration is approved.
- Remove jQuery only after consumers are migrated and regression-tested; premature deletion would break existing pages.

## Prioritized Roadmap

### P0: Incident Response Only

Acceptance criteria:

- No active exploit, critical workflow outage, or confirmed frontend data corruption is known.
- If one appears, stop roadmap work, contain it, add a regression test, and document the incident boundary.

Validation: production evidence, focused reproduction, and security/incident review.

### P1: Security, Shells, and Accessibility

Acceptance criteria:

- `npm audit --json` has no unresolved critical/high finding applicable to installed/build/browser paths, or each exception has documented reachability, owner, and expiry.
- All worker workflow pages extend `layouts.trabajador`; login uses the approved asset boundary with no unreviewed executable CDN dependency.
- Representative admin, worker, auth, modal, filter, and order-form flows pass keyboard, reduced-motion, 200% zoom, forced-colors, NVDA, and automated axe/Lighthouse checks.
- Dialogs expose role/name/modal state, field errors are programmatically associated, and AJAX replacement preserves a useful focus/status contract.

Validation: lock review, `npm audit`, isolated build, focused/full tests, browser and assistive-technology checklist.

### P2: Modules, Payload, Consistency, and Browser Tests

Acceptance criteria:

- Repeated page behavior is imported through Vite modules; no new inline handler or legacy-template consumer is added.
- A representative-page budget records transferred CSS/JS, gzip size, parse/execute cost, requests, LCP, and interaction latency.
- Global dependencies are justified by universal use; page-specific features load only where required.
- One stat-card API and a small token set cover new work; table names/header scopes and status/error patterns are consistent.
- A minimal browser suite covers login, navigation, one progressive list, one modal, and one dynamic order form.

Validation: bundle report, Lighthouse traces, network inspection, browser tests, rendering tests, and manual mobile review.

### P3: Conditional Product Enhancements

Acceptance criteria:

- Every new library or UI framework answers a documented user problem and passes the decision checklist below.
- Public-page, chart, image-format, font, icon, and visual-regression work has measurable value and an owner.

Validation: product requirement, before/after measurement, accessibility review, and removal plan.

## Review-Sized Implementation Slices

| Slice | Priority | Scope | Completion signal |
|---|---|---|---|
| 1. Frontend dependency security (complete) | P1 | Remediation committed as `c229dbf`; `package.json` unchanged | Full and production audits report zero vulnerabilities; 7 JavaScript tests and the 136-module Vite 7.3.6 build pass |
| 2. Worker product shell consolidation | P1 | Worker product index/detail onto shared layout | No duplicate document shell; routes and screenshots unchanged except intentional shell consistency |
| 3. Worker client/order shell consolidation | P1 | Client list and order create/index/detail onto shared layout in separate review units if needed | All worker pages extend shared layout; focused route tests pass |
| 4. Login asset boundary | P1 | Guest/auth layout plus Vite assets, preserving auth behavior | No duplicated executable CDN assets; login/password tests and keyboard review pass |
| 5. Reduced motion and timing | P1 | Shared motion override and persistent/controllable status rules | Reduced-motion and timing checklist passes on representative screens |
| 6. Dialog and AJAX focus contract | P1 | Shared Alpine modal semantics plus progressive-list focus/status restoration | Keyboard/NVDA/browser tests pass |
| 7. Form error semantics | P1 | Shared input/error components, then high-value admin/worker forms | Invalid fields expose associated messages; first-error behavior is defined and tested |
| 8. Legacy JS extraction | P2 | One domain page family per slice | Data hooks + imported module replace inline behavior; no bridge regression |
| 9. Payload measurement and splitting | P2 | Establish budget, then conditionally load Tom Select/SweetAlert/other non-universal code | Budget documented and representative pages improve without behavior loss |
| 10. Component/token consolidation | P2 | Stat cards, status colors, spacing, typography | One documented contract; dashboard rendering tests pass |
| 11. Table semantics and mobile validation | P2 | High-density lists/reports first | Accessible name/scope plus keyboard, zoom, and mobile evidence |
| 12. Browser smoke/a11y gate | P2 | Five critical workflows only | Stable CI/local command with documented limitations |

## Backend Roadmap Alignment

- **Dependency security:** frontend and Composer remediations remain separate reviewable commits (`c229dbf` and `7da3f53`) so regressions and advisory closure remain attributable.
- **Authentication and authorization:** frontend visibility is not authorization. Keep route middleware, Policies, and Form Request checks authoritative; do not infer permission from hidden buttons.
- **API direction:** no SPA or token API consumer is verified. Do not introduce Sanctum/API Resources solely for frontend modernization.
- **Queues and exports:** preserve server-rendered progress/error boundaries. If large exports become queued, the frontend needs an accessible status/polling/download contract; it must not simulate completion before the backend job succeeds.
- **Observability:** frontend fetch failures should expose a stable user message and emit useful request/operation context without personal data or credentials. Align correlation identifiers with backend logging if observability work is approved.
- **Error security:** never render raw backend exception details. Expected validation/domain messages may be shown; unexpected failures should use stable copy and internal logs.
- **Third parties:** frontend SDKs, analytics, payment, messaging, or maps require the same ownership, secret, privacy, timeout, retry, idempotency, CSP, and removal review defined by the backend roadmap.

## Dependency and UI Decision Checklist

Before adding a dependency, framework, or component system:

- [ ] The user problem and measurable success condition are documented.
- [ ] Existing Blade, Bootstrap, Alpine, native browser APIs, and server rendering were evaluated first.
- [ ] The capability is not already provided by an installed dependency.
- [ ] Security advisories, maintenance, license, browser support, Laravel/Vite compatibility, and transitive dependencies were reviewed.
- [ ] Bundle, CSS, font, request, and runtime costs were measured on a representative page.
- [ ] Keyboard, screen reader, contrast, reduced motion, zoom, forced colors, and target-size behavior are acceptable.
- [ ] Server rendering and no-JavaScript fallback remain correct where the workflow requires progressive enhancement.
- [ ] The choice does not create a second modal, toast, select, table, chart, icon, or design-token system.
- [ ] Test ownership, update cadence, operational ownership, and removal path are defined.
- [ ] Installation/update is isolated from unrelated UI refactors.

## Verification Evidence and Audit History

| Command/check | Verified outcome |
|---|---|
| `git rev-parse --show-toplevel` | Passed: `C:/xampp/htdocs/ProyectoDariva` |
| CodeGraph architecture exploration | Completed first; index existed. Recently edited files were read directly where CodeGraph warned of pending synchronization |
| Composer remediation | Commit `7da3f53`; zero Composer advisories; PHPStan clean; 494 tests and 2,505 assertions passed |
| Full and production npm audits | Zero vulnerabilities after remediation commit `c229dbf`; `package.json` unchanged |
| `npm run test:js` | Passed: 7 tests after frontend dependency remediation |
| Vite 7.3.6 production build | Passed: 136 modules after frontend dependency remediation |
| npm deprecation output | `glob@10.5.0` remains a dev-only transitive dependency through `tailwindcss` and `sucrase`; no active npm vulnerability applies |
| Browserslist maintenance output | The `caniuse-lite` age warning is separate non-blocking maintenance |
| 2026-07-18 frontend audit | Historical baseline reported 14 vulnerable packages and a successful 134-module isolated build; remediation commit `c229dbf` supersedes its dependency status |

### Verification Checklist for Dependency Changes

```bash
npm audit --json
npm run test:js
php artisan test tests/Feature/AccessibilityBaselineRenderingTest.php tests/Feature/AssetPipelineRenderingTest.php tests/Feature/BladeComponentRenderingTest.php tests/Feature/DashboardLayoutRenderingTest.php tests/Feature/SearchableSelectRenderingTest.php tests/Feature/TableAndModalPerformanceRenderingTest.php
npx vite build --outDir C:/Users/crist/AppData/Local/Temp/opencode/proyectodariva-vite-audit-build --emptyOutDir
git diff --check -- docs/frontend-views-improvement-plan.md
```

Manual release checks for changed UI:

- [ ] Complete keyboard-only navigation, including open/close/return focus for dialogs and menus.
- [ ] NVDA reads landmarks, names, errors, dynamic status, tables, and totals in a useful order.
- [ ] Layout remains usable at 320 CSS pixels and 200% zoom without hidden actions or two-dimensional scrolling except genuine data tables.
- [ ] Windows forced-colors/high-contrast mode preserves controls, focus, status, and selected state.
- [ ] `prefers-reduced-motion: reduce` disables non-essential movement.
- [ ] Interactive targets meet WCAG 2.2's 24 by 24 CSS pixel minimum or a valid exception.
- [ ] Error, empty, loading, success, disabled, and network-failure states remain understandable without relying on color alone.
- [ ] External resources, CSP behavior, source maps, and browser console/network failures are reviewed.

## Risks and Limitations

- No production traffic, real-user monitoring, Core Web Vitals, device matrix, screenshots, or design specification was available; bundle and code evidence identify risk but not production impact.
- Automated rendering tests do not prove computed contrast, focus movement, screen-reader output, responsive layout, or JavaScript behavior in a real browser.
- Audit results reflect registry data at verification time. The remaining `glob@10.5.0` deprecation is dev-only and has no active npm vulnerability; the `caniuse-lite` age warning is separate maintenance.
- The isolated build used current local `node_modules` and wrote only to the approved temporary directory, not `public/build`.
- The 2026-07-18 focused frontend snapshot remains historical evidence. Composer remediation commit `7da3f53` subsequently passed the full 494-test, 2,505-assertion suite and clean PHPStan analysis; frontend remediation commit `c229dbf` separately passed both npm audits, all 7 JavaScript tests, and the Vite production build.
- CodeGraph reported several files pending watcher synchronization during exploration; those relevant to frontend evidence were read directly, and executable tests were treated as stronger proof.

## Next Recommended Action

Proceed with **Slice 2: worker product shell consolidation**, followed by the remaining worker client/order shells and the login asset boundary. Keep the completed dependency gate as a validation requirement for future dependency changes; it is no longer a blocker or remediation action.
