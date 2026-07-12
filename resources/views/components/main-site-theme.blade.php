@php
    // ─────────────────────────────────────────────────────────────────────────────
    // MAIN SITE THEME ENGINE
    // Controls: main landing page colors, font, dark-mode, section height
    // Does NOT affect customer portal (portal.* is controlled by portal_theme_preset)
    // ─────────────────────────────────────────────────────────────────────────────

    $theme_name = siteUrlSettings('theme_name') ?? 'default';

    // Preset color map — each preset has a ready-made primary + secondary
    $presets = [
        'emerald_isp'     => ['primary' => '#06ad73', 'secondary' => '#ff6b35'],
        'ocean_blue'      => ['primary' => '#0284c7', 'secondary' => '#38bdf8'],
        'midnight_purple' => ['primary' => '#4f46e5', 'secondary' => '#818cf8'],
        'cyber_neon'      => ['primary' => '#00ffcc', 'secondary' => '#ff007f'],
        'rose_elegant'    => ['primary' => '#f43f5e', 'secondary' => '#fda4af'],
        'islamic_green'   => ['primary' => '#065f46', 'secondary' => '#10b981'],
        'golden_sunset'   => ['primary' => '#f59e0b', 'secondary' => '#ef4444'],
    ];

    // Resolve colors
    if ($theme_name === 'default' || !$theme_name) {
        $primary   = null;
        $secondary = null;
    } elseif ($theme_name === 'custom') {
        $primary   = siteUrlSettings('theme_primary_color')  ?: null;
        $secondary = siteUrlSettings('theme_accent_color')   ?: null;
    } elseif (isset($presets[$theme_name])) {
        $primary   = $presets[$theme_name]['primary'];
        $secondary = $presets[$theme_name]['secondary'];
    } else {
        $primary   = null;
        $secondary = null;
    }

    // Other settings
    $fontFamily    = siteUrlSettings('theme_font_family')    ?: null;
    $darkMode      = siteUrlSettings('theme_mode')           ?: null; // 'dark' | 'light' | ''
    $sectionHeight = siteUrlSettings('theme_section_height') ?: 'auto';

    // Helper: hex → "r, g, b"
    $getRgb = function ($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return hexdec(substr($hex, 0, 2)).', '.hexdec(substr($hex, 2, 2)).', '.hexdec(substr($hex, 4, 2));
    };

    $primaryRgb   = $primary   ? $getRgb($primary)   : null;
    $secondaryRgb = $secondary ? $getRgb($secondary) : null;

    // Only inject if at least one setting is active
    $hasCustomization = $primary || $secondary || $fontFamily
                        || ($darkMode && in_array($darkMode, ['dark', 'light']))
                        || $sectionHeight !== 'auto';
@endphp

@if($hasCustomization)
<style id="main-site-dynamic-styles">
    :root {
        @if($primary)
        --primary-color:       {{ $primary }};
        --primary-rgb:         {{ $primaryRgb }};
        --primary-color-light: rgba({{ $primaryRgb }}, 0.35);
        --primary-color-text:  {{ $primary }};
        --primary-gradient:    linear-gradient(135deg, {{ $primary }} 0%, rgba({{ $primaryRgb }}, 0.65) 100%);
        --glass-glow-border:   rgba({{ $primaryRgb }}, 0.18);
        @endif

        @if($secondary)
        --secondary-color:       {{ $secondary }};
        --secondary-rgb:         {{ $secondaryRgb }};
        --secondary-color-light: rgba({{ $secondaryRgb }}, 0.35);
        --secondary-color-text:  {{ $secondary }};
        --secondary-gradient:    linear-gradient(135deg, {{ $secondary }} 0%, rgba({{ $secondaryRgb }}, 0.65) 100%);
        --accent-color:          {{ $secondary }};
        --accent-rgb:            {{ $secondaryRgb }};
        --accent-gradient:       linear-gradient(135deg, {{ $secondary }} 0%, {{ $primary ?? 'var(--primary-color)' }} 100%);
        @endif

        @if($fontFamily)
        --font-heading: "{{ $fontFamily }}", system-ui, -apple-system, sans-serif;
        --font-body:    "{{ $fontFamily }}", system-ui, -apple-system, sans-serif;
        @endif

        @if($sectionHeight !== 'auto')
        --section-min-height: {{ $sectionHeight }};
        @endif
    }
</style>
@endif

@if($darkMode && in_array($darkMode, ['dark', 'light']))
<script>
    (function () {
        var m = '{{ $darkMode }}';
        document.documentElement.setAttribute('data-bs-theme', m);
        document.documentElement.setAttribute('data-theme', m);
        if (m === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('theme-light');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('theme-light');
        }
    })();
</script>
@endif
