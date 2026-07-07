import './bootstrap';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.$ = window.jQuery = $;
window.Swal = Swal;

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

document.addEventListener('DOMContentLoaded', () => {
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
});
