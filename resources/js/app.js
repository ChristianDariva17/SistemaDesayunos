import './bootstrap';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';
import TomSelect from 'tom-select/base';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

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

installBootstrapJqueryBridge();
runLegacyPageScripts();

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
