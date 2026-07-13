<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('All Packages') }} - {{ siteUrlSettings('site_name') ?? config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ site_image(siteUrlSettings('site_favicon')) }}" type="image/x-icon">
    
    @vite(['resources/sass/main-site.scss', 'resources/js/app.js'])

    <x-main-site-theme />
    <script>
        (function() {
            const adminDefaultTheme = "{{ siteUrlSettings('theme_mode') ?? 'dark' }}";
            const userPreferredTheme = localStorage.getItem('site-theme');
            const activeTheme = userPreferredTheme || adminDefaultTheme;
            if (activeTheme === 'light') {
                document.documentElement.classList.add('theme-light');
            } else {
                document.documentElement.classList.remove('theme-light');
            }
        })();
    </script>
</head>
<body class="container-fluid m-0 p-0 position-relative overflow-x-hidden">

    {{-- Background mesh gradient blobs --}}
    <div class="gradient-blob blob-1"></div>
    <div class="gradient-blob blob-2"></div>
    <div class="gradient-blob blob-3"></div>

    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    <header id="navigation" class="navbar sticky-top animated-header navbar-expand-md bg-light">
        <div class="container text-center">
            {{-- Logo / Brand --}}
            <a class="navbar-brand" href="{{ url('/') }}">
                @if (siteUrlSettings('site_logo'))
                    <img class="d-inline-block align-text-top" style="width:190px;height:53px;"
                        src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo" />
                @else
                    @if (siteUrlSettings('site_icon'))
                        <img class="d-inline-block align-text-top" src="{{ site_image(siteUrlSettings('site_icon')) }}"
                            alt="" width="40" />
                        <span
                            class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? config('app.name') }}</span>
                    @else
                        <span
                            class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? config('app.name') }}</span>
                    @endif
                @endif
            </a>

            {{-- Nav Links --}}
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="nav navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#banner">{{ __('Home') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#features">{{ __('Service') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#gallery">{{ __('Gallery') }}</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold text-success" href="{{ route('all-packages') }}">{{ __('Packages') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#team">{{ __('Team') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#blog">{{ __('Blog') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#testimonial">{{ __('Testimonial') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#contact-form">{{ __('Contact') }}</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center ms-auto ms-lg-3 me-2 gap-2">
                {{-- Language Switcher next to Theme Toggle --}}
                @php
                    $currentMainLocale = session()->get(
                        'main_site_locale',
                        siteUrlSettings('main_site_locale') ?? 'en',
                    );
                @endphp
                @if ($currentMainLocale === 'bn')
                    <a href="{{ route('welcome.lang', 'en') }}"
                        class="btn btn-outline-secondary btn-sm px-2.5 py-1 rounded-3 d-flex align-items-center gap-1 font-sans-serif fw-bold text-decoration-none border-secondary text-secondary"
                        style="font-size: 0.85rem; transition: all 0.3s ease;">
                        <i class="bi bi-globe"></i> EN
                    </a>
                @else
                    <a href="{{ route('welcome.lang', 'bn') }}"
                        class="btn btn-outline-secondary btn-sm px-2.5 py-1 rounded-3 d-flex align-items-center gap-1 font-sans-serif fw-bold text-decoration-none border-secondary text-secondary"
                        style="font-size: 0.85rem; transition: all 0.3s ease;">
                        <i class="bi bi-globe"></i> বাং
                    </a>
                @endif

                <button id="theme-toggle"
                    class="btn btn-link rounded-circle p-2 text-light text-decoration-none border-0" type="button"
                    aria-label="Toggle Theme">
                    <i class="bi bi-moon-stars" id="theme-toggle-icon" style="font-size: 1.25rem;"></i>
                </button>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </header>

    <header class="all-packages-header position-relative">
        <div class="container position-relative" style="z-index: 2;">
            <h1>{{ __('Our Internet Packages') }}</h1>
            <p class="fs-5 opacity-75">{{ __('Find the perfect high-speed broadband plan matching your needs') }}</p>
        </div>
    </header>

    <section id="pricing-table" class="pricing-section position-relative" style="z-index: 2;" 
        x-data="{
            search: '',
            selectedCategory: 'all',
            icons: [
                'bi-speedometer2',
                'bi-lightning-charge-fill',
                'bi-rocket-takeoff-fill',
                'bi-cpu',
                'bi-router',
                'bi-wifi',
                'bi-globe',
                'bi-activity',
                'bi-ethernet',
                'bi-cloud-arrow-down-fill'
            ],
            packages: [
                @foreach($packages as $package)
                @php
                    $detectedSpeed = $package->speed;
                    if (empty($detectedSpeed)) {
                        if (preg_match('/_(\d+)M$/i', $package->package, $matches)) {
                            $detectedSpeed = $matches[1] . ' Mbps';
                        } elseif (is_numeric($package->description)) {
                            $detectedSpeed = $package->description . ' Mbps';
                        } else {
                            $detectedSpeed = 'Standard';
                        }
                    }
                @endphp
                {
                    id: {{ $package->id }},
                    package: '{{ addslashes($package->package) }}',
                    plan_label: '{{ addslashes($package->plan_label ?? 'Standard') }}',
                    speed: '{{ addslashes($detectedSpeed) }}',
                    price: {{ $package->price }},
                    is_featured: {{ $package->is_featured ? 'true' : 'false' }},
                    features: [
                        @if($package->features && count($package->features) > 0)
                            @foreach($package->features as $feature)
                                '{{ addslashes($feature['value'] ?? $feature) }}',
                            @endforeach
                        @else
                            '{{ __('24 HOURS UNLIMITED') }}',
                            '{{ __('Fiber Optics Support') }}',
                            '{{ __('24/7 Priority Support') }}'
                        @endif
                    ],
                    description: '{{ addslashes($package->description ?? '') }}'
                },
                @endforeach
            ],
            getIcon(packageName) {
                let hash = 0;
                const name = packageName || '';
                for (let i = 0; i < name.length; i++) {
                    hash = name.charCodeAt(i) + ((hash << 5) - hash);
                }
                const idx = Math.abs(hash) % this.icons.length;
                return this.icons[idx];
            },
            getCategoryName(label) {
                if (!label || label.toLowerCase() === 'standard') return 'Standard';
                // If label is like FCOM-1_5M, return the part before hyphen (e.g. FCOM)
                if (label.includes('-')) {
                    return label.split('-')[0].trim();
                }
                return label;
            },
            get filteredPackages() {
                const query = this.search.toLowerCase().trim();
                return this.packages.filter(p => {
                    const matchesSearch = p.package.toLowerCase().includes(query) || 
                                          p.plan_label.toLowerCase().includes(query) ||
                                          p.speed.toLowerCase().includes(query) ||
                                          (p.description && p.description.toLowerCase().includes(query));
                    const pCat = this.getCategoryName(p.plan_label).toLowerCase();
                    const matchesCategory = this.selectedCategory === 'all' || pCat === this.selectedCategory.toLowerCase();
                    return matchesSearch && matchesCategory;
                });
            },
            get categories() {
                const labels = new Set(this.packages.map(p => this.getCategoryName(p.plan_label)));
                return ['all', ...Array.from(labels).filter(Boolean)];
            }
        }">
        
        <div class="container">
            <!-- Search and Filter Bar -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 col-lg-6 text-center">
                    <!-- Normal Search Box styled like Contact Form Input -->
                    <div class="position-relative mb-4">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3" style="z-index: 10; color: #6b7280;">
                            <i class="bi bi-search fs-5"></i>
                        </span>
                        <input x-model="search" type="text" class="contact-style-search" 
                               placeholder="{{ __('Search packages (e.g. 20 Mbps, Home)...') }}">
                    </div>
                    
                    <!-- Dynamic Category Pills styled like Gallery Filters -->
                    <div class="recent-work-mixMenu mt-4">
                        <ul>
                            <template x-for="cat in categories" :key="cat">
                                <li>
                                    <button type="button" @click="selectedCategory = cat"  
                                            class="text-success"
                                            :class="{ 'active': selectedCategory === cat }"
                                            x-text="cat === 'all' ? '{{ __('ALL PLANS') }}' : (cat.toUpperCase() + ' PLANS')">
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Dynamic Package List -->
            <div class="row justify-content-center">
                @php
                    $pkgColors = ['', 'pricing-box-2', 'pricing-box-3', ''];
                @endphp
                @foreach($packages as $index => $package)
                    @php
                        // Run speed detection logic
                        $detectedSpeed = $package->speed;
                        if (empty($detectedSpeed)) {
                            if (preg_match('/_(\d+)M$/i', $package->package, $matches)) {
                                $detectedSpeed = $matches[1] . ' Mbps';
                            } elseif (is_numeric($package->description)) {
                                $detectedSpeed = $package->description . ' Mbps';
                            } else {
                                $detectedSpeed = 'Standard';
                            }
                        }
                        
                        // Clean category name
                        $cleanCat = 'Standard';
                        if ($package->plan_label && strtolower($package->plan_label) !== 'standard') {
                            if (str_contains($package->plan_label, '-')) {
                                $cleanCat = explode('-', $package->plan_label)[0];
                            } else {
                                $cleanCat = $package->plan_label;
                            }
                        }
                        $cleanCat = trim($cleanCat);
                    @endphp
                    <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-6 mb-4"
                         x-show="(selectedCategory === 'all' || '{{ strtolower($cleanCat) }}' === selectedCategory.toLowerCase()) && 
                                 ('{{ strtolower($package->package) }}'.includes(search.toLowerCase().trim()) || 
                                  '{{ strtolower($cleanCat) }}'.includes(search.toLowerCase().trim()) || 
                                  '{{ strtolower($detectedSpeed) }}'.includes(search.toLowerCase().trim()) ||
                                  '{{ strtolower($package->description ?? '') }}'.includes(search.toLowerCase().trim()))">
                        <div class="d-flex h-100">
                            <x-package-card :package="$package" :color-class="$pkgColors[$index % 4]" />
                        </div>
                    </div>
                @endforeach
                
                <!-- No Results State -->
                <div class="col-md-12 text-center py-5" x-show="filteredPackages.length === 0" x-cloak>
                    <div class="display-1 text-muted mb-3"><i class="bi bi-slash-circle"></i></div>
                    <h4 class="text-muted">{{ __('No Packages Found') }}</h4>
                    <p class="text-muted">{{ __('Try adjusting your search terms or category filters.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
         FOOTER
    ========================================================== --}}
    <footer>
        <div class="container">
            <div class="row">
                <!-- Column 1: Company Info & Socials -->
                <div class="col-lg-4 col-md-6 col-12 mb-4 mb-lg-0">
                    <div>
                        <a href="#navigation">
                            @if (siteUrlSettings('site_logo'))
                                <img class="d-inline-block align-text-top mb-3" style="max-width: 190px;"
                                    src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo" />
                            @else
                                <h3 class="text-white fw-bold">
                                    {{ siteUrlSettings('site_name') ?? config('app.name') }}</h3>
                            @endif
                        </a>
                        <p class="mb-1"><i
                                class="bi bi-geo-alt me-2"></i>{{ siteUrlSettings('site_address') ?? __('Our Head Office') }}
                        </p>
                        <p class="mb-1"><i
                                class="bi bi-telephone me-2"></i>{{ siteUrlSettings('site_phone') ?? '01700000000' }}
                        </p>
                        <p class="mb-3"><i
                                class="bi bi-envelope me-2"></i>{{ siteUrlSettings('site_email') ?? 'support@example.com' }}
                        </p>



                        {{-- Social Links --}}
                        @php
                            $fb = siteUrlSettings('site_facebook');
                            $tw = siteUrlSettings('site_twitter');
                            $ig = siteUrlSettings('site_instagram');
                            $yt = siteUrlSettings('site_youtube');
                            $wa = siteUrlSettings('site_whatsapp');
                        @endphp
                        <div class="mt-2 d-flex gap-3">
                            @if ($fb)
                                <a href="{{ $fb }}" target="_blank" class="social-link"><i
                                        class="bi bi-facebook"></i></a>
                            @endif
                            @if ($tw)
                                <a href="{{ $tw }}" target="_blank" class="social-link"><i
                                        class="bi bi-twitter-x"></i></a>
                            @endif
                            @if ($ig)
                                <a href="{{ $ig }}" target="_blank" class="social-link"><i
                                        class="bi bi-instagram"></i></a>
                            @endif
                            @if ($yt)
                                <a href="{{ $yt }}" target="_blank" class="social-link"><i
                                        class="bi bi-youtube"></i></a>
                            @endif
                            @if ($wa)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wa) }}" target="_blank"
                                    class="social-link"><i class="bi bi-whatsapp"></i></a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-3 col-md-6 col-6 mb-4 mb-lg-0">
                    <h5 class="text-light fw-bold mb-3" style="font-size: 1.1rem;">{{ __('Quick Links') }}</h5>
                    <ul class="footer-links-list">
                        <li><a href="https://portal.{{ request()->getHost() }}"
                                target="_blank">{{ __('Client Portal') }}</a></li>
                        <li><a href="{{ route('policy.show') }}">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="{{ route('terms.show') }}">{{ __('Terms & Conditions') }}</a></li>
                        <li><a href="{{ $siteData->btcl_tariff_link ?? '#' }}"
                                target="_blank">{{ __('BTCL Tariff PDF') }}</a></li>
                    </ul>
                </div>

                <!-- Column 3: Useful Links -->
                <div class="col-lg-3 col-md-6 col-6 mb-4 mb-lg-0">
                    <h5 class="text-light fw-bold mb-3" style="font-size: 1.1rem;">{{ __('Useful Links') }}</h5>
                    <ul class="footer-links-list">
                        @if (!empty($siteData->important_links))
                            @foreach ($siteData->important_links as $link)
                                @if (!empty($link['label']) && !empty($link['url']))
                                    <li><a href="{{ $link['url'] }}" target="_blank">{{ $link['label'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @else
                            <li class="text-muted small">{{ __('No links configured.') }}</li>
                        @endif
                    </ul>
                </div>

                <!-- Column 4: Visitor Counters -->
                <div class="col-lg-2 col-md-6 col-12 text-lg-end text-start">
                    <div class="row g-2">
                        <div class="col-lg-12 col-6 mb-3 mb-lg-3">
                            <h6 class="mb-1 text-uppercase visitor-counter-title total-title"
                                style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ __('Total Page Views') }}
                            </h6>
                            <div class="visitor-counter-badge justify-content-lg-end justify-content-start">
                                @php
                                    $formattedTotal = sprintf('%06d', $totalVisits ?? 0);
                                    $totalDigits = str_split($formattedTotal);
                                @endphp
                                @foreach ($totalDigits as $digit)
                                    <span class="visitor-digit total-digit">{{ $digit }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-lg-12 col-6">
                            <h6 class="mb-1 text-uppercase visitor-counter-title unique-title"
                                style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ __('Unique Visitors') }}
                            </h6>
                            <div class="visitor-counter-badge justify-content-lg-end justify-content-start">
                                @php
                                    $formattedUnique = sprintf('%06d', $uniqueVisitors ?? 0);
                                    $uniqueDigits = str_split($formattedUnique);
                                @endphp
                                @foreach ($uniqueDigits as $digit)
                                    <span class="visitor-digit unique-digit">{{ $digit }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="mt-4 mb-3 border-secondary opacity-25">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start text-center mb-2 mb-md-0">
                        <p class="text-muted small mb-0">
                            © {{ siteUrlSettings('site_name') ?? config('app.name') }} {{ date('Y') }}. All Rights Reserved
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <p class="text-muted small mb-0">
                            Designed & Develoved by : <a href="https://codepagol.com" target="_blank" class="text-muted text-decoration-none fw-semibold">CodePagol</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <livewire:package-purchase-form />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeToggleIcon = document.getElementById('theme-toggle-icon');

            function updateToggleIcon(isLight) {
                if (isLight) {
                    themeToggleIcon.className = 'bi bi-sun';
                    themeToggle.classList.remove('text-light');
                    themeToggle.classList.add('text-dark');
                } else {
                    themeToggleIcon.className = 'bi bi-moon-stars';
                    themeToggle.classList.remove('text-dark');
                    themeToggle.classList.add('text-light');
                }
            }

            if (themeToggle && themeToggleIcon) {
                // Initial setup
                const isLight = document.documentElement.classList.contains('theme-light');
                updateToggleIcon(isLight);

                themeToggle.addEventListener('click', function() {
                    const currentlyLight = document.documentElement.classList.toggle('theme-light');
                    localStorage.setItem('site-theme', currentlyLight ? 'light' : 'dark');
                    updateToggleIcon(currentlyLight);
                });
            }
        });
    </script>
</body>
</html>
