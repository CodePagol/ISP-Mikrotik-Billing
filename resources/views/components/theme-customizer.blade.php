<div x-data="themeCustomizer()" x-cloak>
    <!-- Floating Action Button -->
    <button 
        @click="open = !open" 
        class="cp-fixed cp-bottom-6 cp-right-6 cp-w-14 cp-h-14 cp-bg-gradient-to-r cp-from-indigo-500 cp-to-emerald-500 cp-text-white cp-rounded-full cp-flex cp-items-center cp-justify-center cp-shadow-2xl cp-transition-transform cp-duration-200 cp-z-50 hover:cp-scale-110 focus:cp-outline-none"
        title="Customize Theme"
        id="theme-customizer-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="cp-h-6 cp-w-6 cp-animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    <!-- Side Drawer Overlay -->
    <div 
        x-show="open" 
        @click="open = false" 
        class="cp-fixed cp-inset-0 cp-bg-black/60 cp-backdrop-blur-sm cp-z-50 cp-transition-opacity"
        x-transition:enter="cp-ease-out cp-duration-300"
        x-transition:enter-start="cp-opacity-0"
        x-transition:enter-end="cp-opacity-100"
        x-transition:leave="cp-ease-in cp-duration-200"
        x-transition:leave-start="cp-opacity-100"
        x-transition:leave-end="cp-opacity-0"></div>

    <!-- Side Drawer Panel -->
    <div 
        x-show="open" 
        class="cp-fixed cp-top-0 cp-right-0 cp-h-full cp-w-96 cp-max-w-full cp-bg-slate-950/95 cp-backdrop-blur-lg cp-border-l cp-border-white/10 cp-shadow-2xl cp-z-50 cp-overflow-y-auto cp-transition-transform cp-p-6 cp-text-slate-100"
        x-transition:enter="cp-transform cp-transition cp-ease-in-out cp-duration-300"
        x-transition:enter-start="cp-translate-x-full"
        x-transition:enter-end="cp-translate-x-0"
        x-transition:leave="cp-transform cp-transition cp-ease-in-out cp-duration-300"
        x-transition:leave-start="cp-translate-x-0"
        x-transition:leave-end="cp-translate-x-full">
        
        <!-- Header -->
        <div class="cp-flex cp-items-center cp-justify-between cp-pb-4 cp-border-b cp-border-white/10">
            <div class="cp-flex cp-items-center cp-gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="cp-h-6 cp-w-6 cp-text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <h3 class="cp-text-lg cp-font-black cp-tracking-tight">Customizer Theme</h3>
            </div>
            <button @click="open = false" class="cp-text-slate-400 hover:cp-text-white cp-p-1 cp-rounded-lg hover:cp-bg-white/5">
                <svg class="cp-w-6 cp-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Customizer Body -->
        <div class="cp-mt-6 cp-space-y-6 cp-text-left">
            
            <!-- 1. UI Style Presets -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-3">Built-in Presets</label>
                <div class="cp-grid cp-grid-cols-2 cp-gap-2">
                    <template x-for="(name, key) in presets" :key="key">
                        <button 
                            @click="selectPreset(key)" 
                            :class="state.theme_preset === key ? 'cp-border-emerald-500 cp-bg-emerald-500/10 cp-text-emerald-400' : 'cp-border-white/5 cp-bg-white/5 hover:cp-bg-white/10 cp-text-slate-300'"
                            class="cp-py-2 cp-px-3 cp-text-xs cp-font-semibold cp-rounded-xl cp-border cp-transition-all cp-duration-150">
                            <span x-text="name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- 2. Theme templates selection -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-3">Theme Template</label>
                <div class="cp-grid cp-grid-cols-2 cp-gap-2">
                    <template x-for="(name, key) in themes" :key="key">
                        <button 
                            @click="state.theme_name = key; updateTheme()" 
                            :class="state.theme_name === key ? 'cp-border-emerald-500 cp-bg-emerald-500/10 cp-text-emerald-400' : 'cp-border-white/5 cp-bg-white/5 hover:cp-bg-white/10 cp-text-slate-300'"
                            class="cp-py-2 cp-px-3 cp-text-xs cp-font-semibold cp-rounded-xl cp-border cp-transition-all cp-duration-150">
                            <span x-text="name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- 3. Theme Mode selection -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-3">Theme Mode</label>
                <div class="cp-grid cp-grid-cols-3 cp-gap-1.5">
                    <template x-for="(name, key) in modes" :key="key">
                        <button 
                            @click="state.theme_mode = key; updateTheme()" 
                            :class="state.theme_mode === key ? 'cp-border-emerald-500 cp-bg-emerald-500/10 cp-text-emerald-400' : 'cp-border-white/5 cp-bg-white/5 hover:cp-bg-white/10 cp-text-slate-300'"
                            class="cp-py-1.5 cp-px-2 cp-text-[10px] cp-font-bold cp-rounded-lg cp-border cp-transition-all cp-duration-150 cp-text-center cp-truncate">
                            <span x-text="name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- 4. Manual Color customization -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-2">Color Personalization</label>
                <div class="cp-space-y-3 cp-p-3 cp-bg-white/5 cp-rounded-2xl cp-border cp-border-white/5">
                    <div class="cp-flex cp-items-center cp-justify-between">
                        <span class="cp-text-xs cp-font-medium cp-text-slate-300">Primary Color</span>
                        <input type="color" x-model="state.theme_primary_color" @input="updateTheme()" class="cp-w-8 cp-h-8 cp-rounded-lg cp-border-0 cp-cursor-pointer bg-transparent">
                    </div>
                    <div class="cp-flex cp-items-center cp-justify-between">
                        <span class="cp-text-xs cp-font-medium cp-text-slate-300">Accent Color</span>
                        <input type="color" x-model="state.theme_accent_color" @input="updateTheme()" class="cp-w-8 cp-h-8 cp-rounded-lg cp-border-0 cp-cursor-pointer bg-transparent">
                    </div>
                </div>
            </div>

            <!-- 5. UI Customizations -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-2">UI Styling & Layout</label>
                <div class="cp-space-y-3 cp-p-3 cp-bg-white/5 cp-rounded-2xl cp-border cp-border-white/5">
                    
                    <!-- Border radius -->
                    <div>
                        <span class="cp-text-xs cp-font-medium cp-text-slate-400 cp-block cp-mb-1">Border Radius</span>
                        <select x-model="state.theme_border_radius" @change="updateTheme()" class="cp-w-full cp-bg-slate-900 cp-border cp-border-white/10 cp-rounded-lg cp-py-1.5 cp-px-2 cp-text-xs cp-text-slate-200">
                            <option value="0px">Sharp (0px)</option>
                            <option value="4px">Subtle (4px)</option>
                            <option value="8px">Standard (8px)</option>
                            <option value="12px">Medium (12px)</option>
                            <option value="16px">Large (16px)</option>
                            <option value="24px">Extra Large (24px)</option>
                            <option value="32px">Rounded curve (32px)</option>
                        </select>
                    </div>

                    <!-- Fonts family -->
                    <div>
                        <span class="cp-text-xs cp-font-medium cp-text-slate-400 cp-block cp-mb-1">Typography</span>
                        <select x-model="state.theme_font_family" @change="updateTheme()" class="cp-w-full cp-bg-slate-900 cp-border cp-border-white/10 cp-rounded-lg cp-py-1.5 cp-px-2 cp-text-xs cp-text-slate-200">
                            <option value="Inter">Inter (Sans)</option>
                            <option value="Outfit">Outfit (Clean Geometry)</option>
                            <option value="Plus Jakarta Sans">Plus Jakarta (Modern)</option>
                            <option value="Playfair Display">Playfair (Serif)</option>
                            <option value="Figtree">Figtree (Friendly)</option>
                            <option value="Courier New">Monospace (Cyber)</option>
                            <option value="Nunito">Nunito (Rounded)</option>
                        </select>
                    </div>

                    <!-- Card Style -->
                    <div>
                        <span class="cp-text-xs cp-font-medium cp-text-slate-400 cp-block cp-mb-1">Card Style</span>
                        <select x-model="state.theme_card_style" @change="updateTheme()" class="cp-w-full cp-bg-slate-900 cp-border cp-border-white/10 cp-rounded-lg cp-py-1.5 cp-px-2 cp-text-xs cp-text-slate-200">
                            <option value="flat">Flat / Solid</option>
                            <option value="glass">Glassmorphism</option>
                            <option value="minimal">Minimal Outline</option>
                            <option value="cyber">Cyber Glow</option>
                            <option value="soft">Soft shadow</option>
                            <option value="neo">Neo Pop (3D)</option>
                            <option value="spiritual">Spiritual curve</option>
                        </select>
                    </div>

                    <!-- Widget style -->
                    <div>
                        <span class="cp-text-xs cp-font-medium cp-text-slate-400 cp-block cp-mb-1">Widget Customization</span>
                        <select x-model="state.theme_widget_style" @change="updateTheme()" class="cp-w-full cp-bg-slate-900 cp-border cp-border-white/10 cp-rounded-lg cp-py-1.5 cp-px-2 cp-text-xs cp-text-slate-200">
                            <option value="compact">Compact style</option>
                            <option value="minimal">Minimal style</option>
                            <option value="glass">Modern Glass style</option>
                            <option value="amoled">AMOLED widget style</option>
                            <option value="transparent">Transparent widget style</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 6. Micro interactions & Details -->
            <div>
                <label class="cp-block cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-text-slate-400 cp-mb-2">Advanced Personalization</label>
                <div class="cp-space-y-4 cp-p-3 cp-bg-white/5 cp-rounded-2xl cp-border cp-border-white/5">
                    <!-- Transparency -->
                    <div>
                        <div class="cp-flex cp-justify-between cp-items-center cp-mb-1">
                            <span class="cp-text-xs cp-font-medium cp-text-slate-400">Transparency</span>
                            <span class="cp-text-[10px] cp-text-slate-400" x-text="`${Math.round(state.theme_transparency * 100)}%`"></span>
                        </div>
                        <input type="range" min="0" max="1" step="0.05" x-model="state.theme_transparency" @input="updateTheme()" class="cp-w-full cp-accent-emerald-500">
                    </div>

                    <!-- Animations -->
                    <div>
                        <div class="cp-flex cp-justify-between cp-items-center cp-mb-1">
                            <span class="cp-text-xs cp-font-medium cp-text-slate-400">Animation speed</span>
                            <span class="cp-text-[10px] cp-text-slate-400" x-text="`${state.theme_animations}x`"></span>
                        </div>
                        <input type="range" min="0.1" max="2" step="0.1" x-model="state.theme_animations" @input="updateTheme()" class="cp-w-full cp-accent-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Restore Default Buttons -->
            <div class="cp-pt-4 cp-flex cp-gap-3">
                <button 
                    @click="resetDefaults()" 
                    class="cp-flex-1 cp-py-3 cp-bg-white/5 hover:cp-bg-white/10 cp-border cp-border-white/10 cp-text-slate-200 cp-font-semibold cp-rounded-xl cp-text-xs cp-transition-colors cp-duration-150">
                    Restore Defaults
                </button>
                <button 
                    @click="open = false" 
                    class="cp-flex-1 cp-py-3 cp-bg-gradient-to-r cp-from-indigo-600 cp-to-emerald-600 hover:cp-from-indigo-500 hover:cp-to-emerald-500 cp-text-white cp-font-bold cp-rounded-xl cp-text-xs cp-transition-colors cp-duration-150">
                    Apply Changes
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function themeCustomizer() {
        return {
            open: false,
            state: {
                theme_preset: 'default',
                theme_name: null,
                theme_primary_color: null,
                theme_accent_color: null,
                theme_card_style: null,
                theme_border_radius: null,
                theme_font_size: null,
                theme_font_family: null,
                theme_nav_style: null,
                theme_widget_style: null,
                theme_mode: 'dark',
                theme_transparency: null,
                theme_blur: null,
                theme_animations: null,
                theme_gradient_intensity: null,
            },

            presets: {
                default: 'Default (SASS Syles)',
                fintech: 'Modern Fintech',
                islamic: 'Minimal Islamic',
                cyber: 'Cyber Dark',
                elegant: 'Elegant Soft',
                glass: 'Glassmorphism',
                neo: 'Neo Modern',
                spiritual: 'Calm Spiritual'
            },

            themes: {
                amoled: 'AMOLED Dark',
                minimal_light: 'Minimal Light',
                islamic_emerald: 'Islamic Emerald',
                ocean_blue: 'Ocean Blue',
                midnight_purple: 'Midnight Purple',
                soft_gold: 'Soft Gold',
                modern_cyan: 'Modern Cyan',
                dynamic_gradient: 'Dynamic Gradient'
            },

            modes: {
                dark: 'Dark',
                light: 'Light',
                auto: 'Auto',
                scheduled: 'Schedule',
                battery: 'Battery'
            },

            presetConfigs: {
                default: {
                    theme_name: null,
                    theme_primary_color: null,
                    theme_accent_color: null,
                    theme_card_style: null,
                    theme_border_radius: null,
                    theme_font_size: null,
                    theme_font_family: null,
                    theme_nav_style: null,
                    theme_widget_style: null,
                    theme_mode: 'dark',
                    theme_transparency: null,
                    theme_blur: null,
                    theme_animations: null,
                    theme_gradient_intensity: null,
                },
                fintech: {
                    theme_name: 'ocean_blue',
                    theme_primary_color: '#00f2fe',
                    theme_accent_color: '#4facfe',
                    theme_card_style: 'flat',
                    theme_border_radius: '12px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Outfit',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'compact',
                    theme_mode: 'dark',
                    theme_transparency: 0.05,
                    theme_blur: '4px',
                    theme_animations: 1.0,
                    theme_gradient_intensity: 0.9,
                },
                islamic: {
                    theme_name: 'islamic_emerald',
                    theme_primary_color: '#065f46',
                    theme_accent_color: '#10b981',
                    theme_card_style: 'minimal',
                    theme_border_radius: '24px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Inter',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'minimal',
                    theme_mode: 'dark',
                    theme_transparency: 0.1,
                    theme_blur: '8px',
                    theme_animations: 0.8,
                    theme_gradient_intensity: 0.4,
                },
                cyber: {
                    theme_name: 'amoled',
                    theme_primary_color: '#00ffcc',
                    theme_accent_color: '#ff007f',
                    theme_card_style: 'cyber',
                    theme_border_radius: '0px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Courier New',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'amoled',
                    theme_mode: 'dark',
                    theme_transparency: 0.0,
                    theme_blur: '0px',
                    theme_animations: 1.5,
                    theme_gradient_intensity: 1.0,
                },
                elegant: {
                    theme_name: 'minimal_light',
                    theme_primary_color: '#f43f5e',
                    theme_accent_color: '#fda4af',
                    theme_card_style: 'soft',
                    theme_border_radius: '16px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Plus Jakarta Sans',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'minimal',
                    theme_mode: 'light',
                    theme_transparency: 0.1,
                    theme_blur: '6px',
                    theme_animations: 0.6,
                    theme_gradient_intensity: 0.5,
                },
                glass: {
                    theme_name: 'dynamic_gradient',
                    theme_primary_color: '#ffffff',
                    theme_accent_color: '#00f2fe',
                    theme_card_style: 'glass',
                    theme_border_radius: '24px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Outfit',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'glass',
                    theme_mode: 'dark',
                    theme_transparency: 0.6,
                    theme_blur: '24px',
                    theme_animations: 1.2,
                    theme_gradient_intensity: 0.9,
                },
                neo: {
                    theme_name: 'midnight_purple',
                    theme_primary_color: '#4f46e5',
                    theme_accent_color: '#06b6d4',
                    theme_card_style: 'neo',
                    theme_border_radius: '12px',
                    theme_font_size: 'medium',
                    theme_font_family: 'Outfit',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'glass',
                    theme_mode: 'dark',
                    theme_transparency: 0.2,
                    theme_blur: '10px',
                    theme_animations: 1.0,
                    theme_gradient_intensity: 0.85,
                },
                spiritual: {
                    theme_name: 'soft_gold',
                    theme_primary_color: '#0f766e',
                    theme_accent_color: '#0d9488',
                    theme_card_style: 'spiritual',
                    theme_border_radius: '32px',
                    theme_font_size: 'large',
                    theme_font_family: 'Playfair Display',
                    theme_nav_style: 'sidebar',
                    theme_widget_style: 'transparent',
                    theme_mode: 'dark',
                    theme_transparency: 0.35,
                    theme_blur: '16px',
                    theme_animations: 0.5,
                    theme_gradient_intensity: 0.6,
                }
            },

            init() {
                // Initialize state from window.__themeSettings if available
                if (window.__themeSettings) {
                    this.state = Object.assign({}, this.state, window.__themeSettings);
                }
            },

            selectPreset(key) {
                this.state.theme_preset = key;
                if (this.presetConfigs[key]) {
                    this.state = Object.assign({}, this.state, this.presetConfigs[key]);
                }
                this.updateTheme();
            },

            updateTheme() {
                localStorage.setItem('portal-theme-settings', JSON.stringify(this.state));
                
                // Sync with existing legacy theme modes
                if (this.state.theme_mode === 'light') {
                    localStorage.setItem('theme', 'light');
                    localStorage.setItem('site-theme', 'light');
                } else if (this.state.theme_mode === 'dark' || this.state.theme_mode === 'battery') {
                    localStorage.setItem('theme', 'dark');
                    localStorage.setItem('site-theme', 'dark');
                } else if (this.state.theme_mode === 'auto') {
                    localStorage.setItem('theme', 'auto');
                    localStorage.setItem('site-theme', 'auto');
                }

                if (window.__updatePortalTheme) {
                    window.__updatePortalTheme(this.state);
                }
            },

            resetDefaults() {
                // Remove all user-customized theme data from localStorage
                localStorage.removeItem('portal-theme-settings');
                localStorage.removeItem('theme');
                localStorage.removeItem('site-theme');

                // Restore state to admin defaults from the backend
                // window.__adminThemeDefaults is set by portal-dynamic-theme before any user overrides
                const adminDefaults = window.__adminThemeDefaults || {};
                this.state = Object.assign({}, this.state, adminDefaults);

                // Apply live without page reload
                if (window.__updatePortalTheme) {
                    window.__updatePortalTheme(adminDefaults);
                }
            }
        };
    }
</script>

<style>
    .cp-animate-spin-slow {
        animation: cp-spin 8s linear infinite;
    }
    @keyframes cp-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
