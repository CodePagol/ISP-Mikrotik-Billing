/**
 * app.js — Unified Main Entry Point
 * ============================================================================
 * Merged: original app.js + cp.js + codepagol.js + config.js
 * Deleted duplicates: main-site.js, codepagol.js, config.js,
 *                     custom copy.js, theme copy.js, codepagol copy.js
 * ============================================================================
 */

// ── Core ─────────────────────────────────────────────────────────────────────
import './bootstrap';
import 'bootstrap';
import * as bootstrap from 'bootstrap';

// ── jQuery ────────────────────────────────────────────────────────────────────
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;
window.bootstrap = bootstrap;

// ── Sorting ───────────────────────────────────────────────────────────────────
import 'livewire-sortable';

// ── Alerts ────────────────────────────────────────────────────────────────────
import Swal from 'sweetalert2';
window.Swal = Swal;

// ── Print ─────────────────────────────────────────────────────────────────────
import print from 'print-js';
window.print = print;

// ── QR Code ───────────────────────────────────────────────────────────────────
import QRCode from 'qrcode';
window.QRCode = QRCode;

// ── Date / Time ───────────────────────────────────────────────────────────────
import moment from 'moment';
window.moment = moment;

// ── Charts ────────────────────────────────────────────────────────────────────
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

// ── Telephone Input ──────────────────────────────────────────────────────────
import intlTelInput from 'intl-tel-input';
window.intlTelInput = intlTelInput;

// ── Icons ─────────────────────────────────────────────────────────────────────
import 'bootstrap-icons/font/bootstrap-icons.css';

// ── Date Range Picker ─────────────────────────────────────────────────────────
import 'daterangepicker/daterangepicker.css';
import 'daterangepicker/daterangepicker.js';

// ── File / PDF ────────────────────────────────────────────────────────────────
import JSZip from 'jszip';
window.JSZip = JSZip;

import pdfmake from 'pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
pdfmake.addVirtualFileSystem(pdfFonts);
window.pdfmake = pdfmake;

// ── DataTables ────────────────────────────────────────────────────────────────
import DataTable from 'datatables.net-bs5';
window.DataTable = DataTable;
import 'datatables.net-autofill-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-colreorder-bs5';
import 'datatables.net-columncontrol-bs5';
import DateTime from 'datatables.net-datetime';
window.DateTime = DateTime;
import 'datatables.net-fixedcolumns-bs5';
import 'datatables.net-fixedheader-bs5';
import 'datatables.net-keytable-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-rowgroup-bs5';
import 'datatables.net-rowreorder-bs5';
import 'datatables.net-scroller-bs5';
import 'datatables.net-searchbuilder-bs5';
import 'datatables.net-searchpanes-bs5';
import 'datatables.net-select-bs5';
import 'datatables.net-staterestore-bs5';

DataTable.Buttons.jszip(JSZip);
DataTable.Buttons.pdfMake(pdfmake);

// ── CP (CodePagol) Controller ─────────────────────────────────────────────────
// Replaces: cp.js + codepagol.js + config.js — merged here once, no duplicates

const CP = {
    /**
     * Set localStorage config defaults.
     * (Replaces standalone config.js)
     */
    initConfig() {
        const CONFIG = {
            isNavbarVerticalCollapsed: true,
            theme:          'auto',
            isRTL:          false,
            isFluid:        true,
            navbarStyle:    'transparent',
            navbarPosition: 'vertical',
        };

        Object.keys(CONFIG).forEach(key => {
            if (localStorage.getItem(key) === null) {
                localStorage.setItem(key, CONFIG[key]);
            }
        });

        // Apply theme
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else if (theme === 'auto') {
            document.documentElement.setAttribute(
                'data-bs-theme',
                window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
            );
        }

        // Restore collapsed state
        if (JSON.parse(localStorage.getItem('isNavbarVerticalCollapsed'))) {
            document.documentElement.classList.add('navbar-vertical-collapsed');
        }
    },

    /** Handle navbar toggle click */
    handleNavbarClick() {
        const html = document.querySelector('html');
        html.classList.toggle('navbar-vertical-collapsed');
        const isCollapsed = html.classList.contains('navbar-vertical-collapsed');
        localStorage.setItem('isNavbarVerticalCollapsed', isCollapsed);
        document.dispatchEvent(new CustomEvent('navbarVerticalToggled', { detail: { isCollapsed } }));
    },

    /** Set up vertical navbar collapse/expand */
    navbarComboInit() {
        const html                 = document.querySelector('html');
        const navbarVerticalToggle = document.querySelector('.navbar-vertical-toggle');

        if (navbarVerticalToggle) {
            navbarVerticalToggle.removeEventListener('click', this.handleNavbarClick);
            navbarVerticalToggle.addEventListener('click', this.handleNavbarClick);
        }

        const isCollapsed = JSON.parse(localStorage.getItem('isNavbarVerticalCollapsed'));
        html.classList.toggle('navbar-vertical-collapsed', isCollapsed);
    },

    /** Master init — call once on DOMContentLoaded */
    init() {
        this.initConfig();
        this.navbarComboInit();
    },
};

window.CP = CP;
export default CP;

// ── Bootstrap & Theme Component Initializer ───────────────────────────────────
const initBootstrapComponents = () => {
    // 1. Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
            const ins = bootstrap.Tooltip.getInstance(el);
            if (ins) ins.dispose();
            new bootstrap.Tooltip(el);
        } catch (e) {
            console.error('Failed to init tooltip:', e);
        }
    });

    // 2. Popovers
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        try {
            const ins = bootstrap.Popover.getInstance(el);
            if (ins) ins.dispose();
            new bootstrap.Popover(el);
        } catch (e) {
            console.error('Failed to init popover:', e);
        }
    });

    // 3. Reset Collapse, Dropdowns and Modals to prevent state mismatch after Livewire navigation
    document.querySelectorAll('.collapse').forEach(el => {
        try {
            const ins = bootstrap.Collapse.getInstance(el);
            if (ins) ins.dispose();
        } catch (e) {
            console.error('Failed to dispose collapse:', e);
        }
    });

    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
        try {
            const ins = bootstrap.Dropdown.getInstance(el);
            if (ins) ins.dispose();
        } catch (e) {
            console.error('Failed to dispose dropdown:', e);
        }
    });

    document.querySelectorAll('.modal').forEach(el => {
        try {
            const ins = bootstrap.Modal.getInstance(el);
            if (ins) ins.dispose();
        } catch (e) {
            console.error('Failed to dispose modal:', e);
        }
    });

    // 4. Init CP Layout config & sidebar toggles
    CP.init();
};

document.addEventListener('DOMContentLoaded', initBootstrapComponents);
document.addEventListener('livewire:navigated', initBootstrapComponents);

// ── Livewire / Alpine intl-tel-input Integration ──────────────────────────────
document.addEventListener('livewire:init', () => {
    // Global submit event interceptor (uses capturing phase to execute before Livewire or other listeners)
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const phoneInputs = form.querySelectorAll('input');
        let firstInvalidInput = null;

        phoneInputs.forEach(input => {
            if (input._iti && input._validateNumber) {
                const iti = input._iti;
                let isValid = iti.isValidNumber();

                // Additionally check number type: accept Mobile and FIXED_LINE_OR_MOBILE only.
                // isValidNumber() can return true for shorter fixed-line numbers,
                // so this ensures mobile-format numbers are enforced.
                if (isValid && window.intlTelInputUtils) {
                    const type = iti.getNumberType();
                    const utils = window.intlTelInputUtils;
                    const okTypes = [
                        utils.numberType.MOBILE,
                        utils.numberType.FIXED_LINE_OR_MOBILE,
                        -1  // UNKNOWN — utils may not have loaded yet, don't block
                    ];
                    if (!okTypes.includes(type)) {
                        isValid = false;
                    }
                }

                if (input.value.trim() && !isValid) {
                    firstInvalidInput = firstInvalidInput || input;
                    input._hasBeenBlurred = true;
                    input._validateNumber();
                }
            }
        });

        if (firstInvalidInput) {
            e.preventDefault();
            e.stopImmediatePropagation();
            firstInvalidInput.focus();
        }
    }, true);

    const registerAlpineIntlTelInput = () => {
        Alpine.data('intlTelInput', (wireModelParam) => ({
            init() {
                const input = this.$el;
                const siteCountry = (window.sitePhoneCountry || 'bd').toLowerCase();
                const iti = window.intlTelInput(input, {
                    allowDropdown: false,
                    initialCountry: siteCountry,
                    onlyCountries: [siteCountry],
                    separateDialCode: true,
                    strictMode: true,
                    loadUtils: () => import("intl-tel-input/dist/js/utils.js")
                });

                // Dynamically discover wire:model attribute from element if not passed as parameter
                let wireModel = wireModelParam;
                if (!wireModel) {
                    const attrs = input.attributes;
                    for (let i = 0; i < attrs.length; i++) {
                        if (attrs[i].name.startsWith('wire:model')) {
                            wireModel = attrs[i].value;
                            break;
                        }
                    }
                }

                // Discover x-model if present
                let xModel = null;
                const attrs = input.attributes;
                for (let i = 0; i < attrs.length; i++) {
                    if (attrs[i].name === 'x-model' || attrs[i].name === 'x-model.defer') {
                        xModel = attrs[i].value;
                        break;
                    }
                }

                let isInternalUpdate = false;

                // Attach references directly to the element to access them globally in the form submit handler
                input._iti = iti;
                input._hasBeenBlurred = false;

                // For standard HTML form inputs (non-Livewire), create a hidden input with the original name to store the full number
                let hiddenInput = null;
                if (!wireModel && input.name) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    let initialFull = iti.getNumber();
                    if (initialFull && initialFull.startsWith('+')) {
                        initialFull = initialFull.substring(1);
                    }
                    hiddenInput.value = initialFull || input.value;
                    input.removeAttribute('name');
                    input.parentNode.appendChild(hiddenInput);
                }

                // Retrieve initial value from Livewire property, x-model, or input value
                let initialValue = '';
                if (wireModel && this.$wire) {
                    try {
                        initialValue = this.$wire.get(wireModel);
                    } catch (e) {}
                }
                if (!initialValue && xModel) {
                    try {
                        initialValue = Alpine.evaluate(input, xModel);
                    } catch (e) {}
                }
                if (!initialValue && input.value) {
                    initialValue = input.value;
                }
                if (initialValue) {
                    // Prepend '+' if the number starts with any country code (doesn't start with '+' or '0')
                    if (!initialValue.startsWith('+') && !initialValue.startsWith('0')) {
                        initialValue = '+' + initialValue;
                    }
                    iti.setNumber(initialValue);
                }

                if (wireModel) {
                    this.$watch(() => {
                        try {
                            return this.$wire.get(wireModel);
                        } catch (e) {
                            return null;
                        }
                    }, value => {
                        if (value) {
                            const cleanValue = String(value).replace(/\D/g, '');
                            const cleanIti = iti.getNumber().replace(/\D/g, '');
                            if (cleanValue !== cleanIti) {
                                isInternalUpdate = true;
                                iti.setNumber('+' + cleanValue);
                                isInternalUpdate = false;
                            }
                        } else {
                            if (iti.getNumber() !== '') {
                                isInternalUpdate = true;
                                iti.setNumber('');
                                isInternalUpdate = false;
                            }
                        }
                    });
                }

                if (xModel) {
                    this.$watch(() => {
                        try {
                            return Alpine.evaluate(input, xModel);
                        } catch (e) {
                            return null;
                        }
                    }, value => {
                        if (value) {
                            const cleanValue = String(value).replace(/\D/g, '');
                            const cleanIti = iti.getNumber().replace(/\D/g, '');
                            if (cleanValue !== cleanIti) {
                                isInternalUpdate = true;
                                iti.setNumber('+' + cleanValue);
                                isInternalUpdate = false;
                            }
                        } else {
                            if (iti.getNumber() !== '') {
                                isInternalUpdate = true;
                                iti.setNumber('');
                                isInternalUpdate = false;
                            }
                        }
                    });
                }

                // Create or select an error message placeholder
                const errorMsg = document.createElement('div');
                errorMsg.className = 'invalid-feedback';
                errorMsg.style.display = 'none';
                errorMsg.style.fontSize = '0.875rem';
                errorMsg.style.marginTop = '0.25rem';
                // Insert errorMsg right after the intl-tel-input wrapper
                input.parentNode.parentNode.appendChild(errorMsg);

                const validateNumber = () => {
                    if (!input._hasBeenBlurred) return;
                    const val = input.value.trim();
                    if (val) {
                        let isValid = iti.isValidNumber();

                        // Additionally reject fixed-line-only numbers (too short for mobile format)
                        // getNumberType() returns MOBILE(1), FIXED_LINE_OR_MOBILE(0), FIXED_LINE(2), etc.
                        if (isValid && window.intlTelInputUtils) {
                            const type    = iti.getNumberType();
                            const utils   = window.intlTelInputUtils;
                            const okTypes = [
                                utils.numberType.MOBILE,
                                utils.numberType.FIXED_LINE_OR_MOBILE,
                                -1  // UNKNOWN — don't block if type is indeterminate
                            ];
                            if (!okTypes.includes(type)) {
                                isValid = false;
                            }
                        }

                        if (isValid) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                            errorMsg.style.display = 'none';
                        } else {
                            const errorCode = iti.getValidationError();
                            const errorMap = [
                                "Invalid number",
                                "Invalid country code",
                                "Too short",
                                "Too long",
                                "Invalid number"
                            ];
                            // If number type is wrong (e.g. fixed-line only), show appropriate message
                            const type        = window.intlTelInputUtils ? iti.getNumberType() : -1;
                            const isWrongType = type === 2; // FIXED_LINE
                            errorMsg.innerText = isWrongType
                                ? "Please enter a mobile number"
                                : (errorMap[errorCode] || "Invalid phone number");
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                            errorMsg.style.display = 'block';
                        }
                    } else {
                        input.classList.remove('is-invalid', 'is-valid');
                        errorMsg.style.display = 'none';
                    }
                };

                input._validateNumber = validateNumber;

                // Intercept all input events to prevent Alpine's native model sync from reading the partial number.
                // We update Alpine/Livewire models manually in handleUpdate with the full number.
                input.addEventListener('input', (e) => {
                    e.stopPropagation();
                }, true);

                const handleUpdate = () => {
                    if (isInternalUpdate) return;
                    let fullNumber = iti.getNumber();
                    // Strip the leading '+' prefix to match database representation (e.g. 8801751136819)
                    if (fullNumber && fullNumber.startsWith('+')) {
                        fullNumber = fullNumber.substring(1);
                    }
                    if (wireModel) {
                        this.$wire.set(wireModel, fullNumber);
                    } else if (xModel) {
                        try {
                            Alpine.evaluate(input, `${xModel} = '${fullNumber}'`);
                        } catch (e) {
                            console.warn('Failed to set x-model value via Alpine.evaluate:', e);
                        }
                    } else if (hiddenInput) {
                        hiddenInput.value = fullNumber;
                    }

                    validateNumber();
                };

                input.addEventListener('countrychange', handleUpdate);
                input.addEventListener('blur', () => {
                    input._hasBeenBlurred = true;
                    handleUpdate();
                });
                input.addEventListener('input', () => {
                    handleUpdate();
                    if (input._hasBeenBlurred) {
                        validateNumber();
                    }
                });
                input.addEventListener('change', handleUpdate);
            }
        }));
    };

    if (typeof Alpine !== 'undefined') {
        registerAlpineIntlTelInput();
    } else {
        document.addEventListener('alpine:init', registerAlpineIntlTelInput);
    }
});
