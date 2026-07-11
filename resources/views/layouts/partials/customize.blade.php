
<div class="offcanvas offcanvas-end settings-panel border-0" id="settings-offcanvas" tabindex="-1" aria-labelledby="settings-offcanvas">
    <div class="offcanvas-header settings-panel-header justify-content-between bg-shape">
        <div class="z-1 py-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="text-white mb-0 me-2">
                    <span class="bi bi-palette fs-9"></span>
                    {{ __('Settings') }}
                </h5>
                <button class="btn btn-primary btn-sm rounded-pill mt-0 mb-0" data-theme-control="reset" style="font-size:12px">
                    <span class="bi bi-arrow-clockwise me-1"></span>
                    {{ __('Reset') }}
                </button>
            </div>
            <p class="mb-0 fs-10 text-white opacity-75"> {{ __('Set your own customized style') }}</p>
        </div>
        <div class="z-1" data-bs-theme="dark">
            <button class="btn-close z-1 mt-0" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body scrollbar-overlay px-x1 h-100" id="themeController">
        <h5 class="fs-9">{{ __('Color Scheme') }}</h5>
        <p class="fs-10">{{ __('Choose the perfect color mode for your app.') }}</p>
        <div class="btn-group d-block w-100 btn-group-navbar-style">
            <div x-data="themeToggle()" x-init="init()" class="row gx-2">
                <div class="col-4">
                    <input @click="setTheme('auto')" :checked="theme === 'auto'" class="btn-check" id="themeSwitcherAuto" name="theme-color" type="radio" value="auto" data-theme-control="theme" />
                    <label class="btn d-inline-block btn-navbar-style fs-10" for="themeSwitcherAuto">
                        <span class="hover-overlay mb-2 rounded d-block">
                            <img class="img-fluid img-prototype mb-0" src="{{asset('images/mode-auto.png')}}" alt=""/>
                        </span>
                        <span class="label-text"> {{ __('Auto') }}</span>
                    </label>
                </div>
                <div class="col-4">
                    <input @click="setTheme('light')" :checked="theme === 'light'" class="btn-check" id="themeSwitcherLight" name="theme-color" type="radio" value="light" data-theme-control="theme" />
                    <label class="btn d-inline-block btn-navbar-style fs-10" for="themeSwitcherLight">
                        <span class="hover-overlay mb-2 rounded d-block">
                            <img class="img-fluid img-prototype mb-0" src="{{asset('images/mode-light.png')}}" alt=""/>
                        </span>
                        <span class="label-text"> {{ __('Light') }}</span>
                    </label>
                </div>
                <div class="col-4">
                    <input @click="setTheme('dark')" :checked="theme === 'dark'" class="btn-check" id="themeSwitcherDark" name="theme-color" type="radio" value="dark" data-theme-control="theme" />
                    <label class="btn d-inline-block btn-navbar-style fs-10" for="themeSwitcherDark">
                        <span class="hover-overlay mb-2 rounded d-block">
                            <img class="img-fluid img-prototype mb-0" src="{{asset('images/mode-dark.png')}}" alt=""/>
                        </span>
                        <span class="label-text"> {{ __('Dark') }}</span>
                    </label>
                </div>
            </div>
        </div>
        <hr />
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
                <i class="bi bi-arrow-bar-left me-2 fs-5 fw-bold text-primary"></i>
                <div class="flex-1">
                    <h5 class="fs-9">{{ __('RTL Mode') }}</h5>
                    <p class="fs-10 mb-0">{{ __('Switch your language direction') }} </p>
                </div>
            </div>
            <div x-data="rtlController()" x-init="initRTL()" class="form-check form-switch"><input @click="toggleRTL()" class="form-check-input ms-0" id="mode-rtl" type="checkbox" data-theme-control="isRTL" /></div>
        </div>
        <hr />
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
                <i class="bi bi-arrows-expand-vertical me-2 fs-5 fw-bold text-primary"></i>
                <div class="flex-1">
                    <h5 class="fs-9">{{ __('Fluid Layout') }}</h5>
                    <p class="fs-10 mb-0">{{ __('Toggle container layout system') }} </p>
                </div>
            </div>
            <div x-data="layoutController()" x-init="initLayout()" class="form-check form-switch">
                <input @change="toggleLayout()" :checked="isFluid" class="form-check-input ms-0" id="mode-fluid" type="checkbox" data-theme-control="isFluid" />
            </div>
        </div>
        <hr />
        <div class="d-flex align-items-start">
            <i class="bi bi-filter-left me-2 fs-5 fw-bold text-primary"></i>
            <div class="flex-1">
                <h5 class="fs-9 d-flex align-items-center">{{ __('Navigation Position') }}</h5>
                <p class="fs-10 mb-2">{{ __('Select a suitable navigation system for your web application') }} </p>
                <div x-data="navbarPosition()" x-init="initNavPosition()">
                    <select
                        x-model="isNavbarPosition"
                        @change="setNavbarPosition($event.target.value)"
                        class="form-select form-select-sm"
                        aria-label="Navbar position"
                    >
                        <option value="vertical">{{ __('Vertical') }}</option>
                        <option value="top">{{ __('Top') }}</option>
                        <option class="d-none d-md-none d-lg-block" value="combo">{{ __('Combo') }}</option>
                        <option value="double-top">{{ __('Double Top') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <hr />
        <h5 class="fs-9 d-flex align-items-center">{{ __('Vertical Navbar Style') }}</h5>
        <p class="fs-10 mb-0">{{ __('Switch between styles for your vertical navbar') }} </p>
        <div class="btn-group d-block w-100 btn-group-navbar-style">
            <div class="row gx-2" x-data="verticalNavbarStyle()" x-init="initNavStyle()">
                <div class="col-6"><input @click="setNavbarStyle('transparent')" :checked="isNavbarStyle === 'transparent'" class="btn-check" id="navbar-style-transparent" type="radio" name="navbarStyle" value="transparent" data-theme-control="navbarStyle" /><label class="btn d-block w-100 btn-navbar-style fs-10" for="navbar-style-transparent"> <img class="img-fluid img-prototype" src="{{asset('images/default.png')}}" alt="" /><span class="label-text"> {{ __('Transparent') }}</span></label></div>
                <div class="col-6"><input @click="setNavbarStyle('inverted')" :checked="isNavbarStyle === 'inverted'" class="btn-check" id="navbar-style-inverted" type="radio" name="navbarStyle" value="inverted" data-theme-control="navbarStyle" /><label class="btn d-block w-100 btn-navbar-style fs-10" for="navbar-style-inverted"> <img class="img-fluid img-prototype" src="{{asset('images/inverted.png')}}" alt="" /><span class="label-text"> {{ __('Inverted') }}</span></label></div>
                <div class="col-6"><input @click="setNavbarStyle('card')" :checked="isNavbarStyle === 'card'" class="btn-check" id="navbar-style-card" type="radio" name="navbarStyle" value="card" data-theme-control="navbarStyle" /><label class="btn d-block w-100 btn-navbar-style fs-10" for="navbar-style-card"> <img class="img-fluid img-prototype" src="{{asset('images/card.png')}}" alt="" /><span class="label-text"> {{ __('Card') }}</span></label></div>
                <div class="col-6"><input @click="setNavbarStyle('vibrant')" :checked="isNavbarStyle === 'vibrant'" class="btn-check" id="navbar-style-vibrant" type="radio" name="navbarStyle" value="vibrant" data-theme-control="navbarStyle" /><label class="btn d-block w-100 btn-navbar-style fs-10" for="navbar-style-vibrant"> <img class="img-fluid img-prototype" src="{{asset('images/vibrant.png')}}" alt="" /><span class="label-text"> {{ __('Vibrant') }}</span></label></div>
            </div>
        </div>
    </div>
</div>

<style>
    .setting-toggle {
        right: -2.65% !important;
        transition: right 0.3s ease-in-out; /* Add transition for smooth animation */
    }

    .setting-toggle:hover {
        right: 0 !important; /* Move to 0 on hover */
    }
</style>
