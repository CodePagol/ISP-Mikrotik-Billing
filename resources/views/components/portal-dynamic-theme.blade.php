@php
    // ─────────────────────────────────────────────────────────────────────────────
    // CUSTOMER PORTAL THEME ENGINE
    // Controlled ONLY by portal_theme_preset — completely independent from main site
    // ─────────────────────────────────────────────────────────────────────────────

    $portalPreset = siteUrlSettings('portal_theme_preset') ?? 'indigo';

    // Each preset carries its own complete color set
    $presetMap = [
        'indigo'  => ['primary' => '#6366f1', 'accent' => '#a78bfa', 'dark' => false],
        'emerald' => ['primary' => '#10b981', 'accent' => '#34d399', 'dark' => false],
        'blue'    => ['primary' => '#3b82f6', 'accent' => '#38bdf8', 'dark' => false],
        'orange'  => ['primary' => '#f97316', 'accent' => '#fb923c', 'dark' => false],
        'dark'    => ['primary' => '#64748b', 'accent' => '#94a3b8', 'dark' => true],
    ];

    if ($portalPreset === 'custom') {
        $preset = [
            'primary' => siteUrlSettings('portal_primary_color') ?? '#6366f1',
            'accent'  => siteUrlSettings('portal_accent_color') ?? '#a78bfa',
            'dark'    => false,
        ];
    } else {
        $preset = $presetMap[$portalPreset] ?? $presetMap['indigo'];
    }

    $adminSettings = [
        'portal_theme_preset'      => $portalPreset,
        'theme_preset'             => 'fintech',           // keep portal dynamic CSS active
        'theme_name'               => 'ocean_blue',
        'theme_primary_color'      => $preset['primary'],
        'theme_accent_color'       => $preset['accent'],
        'theme_card_style'         => siteUrlSettings('theme_card_style')         ?? 'glass',
        'theme_border_radius'      => siteUrlSettings('theme_border_radius')      ?? '16px',
        'theme_font_size'          => siteUrlSettings('theme_font_size')          ?? 'medium',
        'theme_font_family'        => siteUrlSettings('theme_font_family')        ?? 'Outfit',
        'theme_nav_style'          => siteUrlSettings('theme_nav_style')          ?? 'sidebar',
        'theme_widget_style'       => siteUrlSettings('theme_widget_style')       ?? 'glass',
        'theme_mode'               => siteUrlSettings('theme_mode')               ?? 'dark',
        'theme_transparency'       => siteUrlSettings('theme_transparency')       ?? '0.5',
        'theme_blur'               => siteUrlSettings('theme_blur')               ?? '16px',
        'theme_animations'         => siteUrlSettings('theme_animations')         ?? '1.0',
        'theme_gradient_intensity' => siteUrlSettings('theme_gradient_intensity') ?? '0.7',
        'theme_updated_at'         => siteUrlSettings('theme_updated_at')         ?? '0',
    ];
@endphp

<script>
    (function () {
        // Fallback defaults from backend
        const adminDefaults = @json($adminSettings);

        // Fetch user custom settings from localStorage
        let userSettings = {};
        try {
            const lastUpdated = localStorage.getItem('portal-theme-updated-at');
            if (lastUpdated !== adminDefaults.theme_updated_at) {
                localStorage.removeItem('portal-theme-settings');
                localStorage.setItem('portal-theme-updated-at', adminDefaults.theme_updated_at);
            } else {
                const saved = localStorage.getItem('portal-theme-settings');
                if (saved) {
                    userSettings = JSON.parse(saved);
                }
            }
        } catch (e) {
            console.error('Failed to parse user theme settings:', e);
        }

        // Sync legacy theme modes from other toggles
        if (!userSettings.theme_mode) {
            const legacyTheme = localStorage.getItem('theme') || localStorage.getItem('site-theme');
            if (legacyTheme === 'light') {
                userSettings.theme_mode = 'light';
            } else if (legacyTheme === 'dark') {
                userSettings.theme_mode = 'dark';
            } else if (legacyTheme === 'auto') {
                userSettings.theme_mode = 'auto';
            }
        }

        // Merge user overrides with admin settings
        const settings = Object.assign({}, adminDefaults, userSettings);

        // Save active settings globally for runtime access by other components
        window.__themeSettings = settings;
        window.__adminThemeDefaults = adminDefaults;

        // Apply background, mode classes, and configurations
        applyThemeMode(settings.theme_mode);
        loadThemeFont(settings.theme_font_family);
        generateThemeCss(settings);

        // ----------------------------------------------------
        // Helper Functions
        // ----------------------------------------------------

        function applyThemeMode(mode) {
            const html = document.documentElement;
            let applied = 'dark'; // default

            if (mode === 'light') {
                applied = 'light';
            } else if (mode === 'auto') {
                applied = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            } else if (mode === 'battery') {
                applied = 'dark'; // save battery
            } else if (mode === 'scheduled') {
                const hour = new Date().getHours();
                applied = (hour < 6 || hour > 18) ? 'dark' : 'light';
            }

            html.setAttribute('data-theme', applied);
            html.setAttribute('data-bs-theme', applied);

            if (applied === 'dark') {
                html.classList.add('dark');
                html.classList.remove('theme-light');
            } else {
                html.classList.remove('dark');
                html.classList.add('theme-light');
            }
        }

        function loadThemeFont(font) {
            // Google Fonts remote CDN loading disabled to comply with no-CDN requirements
            return;
        }

        function hexToRgb(hex) {
            hex = hex.replace(/^#/, '');
            if (hex.length === 3) {
                hex = hex.split('').map(char => char + char).join('');
            }
            const num = parseInt(hex, 16);
            return {
                r: (num >> 16) & 255,
                g: (num >> 8) & 255,
                b: num & 255
            };
        }

        function rgbToHsl(r, g, b) {
            r /= 255; g /= 255; b /= 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;

            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }
            return { h: h * 360, s: s * 100, l: l * 100 };
        }

        function hslToRgb(h, s, l) {
            h /= 360; s /= 100; l /= 100;
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            return {
                r: Math.round(r * 255),
                g: Math.round(g * 255),
                b: Math.round(b * 255)
            };
        }

        function generateShades(hex) {
            const rgb = hexToRgb(hex);
            const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
            
            const lightnessSteps = {
                50: 97,
                100: 92,
                200: 84,
                300: 74,
                400: 62,
                500: 48,
                600: 38,
                700: 28,
                800: 18,
                900: 12,
                950: 8
            };
            
            const shades = {};
            for (const [step, l] of Object.entries(lightnessSteps)) {
                const shadeRgb = hslToRgb(hsl.h, hsl.s, l);
                shades[step] = `${shadeRgb.r}, ${shadeRgb.g}, ${shadeRgb.b}`;
            }
            return shades;
        }

        function generateThemeCss(s) {
            let styleTag = document.getElementById('portal-dynamic-styles');
            if (s.theme_preset === 'default' || !s.theme_primary_color) {
                if (styleTag) {
                    styleTag.remove();
                }
                return;
            }

            const primaryRgb = hexToRgb(s.theme_primary_color);
            const accentRgb = hexToRgb(s.theme_accent_color);
            const shades = generateShades(s.theme_primary_color);

            // Compute background color depending on theme templates
            let darkBg = '#0b1329';
            let darkBgAlt = '#121e3d';
            let isLight = s.theme_mode === 'light';

            if (s.theme_name === 'amoled') {
                darkBg = '#000000';
                darkBgAlt = '#09090b';
            } else if (s.theme_name === 'minimal_light') {
                darkBg = '#fcfcfc';
                darkBgAlt = '#f3f4f6';
                isLight = true;
            } else if (s.theme_name === 'islamic_emerald') {
                darkBg = '#022c22';
                darkBgAlt = '#064e3b';
            } else if (s.theme_name === 'midnight_purple') {
                darkBg = '#080512';
                darkBgAlt = '#120d24';
            } else if (s.theme_name === 'soft_gold') {
                darkBg = '#1c1917';
                darkBgAlt = '#292524';
            } else if (s.theme_name === 'modern_cyan') {
                darkBg = '#041014';
                darkBgAlt = '#081f26';
            }

            // Determine card/widget background colors
            const trans = parseFloat(s.theme_transparency) || 0.5;
            const blur = s.theme_blur || '16px';
            const cardBg = isLight 
                ? `rgba(255, 255, 255, ${1 - trans * 0.5})` 
                : `rgba(30, 41, 59, ${1 - trans})`;
            const cardBorder = isLight 
                ? `rgba(0, 0, 0, 0.08)` 
                : `rgba(255, 255, 255, 0.08)`;
            
            // Fonts selection
            let fontStack = 'sans-serif';
            if (s.theme_font_family === 'Courier New') {
                fontStack = 'Courier New, monospace';
            } else if (s.theme_font_family) {
                fontStack = `"${s.theme_font_family}", system-ui, -apple-system, sans-serif`;
            }

            // Animation scaling
            const animScale = parseFloat(s.theme_animations) ?? 1.0;

            // Generate CSS properties string
            let css = `
                :root {
                    --primary-color: ${s.theme_primary_color};
                    --primary-rgb: ${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b};
                    --accent-color: ${s.theme_accent_color};
                    --accent-rgb: ${accentRgb.r}, ${accentRgb.g}, ${accentRgb.b};
                    --dark-bg: ${darkBg};
                    --section-dark-alternate: ${darkBgAlt};
                    
                    --primary-gradient: linear-gradient(135deg, ${s.theme_primary_color} 0%, ${s.theme_accent_color} 100%);
                    --accent-gradient: linear-gradient(135deg, ${s.theme_accent_color} 0%, ${s.theme_primary_color} 100%);
                    
                    /* Filament core override variables */
                    --primary-50: ${shades[50]};
                    --primary-100: ${shades[100]};
                    --primary-200: ${shades[200]};
                    --primary-300: ${shades[300]};
                    --primary-400: ${shades[400]};
                    --primary-500: ${shades[500]};
                    --primary-600: ${shades[600]};
                    --primary-700: ${shades[700]};
                    --primary-800: ${shades[800]};
                    --primary-900: ${shades[900]};
                    --primary-950: ${shades[950]};
                    
                    --primary: rgb(${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}) !important;
                    --primary-hover: rgb(${Math.max(0, primaryRgb.r-30)}, ${Math.max(0, primaryRgb.g-30)}, ${Math.max(0, primaryRgb.b-30)}) !important;
                    
                    /* Theme UI controls */
                    --border-radius: ${s.theme_border_radius};
                    --font-family: ${fontStack};
                    --animation-scale: ${animScale};
                    --blur-effect: ${blur};
                    --card-bg: ${cardBg};
                    --card-border: ${cardBorder};
                }
                
                /* Global Font Override */
                body, .fi-body, input, button, select, textarea, .font-sans {
                    font-family: var(--font-family) !important;
                }
                
                /* Border Radius Override */
                .rounded-xl, .portal-card, .fi-card, .fi-section, .fi-ta-content, .fi-ta-header, .fi-simple-main-card, .card, .btn, .fi-btn, .rounded-3xl, .rounded-2xl {
                    border-radius: var(--border-radius) !important;
                }
            `;

            if (s.theme_card_style === 'glass') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: ${cardBg} !important;
                        backdrop-filter: blur(var(--blur-effect)) !important;
                        -webkit-backdrop-filter: blur(var(--blur-effect)) !important;
                        border: 1px solid var(--card-border) !important;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
                    }
                `;
            } else if (s.theme_card_style === 'minimal') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: transparent !important;
                        border: 1px solid var(--card-border) !important;
                        box-shadow: none !important;
                    }
                `;
            } else if (s.theme_card_style === 'cyber') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: #000000 !important;
                        border: 2px solid var(--primary-color) !important;
                        box-shadow: 0 0 12px var(--primary-color) !important;
                    }
                `;
            } else if (s.theme_card_style === 'soft') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: ${isLight ? '#ffffff' : '#1e293b'} !important;
                        border: none !important;
                        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.05) !important;
                    }
                `;
            } else if (s.theme_card_style === 'neo') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: ${isLight ? '#ffffff' : '#1e293b'} !important;
                        border: 3px solid #000000 !important;
                        box-shadow: 6px 6px 0px #000000 !important;
                    }
                `;
            } else if (s.theme_card_style === 'spiritual') {
                css += `
                    .portal-card, .fi-card, .fi-section, .fi-simple-main-card, .card {
                        background: ${cardBg} !important;
                        border: 1px solid rgba(255, 255, 255, 0.05) !important;
                        border-radius: 32px !important;
                        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
                    }
                `;
            }

            // Widget Customizations
            if (s.theme_widget_style === 'minimal') {
                css += `
                    .portal-card {
                        padding: 1rem !important;
                        border: 1px solid var(--card-border) !important;
                    }
                `;
            } else if (s.theme_widget_style === 'amoled') {
                css += `
                    .portal-card {
                        background: #000000 !important;
                        border: 1px solid #1f2937 !important;
                    }
                `;
            } else if (s.theme_widget_style === 'transparent') {
                css += `
                    .portal-card {
                        background: transparent !important;
                        border: 1px solid var(--card-border) !important;
                    }
                `;
            }

            // Font size adjustments
            if (s.theme_font_size === 'small') {
                css += `
                    body, p, span, td, th, input, button { font-size: 0.875rem !important; }
                `;
            } else if (s.theme_font_size === 'large') {
                css += `
                    body, p, span, td, th, input, button { font-size: 1.075rem !important; }
                `;
            }

            // Dynamic background adjustments for the main site and portal body
            css += `
                body, .fi-body, .fi-simple-layout, .bg-light {
                    background-color: var(--dark-bg) !important;
                }
                .fi-simple-layout {
                    background-image: radial-gradient(circle at 50% 50%, rgba(var(--primary-rgb), 0.05) 0%, transparent 60%) !important;
                }
                .bg-success {
                    background-color: var(--section-dark-alternate) !important;
                }
            `;

            let styleTag = document.getElementById('portal-dynamic-styles');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'portal-dynamic-styles';
                document.head.appendChild(styleTag);
            }
            styleTag.innerHTML = css;
        }

        // Bind update theme helper to window for customizer access
        window.__updatePortalTheme = function(updatedSettings) {
            const merged = Object.assign({}, adminDefaults, updatedSettings);
            window.__themeSettings = merged;
            applyThemeMode(merged.theme_mode);
            loadThemeFont(merged.theme_font_family);
            generateThemeCss(merged);
        };

    })();
</script>
