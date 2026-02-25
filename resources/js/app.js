import '../css/app.css';
import Sortable from 'sortablejs';
import * as Bootstrap from 'bootstrap';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

window.Sortable = Sortable;
window.Bootstrap = Bootstrap; // Optional: expose if inline scripts need `new bootstrap.Modal(...)`
window.Chart = Chart;

/**
 * GLOBAL ARCHITECTURE: PROTEKTA APP JS
 * Terpusat, Efisien, dan AJAX-Ready
 */

// Initial Setup (Merge with existing if present)
const existingRegistry = (window.Protekta && window.Protekta.initRegistry) ? window.Protekta.initRegistry : [];

window.Protekta = {
    // Registry untuk fungsi inisialisasi yang harus dipanggil saat konten berubah
    initRegistry: existingRegistry,

    registerInit: function(fn) {
        if (typeof fn === 'function') {
            this.initRegistry.push(fn);
            // Langsung jalankan jika DOM sudah siap & app.js sudah load
            if (document.readyState === 'complete' || document.readyState === 'interactive') fn();
        }
    },

    initAll: function() {
        console.log('[Protekta] Initializing components...');
        this.initRegistry.forEach(fn => {
            try { fn(); } catch (e) {
                console.error('[Protekta] Init error:', e);
            }
        });
    },

    // Global Helpers
    helpers: {
        formatWA: function(number) {
            let clean = String(number).replace(/\D/g, '');
            if (clean.startsWith('0')) clean = '62' + clean.substring(1);
            return clean;
        },
        copyToClipboard: function(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Tersalin ke clipboard!');
            });
        }
    }
};

/**
 * GLOBAL EVENT DELEGATION
 */
document.addEventListener('click', function(e) {
    // 1. Password Visibility Toggle
    const togglePassBtn = e.target.closest('.toggle-password');
    if (togglePassBtn) {
        e.preventDefault();
        const targetId = togglePassBtn.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const icon = togglePassBtn.querySelector('i');
        if (passwordInput && icon) {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    }

    // 2. Signature Pad Actions
    const sigToggleBtn = e.target.closest('[class*="toggle-signature-btn-"]');
    if (sigToggleBtn) {
        e.preventDefault();
        const typeMatch = sigToggleBtn.className.match(/toggle-signature-btn-([a-zA-Z0-9_-]+)/);
        if (typeMatch) window.dispatchEvent(new CustomEvent('signature:toggle', { detail: { type: typeMatch[1] } }));
    }

    const sigClearBtn = e.target.closest('[class*="clear-signature-btn-"]');
    if (sigClearBtn) {
        e.preventDefault();
        const typeMatch = sigClearBtn.className.match(/clear-signature-btn-([a-zA-Z0-9_-]+)/);
        if (typeMatch) window.dispatchEvent(new CustomEvent('signature:clear', { detail: { type: typeMatch[1] } }));
    }

    // 3. Quick Actions (WhatsApp & Phone)
    const waBtn = e.target.closest('[data-wa]');
    if (waBtn) {
        e.preventDefault();
        const num = window.Protekta.helpers.formatWA(waBtn.dataset.wa);
        const text = waBtn.dataset.message || '';
        window.open(`https://wa.me/${num}?text=${encodeURIComponent(text)}`, '_blank');
    }

    const phoneBtn = e.target.closest('[data-phone]');
    if (phoneBtn) {
        // Biarkan default tel: browser kecuali jika kita ingin intervensi
    }

    // 4. Global Confirm Action
    const confirmBtn = e.target.closest('[data-confirm]');
    if (confirmBtn && confirmBtn.tagName !== 'FORM') { // Exclude forms as they have onsubmit
        if (!confirm(confirmBtn.dataset.confirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }

    // 5. Global Modal Toggles (Checking defaultPrevented to allow page scripts to override)
    const modalToggle = e.target.closest('[data-modal-toggle]');
    if (modalToggle && !e.defaultPrevented) {
        const targetId = modalToggle.getAttribute('data-modal-toggle');
        window.Protekta.modal.toggle(targetId);
    }
    
    // Closer for generic close buttons in modals
    if (e.target.closest('.btn-close-modal')) {
        const modal = e.target.closest('.fixed.inset-0'); // Find the closest modal backdrop
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
});

/**
 * MUTATION OBSERVER (AUTO-INITIALIZATION)
 * Debounced to prevent performance hit during mass DOM changes.
 */
let debounceTimer;
const observer = new MutationObserver((mutations) => {
    let shouldUpdate = false;
    mutations.forEach(mutation => {
        if (mutation.addedNodes.length) shouldUpdate = true;
    });

    if (shouldUpdate) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            window.Protekta.initAll();
            window.dispatchEvent(new CustomEvent('content:updated'));
        }, 100);
    }
});

observer.observe(document.body, { childList: true, subtree: true });

// Programmatic Modal Control
window.Protekta.modal = {
    show: function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    },
    hide: function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    },
    toggle: function(id) {
        const modal = document.getElementById(id);
        if (modal && modal.classList.contains('hidden')) this.show(id);
        else if (modal) this.hide(id);
    }
};

/**
 * INITIAL LOAD
 */
window.addEventListener('load', () => {
    window.Protekta.initAll();
    window.dispatchEvent(new CustomEvent('content:updated'));
});

// Case where app.js loads after window 'load' event (e.g. some SPA navigations or slow network)
if (document.readyState === 'complete') {
    window.Protekta.initAll();
}