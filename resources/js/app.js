import './bootstrap';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';
import TomSelect from 'tom-select/base';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import {
    buildAjaxFormUrl,
    buildAjaxPaginationUrl,
    getAjaxFallbackUrl,
    isAjaxFilterFormEligible,
} from './ajax-filter-helpers';

export {
    buildAjaxFormUrl,
    buildAjaxPaginationUrl,
    getAjaxFallbackUrl,
    isAjaxFilterFormEligible,
};

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.$ = window.jQuery = $;
window.Swal = Swal;

export function enhanceSearchableSelect(selectElement, TomSelectClass = TomSelect) {
    if (!selectElement || selectElement.tomselect) {
        return selectElement?.tomselect ?? null;
    }

    return new TomSelectClass(selectElement, {
        allowEmptyOption: true,
        create: false,
        maxOptions: null,
        placeholder: selectElement.dataset.searchableSelectPlaceholder
            ?? selectElement.querySelector('option[value=""]')?.textContent?.trim()
            ?? 'Search...',
    });
}

export function enhanceSearchableSelects(root = document, TomSelectClass = TomSelect) {
    return Array.from(root.querySelectorAll('select[data-enhance="searchable-select"]'))
        .map((selectElement) => enhanceSearchableSelect(selectElement, TomSelectClass));
}

export function clearSearchableSelect(target) {
    const selectElement = typeof target === 'string' ? document.querySelector(target) : target;

    if (!selectElement) {
        return;
    }

    if (selectElement.tomselect) {
        selectElement.tomselect.clear(true);
        selectElement.tomselect.blur();
        return;
    }

    selectElement.value = '';
}

window.enhanceSearchableSelect = enhanceSearchableSelect;
window.enhanceSearchableSelects = enhanceSearchableSelects;
window.clearSearchableSelect = clearSearchableSelect;

function installBootstrapJqueryBridge() {
    if (!$.fn.modal) {
        $.fn.modal = function(action = 'show') {
            return this.each(function() {
                const modal = bootstrap.Modal.getOrCreateInstance(this);

                if (typeof modal[action] === 'function') {
                    modal[action]();
                }
            });
        };
    }

    if (!$.fn.tooltip) {
        $.fn.tooltip = function(action) {
            return this.each(function() {
                const tooltip = bootstrap.Tooltip.getOrCreateInstance(this);

                if (typeof action === 'string' && typeof tooltip[action] === 'function') {
                    tooltip[action]();
                }
            });
        };
    }
}

function runLegacyPageScripts() {
    document.querySelectorAll('template[data-run-after-vite="legacy-scripts"]').forEach((template) => {
        template.content.querySelectorAll('script').forEach((script) => {
            const executableScript = document.createElement('script');

            Array.from(script.attributes).forEach((attribute) => {
                executableScript.setAttribute(attribute.name, attribute.value);
            });

            executableScript.textContent = script.textContent;
            document.body.appendChild(executableScript);
        });

        template.remove();
    });
}

const ajaxFilterControllers = new WeakMap();
const ajaxInputTimers = new WeakMap();

function getAjaxTarget(formOrLink) {
    const selector = formOrLink?.dataset?.ajaxTarget;

    if (!selector) {
        return null;
    }

    return document.querySelector(selector);
}

function setAjaxLoading(target, isLoading) {
    if (!target) {
        return;
    }

    target.classList.toggle('is-ajax-loading', isLoading);
    target.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    target.style.opacity = isLoading ? '0.55' : '';
    target.style.pointerEvents = isLoading ? 'none' : '';
}

function replaceAjaxRegion(target, html) {
    const parser = new DOMParser();
    const nextDocument = parser.parseFromString(html, 'text/html');
    const nextTarget = nextDocument.querySelector(`#${CSS.escape(target.id)}`);

    if (!nextTarget) {
        throw new Error(`AJAX target #${target.id} was not found in the response.`);
    }

    target.innerHTML = nextTarget.innerHTML;
    enhanceSearchableSelects(target);
    target.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
        bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
    });
}

function fetchAjaxRegion(url, target, options = {}) {
    if (!target?.id) {
        window.location.assign(url.toString());
        return Promise.resolve();
    }

    const previousController = ajaxFilterControllers.get(target);
    previousController?.abort();

    const controller = new AbortController();
    ajaxFilterControllers.set(target, controller);
    setAjaxLoading(target, true);

    return fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'text/html',
            'X-Requested-With': 'XMLHttpRequest',
        },
        signal: controller.signal,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`AJAX request failed with status ${response.status}`);
            }

            return response.text();
        })
        .then((html) => {
            replaceAjaxRegion(target, html);

            if (options.pushState !== false) {
                window.history.pushState({ ajaxTarget: `#${target.id}` }, '', url);
            }
        })
        .catch((error) => {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(error);
            window.location.assign(getAjaxFallbackUrl(url));
        })
        .finally(() => {
            if (ajaxFilterControllers.get(target) === controller) {
                ajaxFilterControllers.delete(target);
                setAjaxLoading(target, false);
            }
        });
}

function submitAjaxFilterForm(form) {
    if (!isAjaxFilterFormEligible(form)) {
        return;
    }

    fetchAjaxRegion(buildAjaxFormUrl(form), getAjaxTarget(form));
}

function installAjaxFilterEnhancements() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-ajax-filter="true"]');

        if (!isAjaxFilterFormEligible(form)) {
            return;
        }

        event.preventDefault();
        submitAjaxFilterForm(form);
    });

    document.addEventListener('change', (event) => {
        const field = event.target.matches('select') && event.target.form?.matches('form[data-ajax-filter="true"][data-ajax-auto-submit="true"]')
            ? event.target
            : event.target.closest('form[data-ajax-filter="true"][data-ajax-auto-submit="true"] select');

        if (!field) {
            return;
        }

        submitAjaxFilterForm(field.form);
    });

    document.addEventListener('input', (event) => {
        const field = event.target.closest('form[data-ajax-filter="true"][data-ajax-auto-submit="true"] input[type="search"], form[data-ajax-filter="true"][data-ajax-auto-submit="true"] input[type="text"]');

        if (!field) {
            return;
        }

        clearTimeout(ajaxInputTimers.get(field));
        ajaxInputTimers.set(field, setTimeout(() => submitAjaxFilterForm(field.form), 450));
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-ajax-link="true"], [data-ajax-region] .pagination a');

        if (!link || event.defaultPrevented || event.button !== 0 || link.target || link.hasAttribute('download')) {
            return;
        }

        const target = link.dataset.ajaxTarget
            ? getAjaxTarget(link)
            : link.closest('[data-ajax-region]');

        if (!target) {
            return;
        }

        event.preventDefault();
        const url = link.closest('.pagination')
            ? buildAjaxPaginationUrl(link.href, window.location.href)
            : new URL(link.href, window.location.origin);

        fetchAjaxRegion(url, target);
    });

    window.addEventListener('popstate', () => {
        document.querySelectorAll('[data-ajax-region][id]').forEach((target) => {
            fetchAjaxRegion(new URL(window.location.href), target, { pushState: false });
        });
    });
}

installBootstrapJqueryBridge();
runLegacyPageScripts();
installAjaxFilterEnhancements();

Alpine.start();

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    },
});

// Make SweetAlert available for existing Blade page scripts.
window.confirmDelete = function(empleadoId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + empleadoId).submit();
        }
    });
}

window.showToast = function(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: icon,
        title: title
    });
}

export function resetProductStockModalForm(form) {
    if (form) {
        form.reset();
    }
}

export function registerProductStockModalHandlers(productStockModal, documentRef = document) {
    if (!productStockModal) {
        return;
    }

    productStockModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = documentRef.getElementById('productStockModalForm');
        const title = documentRef.getElementById('productStockModalLabel');
        const current = documentRef.getElementById('productStockModalCurrent');

        if (!trigger || !form || !title || !current) {
            return;
        }

        resetProductStockModalForm(form);
        form.action = trigger.dataset.productStockAction ?? '#';
        title.textContent = `Actualizar Stock - ${trigger.dataset.productStockName ?? ''}`.trim();
        current.textContent = trigger.dataset.productStockCurrent ?? '0';
    });

    productStockModal.addEventListener('hidden.bs.modal', () => {
        const form = documentRef.getElementById('productStockModalForm');
        const title = documentRef.getElementById('productStockModalLabel');
        const current = documentRef.getElementById('productStockModalCurrent');

        resetProductStockModalForm(form);

        if (form) {
            form.action = '#';
        }

        if (title) {
            title.textContent = 'Actualizar Stock';
        }

        if (current) {
            current.textContent = '0';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    enhanceSearchableSelects(document);

    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    const pageContentWrapper = document.getElementById('page-content-wrapper');
    const navbar = document.querySelector('.navbar-custom');

    if (sidebarToggle && wrapper) {
        sidebarToggle.addEventListener('click', () => {
            wrapper.classList.toggle('toggled');
            localStorage.setItem('sidebarToggled', wrapper.classList.contains('toggled'));
        });

        if (localStorage.getItem('sidebarToggled') === 'true') {
            wrapper.classList.add('toggled');
        }
    }

    if (window.innerWidth < 768 && wrapper && pageContentWrapper) {
        pageContentWrapper.addEventListener('click', () => {
            if (wrapper.classList.contains('toggled')) {
                wrapper.classList.remove('toggled');
            }
        });
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const flashMessages = document.getElementById('flash-messages');
    if (flashMessages) {
        [
            ['success', '¡Éxito!'],
            ['error', '¡Error!'],
            ['warning', '¡Advertencia!'],
            ['info', 'Información'],
        ].forEach(([type, title]) => {
            const text = flashMessages.dataset[type];
            if (!text) {
                return;
            }

            Swal.fire({
                icon: type,
                title,
                text,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
            });
        });
    }

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach((alert) => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        });
    }, 5000);

    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.style.boxShadow = window.scrollY > 10
                ? '0 4px 12px rgba(0,0,0,0.08)'
                : '0 2px 4px rgba(0,0,0,0.05)';
        });
    }

    const productImageModal = document.getElementById('productImageModal');
    if (productImageModal) {
        productImageModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const image = document.getElementById('productImageModalImg');
            const title = document.getElementById('productImageModalLabel');

            if (!trigger || !image || !title) {
                return;
            }

            image.src = trigger.dataset.productImageSrc ?? '';
            image.alt = trigger.dataset.productImageAlt ?? '';
            title.textContent = trigger.dataset.productImageTitle ?? 'Imagen del producto';
        });

        productImageModal.addEventListener('hidden.bs.modal', () => {
            const image = document.getElementById('productImageModalImg');

            if (image) {
                image.src = '';
                image.alt = '';
            }
        });
    }

    registerProductStockModalHandlers(document.getElementById('productStockModal'), document);
});
