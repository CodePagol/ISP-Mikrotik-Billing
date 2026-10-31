    <div>
        <!-- Modern Responsive Glassmorphism Design System -->
        <style>
            .fi-fo-file-upload .fi-fo-file-upload-editor {
                width: 100% !important;
                top: -10% !important;
            }

            .glass-card {
                background: rgba(var(--bs-body-bg-rgb), 0.72) !important;
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                border: 1px solid rgba(var(--bs-body-color-rgb), 0.08) !important;
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 0 1px 1px rgba(255, 255, 255, 0.1) inset;
                border-radius: 18px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .modern-nav-pill {
                transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 12px;
                font-weight: 500;
                border: 1px solid transparent;
                font-size: 0.88rem;
            }

            .modern-nav-pill.active {
                background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%) !important;
                color: #ffffff !important;
                box-shadow: 0 6px 20px -3px rgba(79, 70, 229, 0.45) !important;
                font-weight: 600;
                border-color: rgba(255, 255, 255, 0.2) !important;
            }

            .modern-nav-pill:not(.active):hover {
                background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
                color: var(--bs-primary) !important;
                transform: translateX(4px);
            }

            .nav-category-header {
                font-size: 0.68rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.07em;
                color: var(--bs-secondary-color);
                padding: 10px 12px 4px 12px;
                margin-top: 6px;
                opacity: 0.85;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            /* Horizontal mobile scroll chips */
            .mobile-chips-container {
                display: flex;
                gap: 0.5rem;
                overflow-x: auto;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
                padding: 4px 2px;
                scrollbar-width: none;
                /* Firefox */
            }

            .mobile-chips-container::-webkit-scrollbar {
                display: none;
                /* Safari and Chrome */
            }

            .mobile-chip-btn {
                white-space: nowrap;
                flex-shrink: 0;
                border-radius: 50px;
                padding: 8px 16px;
                font-size: 0.82rem;
                font-weight: 600;
                border: 1px solid rgba(var(--bs-body-color-rgb), 0.12);
                background: rgba(var(--bs-body-bg-rgb), 0.85);
                color: var(--bs-body-color);
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .mobile-chip-btn.active {
                background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%) !important;
                color: #ffffff !important;
                border-color: transparent !important;
                box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            }

            .form-control:focus,
            .form-select:focus,
            .form-control-color:focus {
                border-color: #06b6d4 !important;
                box-shadow: 0 0 0 0.25rem rgba(6, 182, 212, 0.18) !important;
            }

            .form-section-title {
                position: relative;
                padding-bottom: 10px;
                margin-bottom: 22px;
                font-size: 1.15rem;
                font-weight: 700;
            }

            .form-section-title::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 45px;
                height: 3px;
                background: linear-gradient(135deg, #4f46e5, #06b6d4);
                border-radius: 4px;
            }

            .badge-pill-outline {
                border: 1px solid rgba(var(--bs-body-color-rgb), 0.15);
                background: transparent;
                font-size: 0.8rem;
                padding: 4px 12px;
                border-radius: 50px;
            }

            .cursor-pointer {
                cursor: pointer;
            }

            .pulse-badge {
                animation: pulse-dot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse-dot {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.4;
                    transform: scale(0.85);
                }
            }
        </style>

        <!-- Global Loading Overlay -->
        <div wire:loading.delay wire:target="save, clearCacheAction, storageLinkAction, backupDatabaseAction"
            class="position-fixed top-0 start-0 w-100 h-100 z-3"
            style="background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center bg-white dark:bg-dark p-4 rounded-4 shadow-lg text-dark dark:text-white"
                    style="min-width: 250px;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <h5 class="mb-0 fw-bold">{{ __('Saving Changes...') }}</h5>
                    <small class="text-muted">{{ __('Updating systems config, please wait') }}</small>
                </div>
            </div>
        </div>

        <x-slot name="header">
            {{ __('Main Site Setup') }}
        </x-slot>

        <!-- Top Action & Info Bar -->
        <div class="card border-0 shadow-sm mb-4 glass-card overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white shadow-sm"
                            style="width: 52px; height: 52px; background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);">
                            <i class="bi bi-sliders fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="mb-0 fw-bold text-dark">{{ __('Master Setup Control Center') }}</h4>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small">
                                    <i class="bi bi-gear-wide-connected me-1"></i> {{ __('Global Config') }}
                                </span>
                            </div>
                            <p class="text-muted small mb-0 d-none d-sm-block">
                                {{ __('Central hub for core branding, themes, billing logic, payment APIs, system security, and plugin extensions.') }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <button type="submit" form="masterSetupForm"
                            class="btn btn-primary px-3 px-md-4 py-2 fw-bold shadow-sm rounded-3 d-flex align-items-center gap-2">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-cloud-check-fill"></i> {{ __('Save All Changes') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                {{ __('Saving...') }}
                            </span>
                            <kbd class="d-none d-md-inline-block bg-white bg-opacity-25 text-white border-0 px-2 py-0.5 rounded small"
                                style="font-size: 0.68rem;">Ctrl+S</kbd>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Layout (Alpine Powered Tab Switcher) -->
        @php
            $tabErrorKeys = [
                'identity' => [
                    'data.site_name',
                    'data.portal_name',
                    'data.site_title',
                    'data.site_description',
                    'data.site_keywords',
                    'data.site_author',
                    'data.site_logo',
                    'data.site_icon',
                    'data.site_favicon',
                ],
                'theme' => [
                    'data.portal_theme_preset',
                    'data.theme_name',
                    'data.theme_primary_color',
                    'data.theme_accent_color',
                    'data.theme_font_family',
                    'data.theme_mode',
                    'data.theme_section_height',
                    'data.portal_primary_color',
                    'data.portal_accent_color',
                ],
                'contact' => [
                    'data.site_email',
                    'data.site_phone',
                    'data.site_address',
                    'data.site_map',
                    'data.site_whatsapp',
                    'data.site_facebook',
                    'data.site_twitter',
                    'data.site_instagram',
                    'data.site_linkedin',
                    'data.site_youtube',
                    'data.site_pinterest',
                ],
                'billing' => [
                    'data.site_invoice_prefix',
                    'data.customer_id_prefix',
                    'data.site_invoice_color',
                    'data.site_invoice_footer',
                    'data.site_invoice_notes',
                    'data.site_invoice_terms',
                    'data.site_invoice_logo',
                    'data.site_invoice_signature',
                ],
                'payment' => [],
                'security' => [
                    'data.site_secret_key',
                    'data.site_secret_value',
                    'data.site_secret_validity',
                    'data.site_secret_url',
                    'data.site_secret_email',
                ],
                'content' => [
                    'data.hero_title',
                    'data.hero_subtitle',
                    'data.hero_button_text',
                    'data.hero_button_link',
                    'data.registration_link',
                    'data.about_title',
                    'data.about_body',
                    'data.packages_section_title',
                    'data.testimonial_title',
                    'data.footer_copyright',
                ],
                'logs' => [
                    'data.mysql_binary_path',
                    'data.log_server_enabled',
                    'data.log_server_routers',
                    'data.log_retention_days',
                ],
            ];

            $hasTabError = function ($tab) use ($errors, $tabErrorKeys) {
                if (!isset($tabErrorKeys[$tab])) {
                    return false;
                }
                foreach ($tabErrorKeys[$tab] as $key) {
                    $rawKey = str_replace('data.', '', $key);
                    if ($errors->has($key) || $errors->has($rawKey)) {
                        return true;
                    }
                }
                return false;
            };
        @endphp

        <div x-data="{
            validTabs: ['identity', 'theme', 'contact', 'billing', 'payment', 'security', 'content', 'logs', 'utilities', 'plugins'],
            activeTab: 'identity',
            searchQuery: '',
            init() {
                const stored = localStorage.getItem('siteSetupActiveTab');
                this.activeTab = this.validTabs.includes(stored) ? stored : 'identity';
                this.$watch('activeTab', val => localStorage.setItem('siteSetupActiveTab', val));
            }
        }" @keydown.window.prevent.ctrl.s="$wire.save()"
            @keydown.window.prevent.cmd.s="$wire.save()">

            {{-- ── MOBILE ONLY: Horizontal Sticky Chips Navigation ──────────────── --}}
            <div class="d-lg-none sticky-top z-3 mb-3 p-2 rounded-4 glass-card shadow-sm" style="top: 10px;">
                <div class="mobile-chips-container">
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'identity' ? 'active' : ''"
                        @click="activeTab = 'identity'">
                        <i class="bi bi-globe"></i> {{ __('Identity') }}
                        @if ($hasTabError('identity'))
                            <span class="badge bg-danger rounded-circle p-1"></span>
                        @endif
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'theme' ? 'active' : ''"
                        @click="activeTab = 'theme'">
                        <i class="bi bi-palette"></i> {{ __('Theme') }}
                        @if ($hasTabError('theme'))
                            <span class="badge bg-danger rounded-circle p-1"></span>
                        @endif
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'contact' ? 'active' : ''"
                        @click="activeTab = 'contact'">
                        <i class="bi bi-telephone"></i> {{ __('Contact') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'billing' ? 'active' : ''"
                        @click="activeTab = 'billing'">
                        <i class="bi bi-receipt-cutoff"></i> {{ __('Billing') }}
                        @if ($hasTabError('billing'))
                            <span class="badge bg-danger rounded-circle p-1"></span>
                        @endif
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'payment' ? 'active' : ''"
                        @click="activeTab = 'payment'">
                        <i class="bi bi-credit-card-2-back"></i> {{ __('Gateways') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'security' ? 'active' : ''"
                        @click="activeTab = 'security'">
                        <i class="bi bi-shield-lock"></i> {{ __('Security') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'content' ? 'active' : ''"
                        @click="activeTab = 'content'">
                        <i class="bi bi-layout-text-window-reverse"></i> {{ __('Content') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'logs' ? 'active' : ''"
                        @click="activeTab = 'logs'">
                        <i class="bi bi-file-earmark-text"></i> {{ __('Logs') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'utilities' ? 'active' : ''"
                        @click="activeTab = 'utilities'">
                        <i class="bi bi-wrench-adjustable-circle"></i> {{ __('Database') }}
                    </button>
                    <button type="button" class="mobile-chip-btn" :class="activeTab === 'plugins' ? 'active' : ''"
                        @click="activeTab = 'plugins'">
                        <i class="bi bi-puzzle"></i> {{ __('Plugins') }}
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;">New</span>
                    </button>
                </div>
            </div>

            <div class="d-flex flex-column flex-lg-row gap-4 align-items-start">
                {{-- ── DESKTOP ONLY: Compact Fixed-Width Sleek Vertical Sidebar ────────────────────── --}}
                <div class="d-none d-lg-block flex-shrink-0" style="width: 280px;">
                    <div class="card border-0 shadow-sm p-3 sticky-top z-2 glass-card"
                        style="top: 80px; max-height: calc(100vh - 100px); overflow-y: auto;">

                        {{-- Quick Search Filter --}}
                        <div class="mb-2">
                            <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text bg-light border-0 pe-1 text-muted"><i
                                        class="bi bi-search"></i></span>
                                <input type="text" x-model="searchQuery"
                                    class="form-control bg-light border-0 small ps-2 shadow-none"
                                    placeholder="{{ __('Quick find setting...') }}" style="font-size: 0.8rem;">
                                <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                                    class="btn btn-sm btn-link text-muted p-0 pe-2 text-decoration-none">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                        </div>

                        <div class="nav flex-column gap-1" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">

                            {{-- ── CATEGORY 1: BRANDING & APPEARANCE ── --}}
                            <div class="nav-category-header"
                                x-show="!searchQuery || 'identity theme contact content'.includes(searchQuery.toLowerCase())">
                                <i class="bi bi-brush text-primary"></i> {{ __('Design & Branding') }}
                            </div>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'identity seo brand logo name'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'identity' ? 'active' : 'text-body'"
                                @click="activeTab = 'identity'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'identity' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-primary-subtle text-primary'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-globe"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Identity & SEO') }}</span>
                                @if ($hasTabError('identity'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'theme color style font design appearance'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'theme' ? 'active' : 'text-body'"
                                @click="activeTab = 'theme'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'theme' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-info-subtle text-info'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-palette"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Theme & Colors') }}</span>
                                @if ($hasTabError('theme'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'contact social phone email address facebook whatsapp'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'contact' ? 'active' : 'text-body'"
                                @click="activeTab = 'contact'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'contact' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-success-subtle text-success'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Contact & Socials') }}</span>
                                @if ($hasTabError('contact'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'content website slider hero service client'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'content' ? 'active' : 'text-body'"
                                @click="activeTab = 'content'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'content' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-secondary-subtle text-secondary'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-layout-text-window-reverse"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Website Content') }}</span>
                                @if ($hasTabError('content'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            {{-- ── CATEGORY 2: BILLING & COMMERCE ── --}}
                            <div class="nav-category-header mt-2"
                                x-show="!searchQuery || 'billing invoice payment gateway bkash nagad sslcommerz'.includes(searchQuery.toLowerCase())">
                                <i class="bi bi-cash-stack text-success"></i> {{ __('Billing & Finance') }}
                            </div>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'billing invoice prefix terms footer currency'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'billing' ? 'active' : 'text-body'"
                                @click="activeTab = 'billing'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'billing' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-primary-subtle text-primary'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-receipt-cutoff"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Billing & Invoice') }}</span>
                                @if ($hasTabError('billing'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'payment gateway bkash nagad rocket upay sslcommerz stripe paypal'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'payment' ? 'active' : 'text-body'"
                                @click="activeTab = 'payment'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'payment' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-warning-subtle text-warning'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-credit-card-2-back"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Payment Gateways') }}</span>
                                @if ($hasTabError('payment'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            {{-- ── CATEGORY 3: SECURITY & INFRASTRUCTURE ── --}}
                            <div class="nav-category-header mt-2"
                                x-show="!searchQuery || 'security secret token log syslog database utility'.includes(searchQuery.toLowerCase())">
                                <i class="bi bi-shield-check text-info"></i> {{ __('Security & Systems') }}
                            </div>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'security secret token api key'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'security' ? 'active' : 'text-body'"
                                @click="activeTab = 'security'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'security' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-danger-subtle text-danger'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-shield-lock"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Security & Secrets') }}</span>
                                @if ($hasTabError('security'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'log syslog retention router'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'logs' ? 'active' : 'text-body'"
                                @click="activeTab = 'logs'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'logs' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-secondary-subtle text-secondary'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Stored Logs') }}</span>
                                @if ($hasTabError('logs'))
                                    <span class="badge bg-danger rounded-circle p-1"
                                        style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                                @endif
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'database backup utility cache storage link'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'utilities' ? 'active' : 'text-body'"
                                @click="activeTab = 'utilities'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'utilities' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-dark-subtle text-dark'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-wrench-adjustable-circle"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('System Database') }}</span>
                                <i class="bi bi-chevron-right small opacity-50 ms-auto"></i>
                            </button>

                            {{-- ── CATEGORY 4: MODULAR EXTENSIONS ── --}}
                            <div class="nav-category-header mt-2"
                                x-show="!searchQuery || 'plugin extension addon module'.includes(searchQuery.toLowerCase())">
                                <i class="bi bi-puzzle text-warning"></i> {{ __('Extensions & Addons') }}
                            </div>

                            <button
                                class="nav-link modern-nav-pill text-start py-2 px-2.5 d-flex align-items-center gap-2 w-100"
                                x-show="!searchQuery || 'plugin extension addon olt gateway zip git composer'.includes(searchQuery.toLowerCase())"
                                :class="activeTab === 'plugins' ? 'active' : 'text-body'"
                                @click="activeTab = 'plugins'" type="button">
                                <span class="p-1.5 rounded-3 d-inline-flex align-items-center justify-content-center"
                                    :class="activeTab === 'plugins' ? 'bg-white bg-opacity-25 text-white' :
                                        'bg-primary-subtle text-primary'"
                                    style="width: 30px; height: 30px;">
                                    <i class="bi bi-puzzle"></i>
                                </span>
                                <span class="flex-grow-1 text-truncate">{{ __('Plugin Manager') }}</span>
                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 pulse-badge ms-auto"
                                    style="font-size: 0.65rem;">{{ __('New') }}</span>
                            </button>

                        </div>
                    </div>
                </div>

                <!-- Content Card Panel -->
                <div class="flex-grow-1 w-100" style="min-width: 0;">
                    <form wire:submit.prevent="save" id="masterSetupForm">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 p-3 d-flex align-items-start gap-3"
                                style="background: rgba(var(--bs-danger-rgb), 0.08);">
                                <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:36px; height:36px;">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading fw-bold mb-1 text-danger-emphasis">
                                        {{ __('Settings Validation Failed') }}</h6>
                                    <p class="text-danger-emphasis small mb-2">
                                        {{ __('Some fields are invalid. Please check the tabs marked with a red dot and correct the errors listed below:') }}
                                    </p>
                                    <ul class="mb-0 ps-3 small text-danger-emphasis">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if (session('success'))
                            <div
                                class="alert alert-success border-0 rounded-4 shadow-sm mb-4 p-3 d-flex align-items-center gap-3">
                                <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:36px; height:36px;">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading fw-bold mb-0 text-success-emphasis">
                                        {{ session('success') }}</h6>
                                </div>
                            </div>
                        @endif

                        <div class="card border-0 shadow-sm p-4 glass-card min-vh-50"
                            x-show="activeTab !== 'plugins'">

                            <!-- TAB 1: IDENTITY & SEO -->
                            <div x-show="activeTab === 'identity'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-info-circle-fill me-2"></i>{{ __('Core Brand Identity') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('App Name') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_name" required>
                                        @error('data.site_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Portal Name') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.portal_name" required>
                                        @error('data.portal_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">{{ __('Browser Title Tag') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_title">
                                        <small
                                            class="text-muted">{{ __('This appears in the browser tab and search results.') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Site Operational Status') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.site_status">
                                            <option value="active">{{ __('Active (Live)') }}</option>
                                            <option value="disabled">{{ __('Disabled (Offline)') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('System Language') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.site_locale">
                                            <option value="en">{{ __('English (US)') }}</option>
                                            <option value="bn">{{ __('Bengali (বাংলা)') }}</option>
                                        </select>
                                        @error('data.site_locale')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Main Site Language') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.main_site_locale">
                                            <option value="en">{{ __('English (US)') }}</option>
                                            <option value="bn">{{ __('Bengali (বাংলা)') }}</option>
                                        </select>
                                        @error('data.main_site_locale')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <div
                                            class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold d-block"
                                                    for="site_maintenance"
                                                    style="cursor: pointer;">{{ __('Maintenance Mode') }}</label>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ __('Block public website access and show a template warning.') }}</span>
                                            </div>
                                            <input class="form-check-input cursor-pointer me-2" type="checkbox"
                                                role="switch" id="site_maintenance"
                                                wire:model="data.site_maintenance" value="1">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">{{ __('System Announcement') }}</label>
                                        <textarea class="form-control rounded-3" rows="2"
                                            placeholder="{{ __('Tagline or warning message shown to portal users...') }}" wire:model="data.site_message"></textarea>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-person-lock me-2"></i>{{ __('Portal Access Settings') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div
                                            class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold d-block"
                                                    for="portal_registration_enabled"
                                                    style="cursor: pointer;">{{ __('Enable Client Registration') }}</label>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ __('Allow new signups on portal') }}</span>
                                            </div>
                                            <input class="form-check-input cursor-pointer me-2" type="checkbox"
                                                role="switch" id="portal_registration_enabled"
                                                wire:model="data.portal_registration_enabled" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div
                                            class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold d-block"
                                                    for="portal_change_password_enabled"
                                                    style="cursor: pointer;">{{ __('Enable Change Password') }}</label>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ __('Allow clients to modify their passwords') }}</span>
                                            </div>
                                            <input class="form-check-input cursor-pointer me-2" type="checkbox"
                                                role="switch" id="portal_change_password_enabled"
                                                wire:model="data.portal_change_password_enabled" value="1">
                                        </div>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-images me-2"></i>{{ __('Brand Assets & Media') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                        <label
                                            class="form-label fw-bold text-center border-bottom pb-2">{{ __('Main Site Logo') }}</label>
                                        <div wire:key="site_logo-wrapper">
                                            {{ $this->form->getComponent('site_logo') }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                        <label
                                            class="form-label fw-bold text-center border-bottom pb-2">{{ __('Square App Icon') }}</label>
                                        <div wire:key="site_icon-wrapper">
                                            {{ $this->form->getComponent('site_icon') }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                        <label
                                            class="form-label fw-bold text-center border-bottom pb-2">{{ __('Browser Favicon') }}</label>
                                        <div wire:key="site_favicon-wrapper">
                                            {{ $this->form->getComponent('site_favicon') }}
                                        </div>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-search me-2"></i>{{ __('Search Engine Optimization (SEO)') }}
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Meta Author') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_author"
                                            placeholder="{{ __('e.g. Your Company Name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Meta Keywords') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_keywords"
                                            placeholder="{{ __('fiber, broadband, ISP, Mikrotik...') }}">
                                        <small class="text-muted">{{ __('Separate with commas.') }}</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">{{ __('Meta Description') }}</label>
                                        <textarea class="form-control rounded-3" rows="3"
                                            placeholder="{{ __('Write a short summary description for search indexes...') }}"
                                            wire:model="data.site_description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: THEME & COLORS -->
                            <div x-show="activeTab === 'theme'" x-transition:enter.duration.200ms>

                                {{-- ── HEADER ── --}}
                                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                    <h5 class="text-primary fw-bold mb-0"><i
                                            class="bi bi-palette-fill me-2"></i>{{ __('Theme & Color Settings') }}
                                    </h5>
                                    <button type="button" wire:click="resetThemeSettings"
                                        wire:confirm="{{ __('Are you sure you want to reset all theme settings back to system defaults?') }}"
                                        class="btn btn-outline-danger btn-sm rounded-3 px-3 py-1 fw-bold">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        {{ __('Reset to Defaults') }}
                                    </button>
                                </div>

                                {{-- ── SECTION 1: CUSTOMER PORTAL ── --}}
                                <h6 class="text-uppercase text-muted fw-bold letter-spacing mb-3"
                                    style="font-size:.7rem; letter-spacing:.08em;">
                                    <i class="bi bi-person-circle me-1"></i>{{ __('Customer Portal Theme') }}
                                </h6>
                                <div x-data="{ portalPreset: '{{ $data['portal_theme_preset'] ?? 'indigo' }}' }">
                                    <div class="row g-3 mb-5">
                                        <div class="col-md-5">
                                            <label
                                                class="form-label fw-semibold">{{ __('Default Portal Color Preset') }}</label>
                                            <select class="form-select rounded-3"
                                                wire:model="data.portal_theme_preset"
                                                @change="portalPreset = $event.target.value">
                                                <option value="indigo">🟣 {{ __('Royal Purple (Indigo)') }}</option>
                                                <option value="emerald">🟢 {{ __('Forest Mint (Emerald)') }}</option>
                                                <option value="blue">🔵 {{ __('Ocean Breeze (Blue)') }}</option>
                                                <option value="orange">🟠 {{ __('Sunset Glow (Orange)') }}</option>
                                                <option value="dark">⚫ {{ __('Midnight Slate (Dark)') }}</option>
                                                <option value="custom">🎨 {{ __('Custom (Choose Your Own Colors)') }}
                                                </option>
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Controls the color of the customer portal login page and dashboard.') }}</small>
                                        </div>

                                        {{-- Custom Portal Color Pickers (only shown when "custom" selected) --}}
                                        <div class="col-md-7 align-items-center gap-4 border-start ps-4"
                                            x-show="portalPreset === 'custom'" x-cloak style="display: none;"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 transform -translate-x-2"
                                            x-transition:enter-end="opacity-100 transform translate-x-0">
                                            <div>
                                                <label
                                                    class="form-label fw-semibold mb-1">{{ __('Portal Primary') }}</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color"
                                                        class="form-control form-control-color border-0 p-0 rounded-circle"
                                                        style="width:38px; height:38px;"
                                                        wire:model="data.portal_primary_color">
                                                    <span
                                                        class="font-monospace text-uppercase text-muted small">{{ !empty($data['portal_primary_color']) ? $data['portal_primary_color'] : '#6366f1' }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="form-label fw-semibold mb-1">{{ __('Portal Accent') }}</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color"
                                                        class="form-control form-control-color border-0 p-0 rounded-circle"
                                                        style="width:38px; height:38px;"
                                                        wire:model="data.portal_accent_color">
                                                    <span
                                                        class="font-monospace text-uppercase text-muted small">{{ !empty($data['portal_accent_color']) ? $data['portal_accent_color'] : '#a78bfa' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 2: MAIN SITE ── --}}
                                <h6 class="text-uppercase text-muted fw-bold mb-3"
                                    style="font-size:.7rem; letter-spacing:.08em;">
                                    <i class="bi bi-globe me-1"></i>{{ __('Main Site (Landing Page) Customization') }}
                                </h6>

                                <div x-data="{
                                    siteTheme: '{{ $data['theme_name'] ?? 'default' }}',
                                    themes: {
                                        default: { primary: '#06ad73', secondary: '#ff0000' },
                                        emerald_isp: { primary: '#06ad73', secondary: '#ff6b35' },
                                        ocean_blue: { primary: '#0284c7', secondary: '#38bdf8' },
                                        midnight_purple: { primary: '#4f46e5', secondary: '#818cf8' },
                                        cyber_neon: { primary: '#00ffcc', secondary: '#ff007f' },
                                        rose_elegant: { primary: '#f43f5e', secondary: '#fda4af' },
                                        islamic_green: { primary: '#065f46', secondary: '#10b981' },
                                        golden_sunset: { primary: '#f59e0b', secondary: '#ef4444' },
                                        custom: { primary: null, secondary: null },
                                    },
                                    get colors() { return this.themes[this.siteTheme] || this.themes.default; }
                                }">

                                    {{-- Theme Preset Selector + Live Color Preview --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-7">
                                            <label
                                                class="form-label fw-semibold">{{ __('Site Color Theme') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select rounded-3 flex-grow-1"
                                                    wire:model="data.theme_name"
                                                    @change="siteTheme = $event.target.value">
                                                    <option value="default">⚙️ {{ __('Default (🟢/🔴)') }}</option>
                                                    <option value="emerald_isp">🌿 {{ __('Emerald ISP (🟢/🟠)') }}
                                                    </option>
                                                    <option value="ocean_blue">🌊 {{ __('Ocean Blue (🔵/🩵)') }}
                                                    </option>
                                                    <option value="midnight_purple">🌌
                                                        {{ __('Midnight Purple (🟣/💜)') }}</option>
                                                    <option value="cyber_neon">💻 {{ __('Cyber Neon (🩵/💖)') }}
                                                    </option>
                                                    <option value="rose_elegant">🌸 {{ __('Rose Elegant (🔴/🌸)') }}
                                                    </option>
                                                    <option value="islamic_green">☪️
                                                        {{ __('Islamic Green (🟢/💚)') }}</option>
                                                    <option value="golden_sunset">🌅
                                                        {{ __('Golden Sunset (🟡/🔴)') }}</option>
                                                    <option value="custom">🎨
                                                        {{ __('Custom (Choose Your Own Colors)') }}</option>
                                                </select>

                                                {{-- Inline Color Dots (hidden for custom) --}}
                                                <template x-if="siteTheme !== 'custom' && colors.primary">
                                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                        <span class="rounded-circle shadow-sm"
                                                            style="width:24px;height:24px;display:inline-block;border:2px solid rgba(255,255,255,.9);"
                                                            :style="'background:' + colors.primary"
                                                            :title="'Primary: ' + colors.primary"></span>
                                                        <span class="rounded-circle shadow-sm"
                                                            style="width:24px;height:24px;display:inline-block;border:2px solid rgba(255,255,255,.9);"
                                                            :style="'background:' + colors.secondary"
                                                            :title="'Secondary: ' + colors.secondary"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <small
                                                class="text-muted">{{ __('Select a preset or choose Custom to set your own colors.') }}</small>
                                        </div>

                                    </div>

                                    {{-- Custom Color Pickers — inside same x-data scope so siteTheme is accessible --}}
                                    <div class="row g-3 mb-4 p-3 rounded-3 border"
                                        style="background: rgba(var(--bs-primary-rgb, 99,102,241), 0.04); display: none;"
                                        x-show="siteTheme === 'custom'" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0">
                                        <p class="col-12 text-muted small mb-1"><i
                                                class="bi bi-info-circle me-1"></i>{{ __('These override the SASS default colors on the main site.') }}
                                        </p>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Primary Color') }}</label>
                                            <small
                                                class="d-block text-muted mb-1">{{ __('Replaces $primary-color in SASS') }}</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color"
                                                    class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:42px; height:42px;"
                                                    wire:model="data.theme_primary_color">
                                                <span
                                                    class="font-monospace text-uppercase text-muted small">{{ !empty($data['theme_primary_color']) ? $data['theme_primary_color'] : '#06ad73' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Secondary Color') }}</label>
                                            <small
                                                class="d-block text-muted mb-1">{{ __('Replaces $secondary-color in SASS') }}</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color"
                                                    class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:42px; height:42px;"
                                                    wire:model="data.theme_accent_color">
                                                <span
                                                    class="font-monospace text-uppercase text-muted small">{{ !empty($data['theme_accent_color']) ? $data['theme_accent_color'] : '#ff0000' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Font, Dark Mode, Section Height --}}
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Font Family') }}</label>
                                            <select class="form-select rounded-3" wire:model="data.theme_font_family">
                                                <option value="">
                                                    {{ __('Default (Outfit + Plus Jakarta Sans)') }}</option>
                                                <option value="Inter">Inter — {{ __('Modern Sans') }}</option>
                                                <option value="Outfit">Outfit — {{ __('Clean Geometric') }}</option>
                                                <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                                <option value="Figtree">Figtree — {{ __('Friendly') }}</option>
                                                <option value="Nunito">Nunito — {{ __('Rounded') }}</option>
                                                <option value="Playfair Display">Playfair Display —
                                                    {{ __('Serif') }}</option>
                                                <option value="Courier New">Courier New —
                                                    {{ __('Monospace / Cyber') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label
                                                class="form-label fw-semibold">{{ __('Site Dark/Light Mode') }}</label>
                                            <select class="form-select rounded-3" wire:model="data.theme_mode">
                                                <option value="">{{ __('Disabled (use SASS default)') }}
                                                </option>
                                                <option value="dark">🌙 {{ __('Force Dark Mode') }}</option>
                                                <option value="light">☀️ {{ __('Force Light Mode') }}</option>
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Overrides the default body background/text.') }}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Section Height') }}</label>
                                            <select class="form-select rounded-3"
                                                wire:model="data.theme_section_height">
                                                <option value="auto">{{ __('Content Height (auto)') }}</option>
                                                <option value="100vh">{{ __('Full Screen Height (100vh)') }}
                                                </option>
                                                <option value="80vh">{{ __('80% Screen Height (80vh)') }}</option>
                                                <option value="60vh">{{ __('60% Screen Height (60vh)') }}</option>
                                            </select>
                                            <small
                                                class="text-muted">{{ __('Controls the minimum height of all main sections.') }}</small>
                                        </div>
                                    </div>

                                </div>{{-- end x-data --}}
                            </div>

                            <!-- TAB 3: CONTACT & SOCIAL -->
                            <div x-show="activeTab === 'contact'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-building me-2"></i>{{ __('Office Contact Info') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Public Support Email') }}</label>
                                        <input type="email" class="form-control rounded-3"
                                            wire:model="data.site_email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Helpline Number') }}</label>
                                        <div wire:ignore>
                                            <input type="tel" id="site_phone" class="form-control rounded-3"
                                                x-data="intlTelInput('data.site_phone')" value="{{ $data['site_phone'] ?? '' }}">
                                        </div>
                                        @error('data.site_phone')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Physical Office Address') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_address">
                                    </div>
                                    <div class="col-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('Google Maps Embed URL') }}</label>
                                        <textarea class="form-control rounded-3" rows="2"
                                            placeholder="{{ __('Paste ONLY the src attribute of the iframe code from Google Maps...') }}"
                                            wire:model="data.site_map"></textarea>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-share me-2"></i>{{ __('Social Media Links') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Facebook Page/Profile') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_facebook" placeholder="facebook.com/username">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Twitter/X Handle') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_twitter" placeholder="@username">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Instagram Profile') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_instagram" placeholder="instagram.com/username">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('LinkedIn Page') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_linkedin" placeholder="linkedin.com/in/username">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('WhatsApp Business Number') }}</label>
                                        <div wire:ignore>
                                            <input type="tel" id="site_whatsapp" class="form-control rounded-3"
                                                x-data="intlTelInput('data.site_whatsapp')" value="{{ $data['site_whatsapp'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('YouTube Channel') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_youtube" placeholder="youtube.com/channel/...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Pinterest') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_pinterest">
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: BILLING & INVOICING -->
                            <div x-show="activeTab === 'billing'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-wallet2 me-2"></i>{{ __('Currency & Global Controls') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Currency Code') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_currency">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Invoice Number Prefix') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_invoice_prefix">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Customer ID Prefix') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.customer_id_prefix">
                                        @error('data.customer_id_prefix')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Grace Limit Amount') }}</label>
                                        <input type="number" class="form-control rounded-3"
                                            wire:model="data.disable_check_no">
                                        <small
                                            class="text-muted">{{ __('Maximum unpaid dues allowed before automatic disable.') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Grace Limit Days') }}</label>
                                        <input type="number" class="form-control rounded-3"
                                            wire:model="data.disable_check_days">
                                        <small
                                            class="text-muted">{{ __('Dues past invoice date before automatic disable.') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Expired Profile Name') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.expired_profile_name">
                                        <small
                                            class="text-muted">{{ __('Mikrotik redirection profile for expired users.') }}</small>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-file-earmark-pdf me-2"></i>{{ __('Invoice Document Layout Design') }}
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                        <label
                                            class="form-label fw-bold text-center border-bottom pb-2">{{ __('Invoice PDF Logo') }}</label>
                                        <div wire:key="site_invoice_logo-wrapper">
                                            {{ $this->form->getComponent('site_invoice_logo') }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                        <label
                                            class="form-label fw-bold text-center border-bottom pb-2">{{ __('Authorized Signature') }}</label>
                                        <div wire:key="site_invoice_signature-wrapper">
                                            {{ $this->form->getComponent('site_invoice_signature') }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div
                                            class="card p-3 shadow-none border bg-light bg-opacity-25 rounded-3 h-100">
                                            <label
                                                class="form-label fw-bold text-center border-bottom pb-2">{{ __('Document Primary Accent Color') }}</label>
                                            <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                                                <input type="color"
                                                    class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:45px; height:45px;"
                                                    wire:model="data.site_invoice_color">
                                                <span
                                                    class="font-monospace text-uppercase text-muted fs-6">{{ $data['site_invoice_color'] ?? '#000000' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">{{ __('Invoice Footer Text') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_invoice_footer"
                                            placeholder="{{ __('e.g. Thank you for choosing our fiber network services!') }}">
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label
                                            class="form-label fw-bold text-primary-emphasis mb-2">{{ __('Terms & Conditions (Rich Editor)') }}</label>
                                        <div class="border rounded-3 p-2 bg-white">
                                            <div wire:key="site_invoice_terms-wrapper">
                                                {{ $this->form->getComponent('site_invoice_terms') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 5: PAYMENT GATEWAYS (PayBridge Unified Hub) -->
                            <div x-show="activeTab === 'payment'" x-transition:enter.duration.200ms
                                x-data="{ categoryFilter: 'all' }">

                                <!-- Header & Stats -->
                                <div
                                    class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                                    <div>
                                        <h5 class="form-section-title text-primary fw-bold mb-1">
                                            <i
                                                class="bi bi-credit-card-2-front-fill me-2"></i>{{ __('Payment Gateway Management') }}
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            {{ __('Manage all 16 PayBridge gateways, live/test modes, default gateway, and API credentials directly from site settings.') }}
                                        </p>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        @if (isset($paybridgeGateways))
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-semibold">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                {{ $paybridgeGateways->where('is_active', true)->count() }}
                                                {{ __('Active') }}
                                            </span>
                                            <span
                                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-layers-fill me-1"></i>
                                                {{ $paybridgeGateways->count() }} {{ __('Total Gateways') }}
                                            </span>
                                            @php
                                                $defaultGw = $paybridgeGateways->firstWhere('is_default', true);
                                            @endphp
                                            @if ($defaultGw)
                                                <span
                                                    class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">
                                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                                    {{ __('Default:') }} {{ $defaultGw->name }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                @if (isset($paybridgeGateways))
                                    <!-- Category Filter Pills -->
                                    <div class="mb-4">
                                        <ul class="nav nav-pills flex-nowrap overflow-auto gap-1 pb-1" role="tablist">
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'all' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'all'">
                                                    <i class="bi bi-grid-fill me-1"></i> {{ __('All Gateways') }}
                                                    ({{ $paybridgeGateways->count() }})
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'MFS' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'MFS'">
                                                    <i class="bi bi-phone-fill me-1"></i>
                                                    {{ __('MFS / Mobile Banking') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'Aggregators' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'Aggregators'">
                                                    <i class="bi bi-shield-check me-1"></i> {{ __('Aggregators') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'International Cards' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'International Cards'">
                                                    <i class="bi bi-credit-card-fill me-1"></i>
                                                    {{ __('International Cards') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'Crypto' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'Crypto'">
                                                    <i class="bi bi-currency-bitcoin me-1"></i> {{ __('Crypto') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    class="nav-link rounded-pill px-3 py-1.5 text-nowrap cursor-pointer"
                                                    type="button"
                                                    :class="categoryFilter === 'Banks & QR' ? 'active shadow-sm' :
                                                        'text-body-secondary bg-light'"
                                                    @click="categoryFilter = 'Banks & QR'">
                                                    <i class="bi bi-qr-code-scan me-1"></i> {{ __('Banks & QR') }}
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Gateways Grid -->
                                    <div class="row g-3">
                                        @foreach ($paybridgeGateways as $gateway)
                                            @php
                                                $definition = $paybridgeDefinitions[$gateway->code] ?? [];
                                                $category = $definition['category'] ?? $gateway->category;

                                                $brandColor = 'primary';
                                                $brandIcon = 'bi-credit-card-2-front';
                                                if (str_contains($gateway->code, 'bkash')) {
                                                    $brandColor = 'danger';
                                                    $brandIcon = 'bi-wallet2';
                                                } elseif (str_contains($gateway->code, 'nagad')) {
                                                    $brandColor = 'warning';
                                                    $brandIcon = 'bi-cash-stack';
                                                } elseif (str_contains($gateway->code, 'rocket')) {
                                                    $brandColor = 'purple';
                                                    $brandIcon = 'bi-send';
                                                } elseif (str_contains($gateway->code, 'upay')) {
                                                    $brandColor = 'info';
                                                    $brandIcon = 'bi-phone';
                                                } elseif (str_contains($gateway->code, 'sslcommerz')) {
                                                    $brandColor = 'primary';
                                                    $brandIcon = 'bi-shield-check';
                                                } elseif (str_contains($gateway->code, 'stripe')) {
                                                    $brandColor = 'indigo';
                                                    $brandIcon = 'bi-stripe';
                                                } elseif (str_contains($gateway->code, 'paypal')) {
                                                    $brandColor = 'primary';
                                                    $brandIcon = 'bi-paypal';
                                                } elseif (
                                                    str_contains($gateway->code, 'binance') ||
                                                    str_contains($gateway->code, 'crypto') ||
                                                    str_contains($gateway->code, 'nowpayments')
                                                ) {
                                                    $brandColor = 'warning';
                                                    $brandIcon = 'bi-currency-bitcoin';
                                                } elseif (str_contains($gateway->code, 'qr')) {
                                                    $brandColor = 'success';
                                                    $brandIcon = 'bi-qr-code-scan';
                                                }
                                            @endphp

                                            <div class="col-12 col-md-6 col-xl-4"
                                                x-show="categoryFilter === 'all' || categoryFilter === '{{ $category }}'"
                                                x-transition:enter.duration.200ms>
                                                <div
                                                    class="card h-100 border shadow-sm transition-all rounded-3 position-relative {{ $gateway->is_active ? 'border-primary-subtle' : 'border-light-subtle opacity-75' }}">

                                                    <!-- Card Header -->
                                                    <div
                                                        class="card-header bg-transparent border-bottom-0 pb-0 pt-3 px-3 d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="avatar bg-{{ $brandColor }}-subtle text-{{ $brandColor }} rounded-2 d-flex align-items-center justify-content-center"
                                                                style="width: 38px; height: 38px;">
                                                                <i class="bi {{ $brandIcon }} fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-truncate"
                                                                    style="max-width: 160px;"
                                                                    title="{{ $gateway->name }}">
                                                                    {{ $gateway->name }}
                                                                </h6>
                                                                <span
                                                                    class="badge bg-secondary-subtle text-secondary py-0.5 px-2 text-[10px] rounded-pill">
                                                                    {{ $category }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- Livewire Toggle Switch -->
                                                        <div class="form-check form-switch form-switch-md mb-0">
                                                            <input class="form-check-input cursor-pointer"
                                                                type="checkbox" role="switch"
                                                                title="{{ $gateway->is_active ? __('Click to Disable') : __('Click to Enable') }}"
                                                                wire:click="togglePayBridgeGateway('{{ $gateway->code }}')"
                                                                {{ $gateway->is_active ? 'checked' : '' }}>
                                                        </div>
                                                    </div>

                                                    <!-- Card Body -->
                                                    <div class="card-body p-3">
                                                        <p class="text-body-secondary small mb-3 text-truncate-2"
                                                            style="min-height: 38px; font-size: 0.8rem;">
                                                            {{ $definition['description'] ?? __('Configure credentials to accept payments.') }}
                                                        </p>

                                                        <!-- Badges -->
                                                        <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">
                                                            @if ($gateway->is_active)
                                                                <span
                                                                    class="badge bg-success-subtle text-success py-1 px-2 rounded-pill font-semibold">
                                                                    <i class="bi bi-circle-fill me-1"
                                                                        style="font-size: 6px;"></i>{{ __('Active') }}
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge bg-secondary-subtle text-secondary py-1 px-2 rounded-pill">
                                                                    <i
                                                                        class="bi bi-dash-circle me-1"></i>{{ __('Inactive') }}
                                                                </span>
                                                            @endif

                                                            <button type="button"
                                                                wire:click="togglePayBridgeSandbox('{{ $gateway->code }}')"
                                                                class="badge border-0 py-1 px-2 rounded-pill cursor-pointer {{ $gateway->is_sandbox ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' }}"
                                                                title="{{ __('Click to toggle Sandbox / Live mode') }}">
                                                                <i
                                                                    class="bi {{ $gateway->is_sandbox ? 'bi-wrench-adjustable' : 'bi-broadcast' }} me-1"></i>
                                                                {{ $gateway->is_sandbox ? __('Sandbox') : __('Live') }}
                                                            </button>

                                                            @if ($gateway->is_default)
                                                                <button type="button"
                                                                    wire:click="setDefaultPayBridgeGateway('{{ $gateway->code }}')"
                                                                    class="badge bg-primary text-white border-0 py-1 px-2 rounded-pill cursor-pointer"
                                                                    title="{{ __('Click to turn off default') }}">
                                                                    <i
                                                                        class="bi bi-star-fill me-1"></i>{{ __('Default') }}
                                                                    <i class="bi bi-x-circle ms-1"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Card Footer -->
                                                    <div
                                                        class="card-footer bg-transparent border-top pt-2 pb-3 px-3 d-flex align-items-center justify-content-between gap-2">
                                                        @if (!$gateway->is_default)
                                                            <button type="button"
                                                                class="btn btn-link btn-sm text-secondary text-decoration-none p-0 fw-semibold"
                                                                wire:click="setDefaultPayBridgeGateway('{{ $gateway->code }}')">
                                                                <i
                                                                    class="bi bi-star me-1"></i>{{ __('Make Default') }}
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-bold"
                                                                wire:click="setDefaultPayBridgeGateway('{{ $gateway->code }}')"
                                                                title="{{ __('Click to turn off default') }}">
                                                                <i
                                                                    class="bi bi-check-all me-1"></i>{{ __('Primary Default') }}
                                                                <i class="bi bi-x-circle text-muted ms-1 small"></i>
                                                            </button>
                                                        @endif

                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modal-gw-{{ $gateway->code }}">
                                                            <i class="bi bi-gear-fill me-1"></i>{{ __('Configure') }}
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning border-0 rounded-3 p-3">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        {{ __('PayBridge gateways could not be loaded.') }}
                                    </div>
                                @endif

                            </div>

                            <!-- TAB 6: SECURITY & SECRETS -->
                            <div x-show="activeTab === 'security'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-key-fill me-2"></i>{{ __('Site Secret Credentials') }}</h5>
                                <p class="text-muted small mb-4">
                                    {{ __("System-wide credentials used for machine-to-machine integrations, remote server sync, and verifying third-party webhook callbacks (e.g., payment status updates). For standard client-level or user integrations, please utilize Jetstream's built-in personal API tokens instead.") }}
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('API Secret Key') }}</label>
                                        <input type="password" class="form-control rounded-3"
                                            wire:model="data.site_secret_key">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('API Secret Value') }}</label>
                                        <input type="password" class="form-control rounded-3"
                                            wire:model="data.site_secret_value">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Validity Threshold') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.site_secret_validity"
                                            placeholder="{{ __('e.g. 365 days') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Notification Destination URL') }}</label>
                                        <input type="url" class="form-control rounded-3"
                                            wire:model="data.site_secret_url">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('Notification Email Address') }}</label>
                                        <input type="email" class="form-control rounded-3"
                                            wire:model="data.site_secret_email">
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-folder-symlink me-2"></i>{{ __('Local System Database Binary Path') }}
                                </h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('MySQL/MariaDB Dump CLI Path') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.mysql_binary_path"
                                            placeholder="{{ __('e.g. C:\laragon\bin\mysql\mysql-8.0\bin\\') }}">
                                        <small
                                            class="text-muted">{{ __('Must include a trailing slash! Required if database backup fails due to missing command in server PATH env.') }}</small>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-activity me-2"></i>{{ __('MikroTik Logger Operations') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div
                                            class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold d-block"
                                                    for="log_server_enabled"
                                                    style="cursor: pointer;">{{ __('Enable Remote Log Streaming') }}</label>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ __('Enable log daemon listener on background worker') }}</span>
                                            </div>
                                            <input class="form-check-input cursor-pointer me-2" type="checkbox"
                                                role="switch" id="log_server_enabled"
                                                wire:model="data.log_server_enabled" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Auto-Delete Logs After (Days)') }}</label>
                                        <input type="number" class="form-control rounded-3"
                                            wire:model="data.log_retention_days">
                                        <small
                                            class="text-muted">{{ __('Logs older than this threshold will be deleted automatically.') }}</small>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label
                                            class="form-label fw-bold text-primary-emphasis mb-2">{{ __('Capture logs for selected routers:') }}</label>
                                        <div class="p-2 border rounded-3 bg-white">
                                            <div wire:key="log_server_routers-wrapper">
                                                {{ $this->form->getComponent('log_server_routers') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        @include('livewire.mikrotik.log-stats-embed')
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 7: WEBSITE CONTENT -->
                            <div x-show="activeTab === 'content'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-window-sidebar me-2"></i>{{ __('Landing Page Hero Panel') }}
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Main Slider Headline') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.hero_title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Slider Sub-headline') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.hero_subtitle">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Action Button Label') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.hero_button_text">
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Action Button Link (URL)') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.hero_button_link">
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-briefcase me-2"></i>{{ __('Company Modules & Services') }}</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('About Company Section Title') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.about_title">
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Packages Section Title') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.packages_section_title">
                                    </div>
                                    <div class="col-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('About Company Brief Summary') }}</label>
                                        <textarea class="form-control rounded-3" rows="3" wire:model="data.about_body"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('Customer Registration Link Override') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.registration_link">
                                    </div>
                                    <div class="col-md-6">
                                        <div
                                            class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-50 mt-4">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold d-block" for="is_active"
                                                    style="cursor: pointer;">{{ __('Public Website Visible') }}</label>
                                                <span class="text-muted"
                                                    style="font-size: 0.75rem;">{{ __('Show/Hide public frontend site') }}</span>
                                            </div>
                                            <input class="form-check-input cursor-pointer me-2" type="checkbox"
                                                role="switch" id="is_active" wire:model="data.is_active"
                                                value="1">
                                        </div>
                                    </div>
                                </div>

                                <h5 class="form-section-title text-primary fw-bold mt-5"><i
                                        class="bi bi-c-circle me-2"></i>{{ __('Footer Section Layout') }}</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('Copyright Footer Text') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.footer_copyright"
                                            placeholder="{{ __('e.g. © 2024 Your Company name. All rights reserved.') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('BTCL Tariff Link (Manual URL)') }}</label>
                                        <input type="text" class="form-control rounded-3"
                                            wire:model="data.btcl_tariff_link"
                                            placeholder="{{ __('e.g. /pdf/btcl_tariff.pdf or #') }}">
                                    </div>
                                </div>

                                {{-- Filament-managed repeaters & BTCL PDF upload - rendered as a full form for proper Alpine.js context --}}
                                <div class="mt-4">
                                    {{ $this->contentForm }}
                                </div>
                            </div>

                            <!-- TAB 8: STORED LOGS -->
                            <div x-show="activeTab === 'logs'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-card-text me-2"></i>{{ __('Router Log Data Archive') }}</h5>
                                @include('livewire.mikrotik.log-table-master-embed')
                            </div>

                            <!-- TAB 9: UTILITIES & SYSTEM DATABASE -->
                            <div x-show="activeTab === 'utilities'" x-transition:enter.duration.200ms>
                                <h5 class="form-section-title text-primary fw-bold"><i
                                        class="bi bi-box-seam me-2"></i>{{ __('System database Snapshots') }}</h5>
                                @include('livewire.mikrotik.system-utilities-embed')
                            </div>

                            <!-- Sticky Footer inside tab pane for instant access -->
                            <div class="border-top mt-4 pt-3 text-end d-md-none">
                                <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm rounded-3">
                                    <i class="bi bi-check2-circle me-1"></i> {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- TAB 10: PLUGIN MANAGER (Rendered outside masterSetupForm so forms and uploads do not conflict) -->
                    <div x-show="activeTab === 'plugins'" x-transition:enter.duration.200ms>
                        <div class="card border-0 shadow-sm p-2 glass-card">
                            @livewire('admin.plugin-manager')
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── MOBILE ONLY: Floating Bottom Action Bar ────────────────────────── --}}
            <div class="d-lg-none position-fixed bottom-0 start-0 end-0 p-3 z-3 bg-white bg-opacity-90 border-top shadow-lg d-flex align-items-center justify-content-between"
                style="backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);"
                x-show="activeTab !== 'plugins'">
                <div class="small fw-semibold text-muted d-flex align-items-center gap-1.5">
                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1">
                        <i class="bi bi-sliders"></i> Master Setup
                    </span>
                </div>
                <button type="submit" form="masterSetupForm"
                    class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow d-flex align-items-center gap-1.5">
                    <span wire:loading.remove wire:target="save">
                        <i class="bi bi-cloud-check-fill"></i> {{ __('Save Changes') }}
                    </span>
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                </button>
            </div>
        </div>

        <!-- PAYBRIDGE CONFIGURATION MODALS (Rendered outside master form) -->
        @if (isset($paybridgeGateways))
            @foreach ($paybridgeGateways as $gateway)
                @php
                    $definition = $paybridgeDefinitions[$gateway->code] ?? [];
                    $fields = $definition['fields'] ?? [];
                    $brandColor = 'primary';
                    if (str_contains($gateway->code, 'bkash')) {
                        $brandColor = 'danger';
                    } elseif (str_contains($gateway->code, 'nagad')) {
                        $brandColor = 'warning';
                    } elseif (str_contains($gateway->code, 'rocket')) {
                        $brandColor = 'purple';
                    } elseif (str_contains($gateway->code, 'upay')) {
                        $brandColor = 'info';
                    } elseif (str_contains($gateway->code, 'sslcommerz')) {
                        $brandColor = 'primary';
                    } elseif (str_contains($gateway->code, 'stripe')) {
                        $brandColor = 'indigo';
                    } elseif (str_contains($gateway->code, 'paypal')) {
                        $brandColor = 'primary';
                    } elseif (str_contains($gateway->code, 'binance') || str_contains($gateway->code, 'crypto')) {
                        $brandColor = 'warning';
                    } elseif (str_contains($gateway->code, 'qr')) {
                        $brandColor = 'success';
                    }
                @endphp

                <div class="modal fade" id="modal-gw-{{ $gateway->code }}" tabindex="-1"
                    aria-labelledby="modalLabel-{{ $gateway->code }}" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form action="{{ route('paybridge.admin.update', $gateway->code) }}" method="POST">
                                @csrf

                                <div class="modal-header border-bottom px-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-{{ $brandColor }}-subtle text-{{ $brandColor }} rounded-3 d-flex align-items-center justify-content-center"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-gear-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold mb-0"
                                                id="modalLabel-{{ $gateway->code }}">
                                                {{ __('Configure') }} {{ $gateway->name }}
                                            </h5>
                                            <small
                                                class="text-muted">{{ $definition['category'] ?? $gateway->category }}</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body p-4">
                                    <!-- Status switches -->
                                    <div class="row g-3 mb-4 p-3 bg-light bg-opacity-50 rounded-3 border">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input cursor-pointer" type="checkbox"
                                                    name="is_active" value="1"
                                                    id="active-{{ $gateway->code }}"
                                                    {{ $gateway->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold"
                                                    for="active-{{ $gateway->code }}">
                                                    {{ __('Enable Gateway') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input cursor-pointer" type="checkbox"
                                                    name="is_sandbox" value="1"
                                                    id="sandbox-{{ $gateway->code }}"
                                                    {{ $gateway->is_sandbox ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold"
                                                    for="sandbox-{{ $gateway->code }}">
                                                    {{ __('Sandbox (Test Mode)') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input cursor-pointer" type="checkbox"
                                                    name="is_default" value="1"
                                                    id="default-{{ $gateway->code }}"
                                                    {{ $gateway->is_default ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold"
                                                    for="default-{{ $gateway->code }}">
                                                    {{ __('Primary Default') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dynamic Credential Fields -->
                                    <h6 class="fw-bold mb-3 text-primary"><i
                                            class="bi bi-key me-1"></i>{{ __('API Credentials & Security') }}</h6>

                                    @if (empty($fields))
                                        <div class="alert alert-info border-0 rounded-3 py-2 small">
                                            <i class="bi bi-info-circle me-1"></i>
                                            {{ __('No custom credentials required for this gateway.') }}
                                        </div>
                                    @else
                                        <div class="row g-3">
                                            @foreach ($fields as $fieldName => $fieldMeta)
                                                @php
                                                    $fieldVal = $gateway->credentials[$fieldName] ?? '';
                                                    $fieldType = $fieldMeta['type'] ?? 'text';
                                                    $isPassword = $fieldType === 'password';
                                                    $fieldPlaceholder = !empty($fieldMeta['placeholder'])
                                                        ? $fieldMeta['placeholder']
                                                        : match ($fieldName) {
                                                            'app_key' => 'e.g. 5c98a3b2b... or App Key',
                                                            'app_secret' => 'Enter App Secret Key',
                                                            'username' => 'e.g. merchant_username',
                                                            'password' => 'Enter Password',
                                                            'merchant_id' => 'e.g. 683002007104225',
                                                            'merchant_number' => 'e.g. 017xxxxxxxx',
                                                            'public_key' => "-----BEGIN PUBLIC KEY-----\n...",
                                                            'private_key' => "-----BEGIN RSA PRIVATE KEY-----\n...",
                                                            'merchant_key' => 'Enter Merchant Key / Secret',
                                                            'merchant_password' => 'Enter Merchant Password',
                                                            'store_id' => 'e.g. testbox or Store ID',
                                                            'store_password' => 'Enter Store Password',
                                                            'signature_key' => 'Enter Signature Key',
                                                            'secret_key' => 'Enter Secret Key',
                                                            'merchant_name' => 'e.g. ' .
                                                                config('app.name', 'My Business'),
                                                            'merchant_prefix' => 'e.g. TXN',
                                                            'client_id' => 'e.g. PayPal Client ID',
                                                            'secret' => 'Enter Secret',
                                                            'webhook_secret' => 'e.g. whsec_...',
                                                            'api_key' => 'e.g. API Key',
                                                            'ipn_secret' => 'Enter IPN Secret',
                                                            'terminal_id' => 'e.g. TID00001',
                                                            'acquirer_bin' => 'e.g. 000001',
                                                            'mcc' => 'e.g. 5411',
                                                            'merchant_city' => 'e.g. Dhaka',
                                                            default => $fieldMeta['default'] ??
                                                                'Enter ' .
                                                                    ($fieldMeta['label'] ??
                                                                        ucfirst(str_replace('_', ' ', $fieldName))),
                                                        };
                                                @endphp
                                                <div
                                                    class="col-12 {{ in_array($fieldType, ['textarea']) ? 'col-md-12' : 'col-md-6' }}">
                                                    <label
                                                        class="form-label fw-semibold d-flex align-items-center justify-content-between"
                                                        for="cred-{{ $gateway->code }}-{{ $fieldName }}">
                                                        <span>
                                                            {{ $fieldMeta['label'] ?? ucfirst(str_replace('_', ' ', $fieldName)) }}
                                                            @if (!empty($fieldMeta['required']))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </span>
                                                        @if ($isPassword && !empty($fieldVal))
                                                            <span
                                                                class="badge bg-success-subtle text-success text-[10px]">{{ __('Saved (Encrypted)') }}</span>
                                                        @endif
                                                    </label>

                                                    @if ($fieldType === 'textarea')
                                                        <textarea class="form-control rounded-3 font-monospace" style="font-size: 0.8rem;" rows="3"
                                                            id="cred-{{ $gateway->code }}-{{ $fieldName }}" name="credentials[{{ $fieldName }}]"
                                                            placeholder="{{ $fieldPlaceholder }}">{{ $fieldVal }}</textarea>
                                                    @elseif($fieldType === 'select')
                                                        <select class="form-select rounded-3"
                                                            id="cred-{{ $gateway->code }}-{{ $fieldName }}"
                                                            name="credentials[{{ $fieldName }}]">
                                                            @foreach ($fieldMeta['options'] ?? [] as $optVal => $optLabel)
                                                                <option value="{{ $optVal }}"
                                                                    {{ $fieldVal == $optVal ? 'selected' : '' }}>
                                                                    {{ $optLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($isPassword)
                                                        <div class="input-group">
                                                            <input type="password"
                                                                class="form-control rounded-start-3"
                                                                id="cred-{{ $gateway->code }}-{{ $fieldName }}"
                                                                name="credentials[{{ $fieldName }}]"
                                                                placeholder="{{ !empty($fieldVal) ? '•••••••••••••••• (Leave blank to keep current)' : $fieldPlaceholder }}">
                                                            <button class="btn btn-outline-secondary rounded-end-3"
                                                                type="button"
                                                                onclick="const el = document.getElementById('cred-{{ $gateway->code }}-{{ $fieldName }}'); el.type = el.type === 'password' ? 'text' : 'password';">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <input type="text" class="form-control rounded-3"
                                                            id="cred-{{ $gateway->code }}-{{ $fieldName }}"
                                                            name="credentials[{{ $fieldName }}]"
                                                            value="{{ $fieldVal }}"
                                                            placeholder="{{ $fieldPlaceholder }}">
                                                    @endif

                                                    @if (!empty($fieldMeta['description']))
                                                        <small class="text-muted d-block mt-1"
                                                            style="font-size: 0.75rem;">
                                                            {{ $fieldMeta['description'] }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Webhook & Callback Endpoints -->
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-bold mb-2 small text-muted"><i
                                                class="bi bi-link-45deg me-1"></i>{{ __('System Endpoints (Auto-configured)') }}
                                        </h6>
                                        <div
                                            class="p-2.5 bg-body-tertiary rounded-3 small font-monospace d-flex align-items-center justify-content-between mb-2">
                                            <div><span class="badge bg-secondary me-1">Callback:</span>
                                                {{ url('/payment/' . $gateway->code . '/callback') }}</div>
                                            <button type="button"
                                                class="btn btn-link btn-sm p-0 text-decoration-none"
                                                onclick="navigator.clipboard.writeText('{{ url('/payment/' . $gateway->code . '/callback') }}'); alert('Copied!');">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                        <div
                                            class="p-2.5 bg-body-tertiary rounded-3 small font-monospace d-flex align-items-center justify-content-between">
                                            <div><span class="badge bg-secondary me-1">Webhook:</span>
                                                {{ url('/payment/' . $gateway->code . '/webhook') }}</div>
                                            <button type="button"
                                                class="btn btn-link btn-sm p-0 text-decoration-none"
                                                onclick="navigator.clipboard.writeText('{{ url('/payment/' . $gateway->code . '/webhook') }}'); alert('Copied!');">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer border-top px-4 py-3">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> {{ __('Save Configuration') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        <x-filament-actions::modals />
    </div>
