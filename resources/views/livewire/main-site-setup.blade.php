<div class="zoom-in position-relative">
    <!-- Style tweaks for a premium look -->
    <style>
        .glass-card {
            background: rgba(var(--bs-body-bg-rgb), 0.6) !important;
            backdrop-filter: blur(16px);
            border: 1px solid rgba(var(--bs-body-color-rgb), 0.08) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            border-radius: 16px;
        }
        .nav-pills .nav-link {
            transition: all 0.2s ease-in-out;
            border-radius: 12px;
            font-weight: 600;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0) !important;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.25) !important;
            color: #ffffff !important;
        }
        .nav-pills .nav-link:not(.active):hover {
            background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
            color: var(--bs-primary) !important;
        }
        .form-control:focus, .form-select:focus, .form-control-color:focus {
            border-color: #0dcaf0 !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.15) !important;
        }
        .form-section-title {
            position: relative;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .form-section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border-radius: 3px;
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
    </style>

    <!-- Global Loading Overlay -->
    <div wire:loading.delay wire:target="save, clearCacheAction, storageLinkAction, backupDatabaseAction" 
         class="position-fixed top-0 start-0 w-100 h-100 z-3" 
         style="background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center bg-white dark:bg-dark p-4 rounded-4 shadow-lg text-dark dark:text-white" style="min-width: 250px;">
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
    <div class="card border-0 shadow-sm mb-4 glass-card">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="mb-1 fw-extrabold text-primary-emphasis d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-primary"></i> {{ __('Master Setup Control Center') }}
                    </h4>
                    <p class="text-muted small mb-0">{{ __('Manage global settings, layouts, theme customization, payment APIs, and dynamic landing page content.') }}</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="submit" form="masterSetupForm" class="btn btn-primary px-4 fw-bold shadow-sm rounded-3">
                        <span wire:loading.remove wire:target="save"><i class="bi bi-check2-circle me-1"></i> {{ __('SAVE ALL CHANGES') }}</span>
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Layout (Alpine Powered Tab Switcher) -->
    @php
        $tabErrorKeys = [
            'identity' => ['data.site_name', 'data.portal_name', 'data.site_title', 'data.site_description', 'data.site_keywords', 'data.site_author', 'data.site_logo', 'data.site_icon', 'data.site_favicon'],
            'theme'    => ['data.portal_theme_preset', 'data.theme_name', 'data.theme_primary_color', 'data.theme_accent_color', 'data.theme_font_family', 'data.theme_mode', 'data.theme_section_height', 'data.portal_primary_color', 'data.portal_accent_color'],
            'contact'  => ['data.site_email', 'data.site_phone', 'data.site_address', 'data.site_map', 'data.site_whatsapp', 'data.site_facebook', 'data.site_twitter', 'data.site_instagram', 'data.site_linkedin', 'data.site_youtube', 'data.site_pinterest'],
            'billing'  => ['data.site_invoice_prefix', 'data.customer_id_prefix', 'data.site_invoice_color', 'data.site_invoice_footer', 'data.site_invoice_notes', 'data.site_invoice_terms', 'data.site_invoice_logo', 'data.site_invoice_signature'],
            'payment'  => ['data.payment_bkash_enabled', 'data.payment_bkash_base_url', 'data.payment_bkash_username', 'data.payment_bkash_password', 'data.payment_bkash_app_key', 'data.payment_bkash_app_secret', 'data.payment_nagad_enabled', 'data.payment_nagad_base_url', 'data.payment_nagad_merchant_id', 'data.payment_nagad_public_key', 'data.payment_nagad_private_key', 'data.payment_sslcommerz_enabled', 'data.payment_sslcommerz_store_id', 'data.payment_sslcommerz_store_password', 'data.payment_sslcommerz_sandbox'],
            'security' => ['data.site_secret_key', 'data.site_secret_value', 'data.site_secret_validity', 'data.site_secret_url', 'data.site_secret_email'],
            'content'  => ['data.hero_title', 'data.hero_subtitle', 'data.hero_button_text', 'data.hero_button_link', 'data.registration_link', 'data.about_title', 'data.about_body', 'data.packages_section_title', 'data.testimonial_title', 'data.footer_copyright'],
            'logs'     => ['data.mysql_binary_path', 'data.log_server_enabled', 'data.log_server_routers', 'data.log_retention_days']
        ];

        $hasTabError = function($tab) use ($errors, $tabErrorKeys) {
            if (!isset($tabErrorKeys[$tab])) return false;
            foreach ($tabErrorKeys[$tab] as $key) {
                $rawKey = str_replace('data.', '', $key);
                if ($errors->has($key) || $errors->has($rawKey)) return true;
            }
            return false;
        };
    @endphp

    <div x-data="{ activeTab: localStorage.getItem('siteSetupActiveTab') || 'identity' }" 
         x-init="$watch('activeTab', val => localStorage.setItem('siteSetupActiveTab', val))">
        
        <div class="row g-4">
            <!-- Sidebar navigation panel -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 sticky-top glass-card" style="top: 20px; ">
                    <div class="nav flex-column nav-pills gap-1" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'identity' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'identity'" type="button">
                            <i class="bi bi-globe fs-5"></i>
                            <span class="flex-grow-1">{{ __('Identity & SEO') }}</span>
                            @if ($hasTabError('identity'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'theme' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'theme'" type="button">
                            <i class="bi bi-palette fs-5"></i>
                            <span class="flex-grow-1">{{ __('Theme & Colors') }}</span>
                            @if ($hasTabError('theme'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'contact' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'contact'" type="button">
                            <i class="bi bi-telephone fs-5"></i>
                            <span class="flex-grow-1">{{ __('Contact & Socials') }}</span>
                            @if ($hasTabError('contact'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'billing' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'billing'" type="button">
                            <i class="bi bi-receipt-cutoff fs-5"></i>
                            <span class="flex-grow-1">{{ __('Billing & Invoice') }}</span>
                            @if ($hasTabError('billing'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'payment' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'payment'" type="button">
                            <i class="bi bi-credit-card-2-back fs-5"></i>
                            <span class="flex-grow-1">{{ __('Payment Gateways') }}</span>
                            @if ($hasTabError('payment'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'security' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'security'" type="button">
                            <i class="bi bi-shield-lock fs-5"></i>
                            <span class="flex-grow-1">{{ __('Security & Secrets') }}</span>
                            @if ($hasTabError('security'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'content' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'content'" type="button">
                            <i class="bi bi-layout-text-window-reverse fs-5"></i>
                            <span class="flex-grow-1">{{ __('Website Content') }}</span>
                            @if ($hasTabError('content'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'logs' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'logs'" type="button">
                            <i class="bi bi-file-earmark-text fs-5"></i>
                            <span class="flex-grow-1">{{ __('Stored Logs') }}</span>
                            @if ($hasTabError('logs'))
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; flex-shrink: 0;" title="Has errors"></span>
                            @endif
                        </button>
                        <button class="nav-link text-start py-2.5 px-3 d-flex align-items-center gap-3 w-100" 
                                :class="activeTab === 'utilities' ? 'active shadow' : 'text-body hover-bg'"
                                @click="activeTab = 'utilities'" type="button">
                            <i class="bi bi-wrench-adjustable-circle fs-5"></i>
                            <span class="flex-grow-1">{{ __('System Database') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Card Panel -->
            <div class="col-md-9">
                <form wire:submit.prevent="save" id="masterSetupForm">
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 p-3 d-flex align-items-start gap-3" style="background: rgba(var(--bs-danger-rgb), 0.08);">
                            <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px;">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading fw-bold mb-1 text-danger-emphasis">{{ __('Settings Validation Failed') }}</h6>
                                <p class="text-danger-emphasis small mb-2">{{ __('Some fields are invalid. Please check the tabs marked with a red dot and correct the errors listed below:') }}</p>
                                <ul class="mb-0 ps-3 small text-danger-emphasis">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm p-4 glass-card min-vh-50">
                        
                        <!-- TAB 1: IDENTITY & SEO -->
                        <div x-show="activeTab === 'identity'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-info-circle-fill me-2"></i>{{ __('Core Brand Identity') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('App Name') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_name" required>
                                    @error('data.site_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Portal Name') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.portal_name" required>
                                    @error('data.portal_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('Browser Title Tag') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_title">
                                    <small class="text-muted">{{ __('This appears in the browser tab and search results.') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Site Operational Status') }}</label>
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
                                    @error('data.site_locale') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Main Site Language') }}</label>
                                    <select class="form-select rounded-3" wire:model="data.main_site_locale">
                                        <option value="en">{{ __('English (US)') }}</option>
                                        <option value="bn">{{ __('Bengali (বাংলা)') }}</option>
                                    </select>
                                    @error('data.main_site_locale') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                        <div class="ms-2">
                                            <label class="form-check-label fw-semibold d-block" for="site_maintenance" style="cursor: pointer;">{{ __('Maintenance Mode') }}</label>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ __('Block public website access and show a template warning.') }}</span>
                                        </div>
                                        <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="site_maintenance" wire:model="data.site_maintenance" value="1">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('System Announcement') }}</label>
                                    <textarea class="form-control rounded-3" rows="2" placeholder="{{ __('Tagline or warning message shown to portal users...') }}" wire:model="data.site_message"></textarea>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-person-lock me-2"></i>{{ __('Portal Access Settings') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                        <div class="ms-2">
                                            <label class="form-check-label fw-semibold d-block" for="portal_registration_enabled" style="cursor: pointer;">{{ __('Enable Client Registration') }}</label>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ __('Allow new signups on portal') }}</span>
                                        </div>
                                        <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="portal_registration_enabled" wire:model="data.portal_registration_enabled" value="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                        <div class="ms-2">
                                            <label class="form-check-label fw-semibold d-block" for="portal_change_password_enabled" style="cursor: pointer;">{{ __('Enable Change Password') }}</label>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ __('Allow clients to modify their passwords') }}</span>
                                        </div>
                                        <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="portal_change_password_enabled" wire:model="data.portal_change_password_enabled" value="1">
                                    </div>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-images me-2"></i>{{ __('Brand Assets & Media') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                    <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Main Site Logo') }}</label>
                                    <div wire:key="site_logo-wrapper">
                                        {{ $this->form->getComponent('site_logo') }}
                                    </div>
                                </div>
                                <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                    <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Square App Icon') }}</label>
                                    <div wire:key="site_icon-wrapper">
                                        {{ $this->form->getComponent('site_icon') }}
                                    </div>
                                </div>
                                <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                    <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Browser Favicon') }}</label>
                                    <div wire:key="site_favicon-wrapper">
                                        {{ $this->form->getComponent('site_favicon') }}
                                    </div>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-search me-2"></i>{{ __('Search Engine Optimization (SEO)') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Meta Author') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_author" placeholder="{{ __('e.g. Your Company Name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Meta Keywords') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_keywords" placeholder="{{ __('fiber, broadband, ISP, Mikrotik...') }}">
                                    <small class="text-muted">{{ __('Separate with commas.') }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Meta Description') }}</label>
                                    <textarea class="form-control rounded-3" rows="3" placeholder="{{ __('Write a short summary description for search indexes...') }}" wire:model="data.site_description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: THEME & COLORS -->
                        <div x-show="activeTab === 'theme'" x-transition:enter.duration.200ms>

                            {{-- ── HEADER ── --}}
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                <h5 class="text-primary fw-bold mb-0"><i class="bi bi-palette-fill me-2"></i>{{ __('Theme & Color Settings') }}</h5>
                                <button type="button" wire:click="resetThemeSettings"
                                    wire:confirm="{{ __('Are you sure you want to reset all theme settings back to system defaults?') }}"
                                    class="btn btn-outline-danger btn-sm rounded-3 px-3 py-1 fw-bold">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reset to Defaults') }}
                                </button>
                            </div>

                            {{-- ── SECTION 1: CUSTOMER PORTAL ── --}}
                            <h6 class="text-uppercase text-muted fw-bold letter-spacing mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                                <i class="bi bi-person-circle me-1"></i>{{ __('Customer Portal Theme') }}
                            </h6>
                            <div x-data="{ portalPreset: '{{ $data['portal_theme_preset'] ?? 'indigo' }}' }">
                                <div class="row g-3 mb-5">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">{{ __('Default Portal Color Preset') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.portal_theme_preset" @change="portalPreset = $event.target.value">
                                            <option value="indigo">🟣 {{ __('Royal Purple (Indigo)') }}</option>
                                            <option value="emerald">🟢 {{ __('Forest Mint (Emerald)') }}</option>
                                            <option value="blue">🔵 {{ __('Ocean Breeze (Blue)') }}</option>
                                            <option value="orange">🟠 {{ __('Sunset Glow (Orange)') }}</option>
                                            <option value="dark">⚫ {{ __('Midnight Slate (Dark)') }}</option>
                                            <option value="custom">🎨 {{ __('Custom (Choose Your Own Colors)') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Controls the color of the customer portal login page and dashboard.') }}</small>
                                    </div>

                                    {{-- Custom Portal Color Pickers (only shown when "custom" selected) --}}
                                    <div class="col-md-7 align-items-center gap-4 border-start ps-4"
                                        x-show="portalPreset === 'custom'"
                                        x-cloak
                                        style="display: none;"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform -translate-x-2"
                                        x-transition:enter-end="opacity-100 transform translate-x-0">
                                        <div>
                                            <label class="form-label fw-semibold mb-1">{{ __('Portal Primary') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:38px; height:38px;" wire:model="data.portal_primary_color">
                                                <span class="font-monospace text-uppercase text-muted small">{{ !empty($data['portal_primary_color']) ? $data['portal_primary_color'] : '#6366f1' }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold mb-1">{{ __('Portal Accent') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:38px; height:38px;" wire:model="data.portal_accent_color">
                                                <span class="font-monospace text-uppercase text-muted small">{{ !empty($data['portal_accent_color']) ? $data['portal_accent_color'] : '#a78bfa' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── SECTION 2: MAIN SITE ── --}}
                            <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                                <i class="bi bi-globe me-1"></i>{{ __('Main Site (Landing Page) Customization') }}
                            </h6>

                            <div x-data="{
                                siteTheme: '{{ $data['theme_name'] ?? 'default' }}',
                                themes: {
                                    default:         { primary: '#06ad73', secondary: '#ff0000' },
                                    emerald_isp:     { primary: '#06ad73', secondary: '#ff6b35' },
                                    ocean_blue:      { primary: '#0284c7', secondary: '#38bdf8' },
                                    midnight_purple: { primary: '#4f46e5', secondary: '#818cf8' },
                                    cyber_neon:      { primary: '#00ffcc', secondary: '#ff007f' },
                                    rose_elegant:    { primary: '#f43f5e', secondary: '#fda4af' },
                                    islamic_green:   { primary: '#065f46', secondary: '#10b981' },
                                    golden_sunset:   { primary: '#f59e0b', secondary: '#ef4444' },
                                    custom:          { primary: null,      secondary: null },
                                },
                                get colors() { return this.themes[this.siteTheme] || this.themes.default; }
                            }">

                                {{-- Theme Preset Selector + Live Color Preview --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold">{{ __('Site Color Theme') }}</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select rounded-3 flex-grow-1"
                                                wire:model="data.theme_name"
                                                @change="siteTheme = $event.target.value">
                                                <option value="default">⚙️ {{ __('Default (🟢/🔴)') }}</option>
                                                <option value="emerald_isp">🌿 {{ __('Emerald ISP (🟢/🟠)') }}</option>
                                                <option value="ocean_blue">🌊 {{ __('Ocean Blue (🔵/🩵)') }}</option>
                                                <option value="midnight_purple">🌌 {{ __('Midnight Purple (🟣/💜)') }}</option>
                                                <option value="cyber_neon">💻 {{ __('Cyber Neon (🩵/💖)') }}</option>
                                                <option value="rose_elegant">🌸 {{ __('Rose Elegant (🔴/🌸)') }}</option>
                                                <option value="islamic_green">☪️ {{ __('Islamic Green (🟢/💚)') }}</option>
                                                <option value="golden_sunset">🌅 {{ __('Golden Sunset (🟡/🔴)') }}</option>
                                                <option value="custom">🎨 {{ __('Custom (Choose Your Own Colors)') }}</option>
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
                                        <small class="text-muted">{{ __('Select a preset or choose Custom to set your own colors.') }}</small>
                                    </div>

                                    </div>

                                    {{-- Custom Color Pickers — inside same x-data scope so siteTheme is accessible --}}
                                    <div class="row g-3 mb-4 p-3 rounded-3 border"
                                        style="background: rgba(var(--bs-primary-rgb, 99,102,241), 0.04); display: none;"
                                        x-show="siteTheme === 'custom'"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0">
                                        <p class="col-12 text-muted small mb-1"><i class="bi bi-info-circle me-1"></i>{{ __('These override the SASS default colors on the main site.') }}</p>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Primary Color') }}</label>
                                            <small class="d-block text-muted mb-1">{{ __('Replaces $primary-color in SASS') }}</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:42px; height:42px;" wire:model="data.theme_primary_color">
                                                <span class="font-monospace text-uppercase text-muted small">{{ !empty($data['theme_primary_color']) ? $data['theme_primary_color'] : '#06ad73' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Secondary Color') }}</label>
                                            <small class="d-block text-muted mb-1">{{ __('Replaces $secondary-color in SASS') }}</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color border-0 p-0 rounded-circle"
                                                    style="width:42px; height:42px;" wire:model="data.theme_accent_color">
                                                <span class="font-monospace text-uppercase text-muted small">{{ !empty($data['theme_accent_color']) ? $data['theme_accent_color'] : '#ff0000' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                {{-- Font, Dark Mode, Section Height --}}
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Font Family') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.theme_font_family">
                                            <option value="">{{ __('Default (Outfit + Plus Jakarta Sans)') }}</option>
                                            <option value="Inter">Inter — {{ __('Modern Sans') }}</option>
                                            <option value="Outfit">Outfit — {{ __('Clean Geometric') }}</option>
                                            <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                            <option value="Figtree">Figtree — {{ __('Friendly') }}</option>
                                            <option value="Nunito">Nunito — {{ __('Rounded') }}</option>
                                            <option value="Playfair Display">Playfair Display — {{ __('Serif') }}</option>
                                            <option value="Courier New">Courier New — {{ __('Monospace / Cyber') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Site Dark/Light Mode') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.theme_mode">
                                            <option value="">{{ __('Disabled (use SASS default)') }}</option>
                                            <option value="dark">🌙 {{ __('Force Dark Mode') }}</option>
                                            <option value="light">☀️ {{ __('Force Light Mode') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Overrides the default body background/text.') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Section Height') }}</label>
                                        <select class="form-select rounded-3" wire:model="data.theme_section_height">
                                            <option value="auto">{{ __('Content Height (auto)') }}</option>
                                            <option value="100vh">{{ __('Full Screen Height (100vh)') }}</option>
                                            <option value="80vh">{{ __('80% Screen Height (80vh)') }}</option>
                                            <option value="60vh">{{ __('60% Screen Height (60vh)') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Controls the minimum height of all main sections.') }}</small>
                                    </div>
                                </div>

                            </div>{{-- end x-data --}}
                        </div>

                        <!-- TAB 3: CONTACT & SOCIAL -->
                        <div x-show="activeTab === 'contact'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-building me-2"></i>{{ __('Office Contact Info') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Public Support Email') }}</label>
                                    <input type="email" class="form-control rounded-3" wire:model="data.site_email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Helpline Number') }}</label>
                                    <div wire:ignore>
                                        <input type="tel" id="site_phone" class="form-control rounded-3"
                                               x-data="intlTelInput('data.site_phone')"
                                               value="{{ $data['site_phone'] ?? '' }}">
                                    </div>
                                    @error('data.site_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Physical Office Address') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_address">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Google Maps Embed URL') }}</label>
                                    <textarea class="form-control rounded-3" rows="2" placeholder="{{ __('Paste ONLY the src attribute of the iframe code from Google Maps...') }}" wire:model="data.site_map"></textarea>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-share me-2"></i>{{ __('Social Media Links') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Facebook Page/Profile') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_facebook" placeholder="facebook.com/username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Twitter/X Handle') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_twitter" placeholder="@username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Instagram Profile') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_instagram" placeholder="instagram.com/username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('LinkedIn Page') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_linkedin" placeholder="linkedin.com/in/username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('WhatsApp Business Number') }}</label>
                                    <div wire:ignore>
                                        <input type="tel" id="site_whatsapp" class="form-control rounded-3"
                                               x-data="intlTelInput('data.site_whatsapp')"
                                               value="{{ $data['site_whatsapp'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('YouTube Channel') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_youtube" placeholder="youtube.com/channel/...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Pinterest') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_pinterest">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: BILLING & INVOICING -->
                        <div x-show="activeTab === 'billing'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-wallet2 me-2"></i>{{ __('Currency & Global Controls') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Currency Code') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_currency">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Invoice Number Prefix') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_invoice_prefix">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Customer ID Prefix') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.customer_id_prefix">
                                    @error('data.customer_id_prefix') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Grace Limit Amount') }}</label>
                                    <input type="number" class="form-control rounded-3" wire:model="data.disable_check_no">
                                    <small class="text-muted">{{ __('Maximum unpaid dues allowed before automatic disable.') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Grace Limit Days') }}</label>
                                    <input type="number" class="form-control rounded-3" wire:model="data.disable_check_days">
                                    <small class="text-muted">{{ __('Dues past invoice date before automatic disable.') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Expired Profile Name') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.expired_profile_name">
                                    <small class="text-muted">{{ __('Mikrotik redirection profile for expired users.') }}</small>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-file-earmark-pdf me-2"></i>{{ __('Invoice Document Layout Design') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                    <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Invoice PDF Logo') }}</label>
                                    <div wire:key="site_invoice_logo-wrapper">
                                        {{ $this->form->getComponent('site_invoice_logo') }}
                                    </div>
                                </div>
                                <div class="col-md-4 card p-3 shadow-none border bg-light bg-opacity-25 rounded-3">
                                    <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Authorized Signature') }}</label>
                                    <div wire:key="site_invoice_signature-wrapper">
                                        {{ $this->form->getComponent('site_invoice_signature') }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card p-3 shadow-none border bg-light bg-opacity-25 rounded-3 h-100">
                                        <label class="form-label fw-bold text-center border-bottom pb-2">{{ __('Document Primary Accent Color') }}</label>
                                        <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                                            <input type="color" class="form-control form-control-color border-0 p-0 rounded-circle" style="width:45px; height:45px;" wire:model="data.site_invoice_color">
                                            <span class="font-monospace text-uppercase text-muted fs-6">{{ $data['site_invoice_color'] ?? '#000000' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('Invoice Footer Text') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_invoice_footer" placeholder="{{ __('e.g. Thank you for choosing our fiber network services!') }}">
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-bold text-primary-emphasis mb-2">{{ __('Terms & Conditions (Rich Editor)') }}</label>
                                    <div class="border rounded-3 p-2 bg-white">
                                        <div wire:key="site_invoice_terms-wrapper">
                                            {{ $this->form->getComponent('site_invoice_terms') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: PAYMENT GATEWAYS -->
                        <div x-show="activeTab === 'payment'" x-transition:enter.duration.200ms>
                            
                            <!-- BKASH -->
                            <div class="card shadow-none border rounded-3 p-4 mb-4 bg-light bg-opacity-25">
                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-danger p-2"><i class="bi bi-cash-stack fs-5"></i></span>
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ __('bKash Merchant Checkout API') }}</h5>
                                            <p class="text-muted small mb-0">{{ __('Manage bKash tokenized gateway integration credentials.') }}</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="payment_bkash_enabled" wire:model="data.payment_bkash_enabled" value="1">
                                    </div>
                                </div>
                                
                                <div class="row g-3" x-show="$wire.data.payment_bkash_enabled">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">{{ __('bKash Endpoint base URL') }}</label>
                                        <input type="text" class="form-control rounded-3" wire:model="data.payment_bkash_base_url">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('bKash Merchant Username') }}</label>
                                        <input type="text" class="form-control rounded-3" wire:model="data.payment_bkash_username">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('bKash Merchant Password') }}</label>
                                        <input type="password" class="form-control rounded-3" wire:model="data.payment_bkash_password">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('bKash Application Key') }}</label>
                                        <input type="password" class="form-control rounded-3" wire:model="data.payment_bkash_app_key">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('bKash Application Secret') }}</label>
                                        <input type="password" class="form-control rounded-3" wire:model="data.payment_bkash_app_secret">
                                    </div>
                                </div>
                            </div>

                            <!-- NAGAD -->
                            <div class="card shadow-none border rounded-3 p-4 mb-4 bg-light bg-opacity-25">
                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-warning p-2"><i class="bi bi-wallet2 fs-5"></i></span>
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ __('Nagad Merchant Checkout API') }}</h5>
                                            <p class="text-muted small mb-0">{{ __('Manage Nagad DFS system checkout credentials.') }}</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="payment_nagad_enabled" wire:model="data.payment_nagad_enabled" value="1">
                                    </div>
                                </div>

                                <div class="row g-3" x-show="$wire.data.payment_nagad_enabled">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">{{ __('Nagad API Endpoint base URL') }}</label>
                                        <input type="text" class="form-control rounded-3" wire:model="data.payment_nagad_base_url">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">{{ __('Nagad Merchant ID') }}</label>
                                        <input type="text" class="form-control rounded-3" wire:model="data.payment_nagad_merchant_id">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Nagad Server Public Key') }}</label>
                                        <textarea class="form-control rounded-3 font-monospace" rows="4" style="font-size:0.75rem;" wire:model="data.payment_nagad_public_key"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('Merchant Private Key') }}</label>
                                        <textarea class="form-control rounded-3 font-monospace" rows="4" style="font-size:0.75rem;" wire:model="data.payment_nagad_private_key"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SSLCOMMERZ -->
                            <div class="card shadow-none border rounded-3 p-4 bg-light bg-opacity-25">
                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-primary p-2"><i class="bi bi-credit-card fs-5"></i></span>
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ __('SSLCommerz Multi-Channel Payment Gateway') }}</h5>
                                            <p class="text-muted small mb-0">{{ __('Accept all debit/credit cards and MFS wallets inside Bangladesh.') }}</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="payment_sslcommerz_enabled" wire:model="data.payment_sslcommerz_enabled" value="1">
                                    </div>
                                </div>

                                <div class="row g-3" x-show="$wire.data.payment_sslcommerz_enabled">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">{{ __('Store ID') }}</label>
                                        <input type="text" class="form-control rounded-3" wire:model="data.payment_sslcommerz_store_id">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">{{ __('Store Password') }}</label>
                                        <input type="password" class="form-control rounded-3" wire:model="data.payment_sslcommerz_store_password">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-50 mt-4">
                                            <div class="ms-2">
                                                <label class="form-check-label fw-semibold" for="payment_sslcommerz_sandbox">{{ __('Sandbox') }}</label>
                                            </div>
                                            <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="payment_sslcommerz_sandbox" wire:model="data.payment_sslcommerz_sandbox" value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 6: SECURITY & SECRETS -->
                        <div x-show="activeTab === 'security'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-key-fill me-2"></i>{{ __('Site Secret Credentials') }}</h5>
                            <p class="text-muted small mb-4">
                                {{ __("System-wide credentials used for machine-to-machine integrations, remote server sync, and verifying third-party webhook callbacks (e.g., payment status updates). For standard client-level or user integrations, please utilize Jetstream's built-in personal API tokens instead.") }}
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('API Secret Key') }}</label>
                                    <input type="password" class="form-control rounded-3" wire:model="data.site_secret_key">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('API Secret Value') }}</label>
                                    <input type="password" class="form-control rounded-3" wire:model="data.site_secret_value">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Validity Threshold') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.site_secret_validity" placeholder="{{ __('e.g. 365 days') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Notification Destination URL') }}</label>
                                    <input type="url" class="form-control rounded-3" wire:model="data.site_secret_url">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Notification Email Address') }}</label>
                                    <input type="email" class="form-control rounded-3" wire:model="data.site_secret_email">
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-folder-symlink me-2"></i>{{ __('Local System Database Binary Path') }}</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('MySQL/MariaDB Dump CLI Path') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.mysql_binary_path" placeholder="{{ __('e.g. C:\laragon\bin\mysql\mysql-8.0\bin\\') }}">
                                    <small class="text-muted">{{ __('Must include a trailing slash! Required if database backup fails due to missing command in server PATH env.') }}</small>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-activity me-2"></i>{{ __('MikroTik Logger Operations') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                        <div class="ms-2">
                                            <label class="form-check-label fw-semibold d-block" for="log_server_enabled" style="cursor: pointer;">{{ __('Enable Remote Log Streaming') }}</label>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ __('Enable log daemon listener on background worker') }}</span>
                                        </div>
                                        <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="log_server_enabled" wire:model="data.log_server_enabled" value="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Auto-Delete Logs After (Days)') }}</label>
                                    <input type="number" class="form-control rounded-3" wire:model="data.log_retention_days">
                                    <small class="text-muted">{{ __('Logs older than this threshold will be deleted automatically.') }}</small>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-bold text-primary-emphasis mb-2">{{ __('Capture logs for selected routers:') }}</label>
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
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-window-sidebar me-2"></i>{{ __('Landing Page Hero Panel') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Main Slider Headline') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.hero_title">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Slider Sub-headline') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.hero_subtitle">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Action Button Label') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.hero_button_text">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Action Button Link (URL)') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.hero_button_link">
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-briefcase me-2"></i>{{ __('Company Modules & Services') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('About Company Section Title') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.about_title">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Packages Section Title') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.packages_section_title">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('About Company Brief Summary') }}</label>
                                    <textarea class="form-control rounded-3" rows="3" wire:model="data.about_body"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Customer Registration Link Override') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.registration_link">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch ps-0 d-flex justify-content-between align-items-center p-2 border rounded-3 bg-light bg-opacity-50 mt-4">
                                        <div class="ms-2">
                                            <label class="form-check-label fw-semibold d-block" for="is_active" style="cursor: pointer;">{{ __('Public Website Visible') }}</label>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ __('Show/Hide public frontend site') }}</span>
                                        </div>
                                        <input class="form-check-input fs-5 cursor-pointer me-2" type="checkbox" role="switch" id="is_active" wire:model="data.is_active" value="1">
                                    </div>
                                </div>
                            </div>

                            <h5 class="form-section-title text-primary fw-bold mt-5"><i class="bi bi-c-circle me-2"></i>{{ __('Footer Section Layout') }}</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Copyright Footer Text') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.footer_copyright" placeholder="{{ __('e.g. © 2024 Your Company name. All rights reserved.') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('BTCL Tariff Link (Manual URL)') }}</label>
                                    <input type="text" class="form-control rounded-3" wire:model="data.btcl_tariff_link" placeholder="{{ __('e.g. /pdf/btcl_tariff.pdf or #') }}">
                                </div>
                            </div>

                            {{-- Filament-managed repeaters & BTCL PDF upload - rendered as a full form for proper Alpine.js context --}}
                            <div class="mt-4">
                                {{ $this->contentForm }}
                            </div>
                        </div>

                        <!-- TAB 8: STORED LOGS -->
                        <div x-show="activeTab === 'logs'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-card-text me-2"></i>{{ __('Router Log Data Archive') }}</h5>
                            @include('livewire.mikrotik.log-table-master-embed')
                        </div>

                        <!-- TAB 9: UTILITIES & SYSTEM DATABASE -->
                        <div x-show="activeTab === 'utilities'" x-transition:enter.duration.200ms>
                            <h5 class="form-section-title text-primary fw-bold"><i class="bi bi-box-seam me-2"></i>{{ __('System database Snapshots') }}</h5>
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
            </div>
        </div>
    </div>
    
    <x-filament-actions::modals />
</div>
