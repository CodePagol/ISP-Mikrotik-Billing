{{-- Dynamic Main Site - Controlled from Billing Admin Panel --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>{{ $siteData?->hero_title ?? (siteUrlSettings('site_name') ?? config('app.name')) }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ siteUrlSettings('site_description') ?? '' }}">

    <link rel="shortcut icon" href="{{ site_image(siteUrlSettings('site_favicon')) }}" type="image/x-icon">

    @vite(['resources/sass/main-site.scss', 'resources/js/main-site.js'])

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
    <script>
        @php
            $tz = config('app.timezone', 'Asia/Dhaka');
            $phoneCountry = 'bd';
            if (class_exists('IntlTimeZone')) {
                $region = \IntlTimeZone::getRegion($tz);
                if ($region && strlen($region) === 2) {
                    $phoneCountry = strtolower($region);
                }
            }
        @endphp
        window.sitePhoneCountry = '{{ $phoneCountry }}';
    </script>
</head>

<body id="top" class="container-fluid m-0 p-0 position-relative overflow-x-hidden">

    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    <header id="navigation" class="navbar sticky-top animated-header navbar-expand-md bg-light">
        <div class="container text-center">
            {{-- Logo / Brand --}}
            <a class="navbar-brand" href="#top">
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
                    <li class="nav-item"><a class="nav-link" href="#banner">{{ __('Home') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">{{ __('Service') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">{{ __('Gallery') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing-table">{{ __('Price') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">{{ __('Team') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#blog">{{ __('Blog') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonial">{{ __('Testimonial') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact-form">{{ __('Contact') }}</a></li>
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


    <div data-bs-spy="scroll" data-bs-target="#navigation" data-bs-root-margin="0px 0px -40%"
        data-bs-smooth-scroll="true" class="scrollspy-example bg-light rounded-2 wrapper" tabindex="0">

        {{-- =========================================================
             HERO / BANNER SLIDER
        ========================================================== --}}
        <section id="banner">
            <div id="carouselExampleCaptions" class="carousel slide carousel-fade" data-bs-ride="carousel"
                data-bs-interval="7000">

                {{-- Indicators --}}
                @php $slides = $siteData?->hero_slides ?? []; @endphp
                @if (count($slides) > 0)
                    <div class="carousel-indicators">
                        @foreach ($slides as $index => $slide)
                            <button type="button" data-bs-target="#carouselExampleCaptions"
                                data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"
                                @if ($index === 0) aria-current="true" @endif
                                aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif

                {{-- Slides --}}
                <div class="carousel-inner">
                    @if (count($slides) > 0)
                        @foreach ($slides as $index => $slide)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ isset($slide['image']) ? site_image($slide['image']) : '' }}"
                                    class="img-fluid" style="width: 100%; height: auto; object-fit: cover;"
                                    alt="{{ $slide['caption'] ?? 'Slide ' . ($index + 1) }}">
                                @if (!empty($slide['caption']))
                                    <div class="carousel-caption d-none d-md-block">
                                        <h2 class="display-4 fw-bold">{{ $slide['caption'] }}</h2>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        {{-- Fallback static slides --}}
                        <div class="carousel-item active">
                            <img src="{{ asset('images/slide/img0.jpg') }}" class="img-fluid"
                                alt="{{ __('Slide 1') }}">
                        </div>
                        <div class="carousel-item">
                            <img src="{{ asset('images/slide/img1.jpg') }}" class="img-fluid"
                                alt="{{ __('Slide 2') }}">
                        </div>
                        <div class="carousel-item">
                            <img src="{{ asset('images/slide/img2.jpg') }}" class="img-fluid"
                                alt="{{ __('Slide 3') }}">
                        </div>
                    @endif
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>


        {{-- =========================================================
             FEATURES / SERVICES
        ========================================================== --}}
        <section id="features" class="pb-2">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            @if ($siteData?->about_title)
                                <h5 class="text-success">{{ $siteData->about_title }}</h5>
                            @else
                                <h5 class="text-success">{{ __('Welcome to') }}
                                    {{ siteUrlSettings('portal_name') ?? siteUrlSettings('site_name') }}</h5>
                            @endif
                            <h2>{{ $siteData?->hero_title ?? __('We are always Faster & Reliable') }}</h2>
                            @if ($siteData?->about_body)
                                <p>{!! nl2br(e($siteData->about_body)) !!}</p>
                            @elseif($siteData?->hero_subtitle)
                                <p>{{ $siteData->hero_subtitle }}</p>
                            @endif
                            <h4>{{ __('Our Services are') }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Service Cards --}}
                @php $services = $siteData?->services ?? []; @endphp
                @if (count($services) > 0)
                    <div class="row">
                        @foreach ($services as $service)
                            <div class="col-md-4 col-xs-6 col-sm-6">
                                <div class="feature-block text-center">
                                    <div class="icon-box">
                                        <i class="{{ $service['icon'] ?? 'bi bi-wifi' }}"></i>
                                    </div>
                                    <h4 class="wow fadeInUp" data-wow-delay=".3s">{{ $service['title'] ?? '' }}</h4>
                                    <p class="wow fadeInUp" data-wow-delay=".5s">{{ $service['description'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Fallback static services --}}
                    <div class="row">
                        <div class="col-md-4 col-xs-6 col-sm-6">
                            <div class="feature-block text-center">
                                <div class="icon-box"><i class="bi bi-house-fill"></i></div>
                                <h4 class="wow fadeInUp" data-wow-delay=".3s">{{ __('Home Internet') }}</h4>
                                <p class="wow fadeInUp" data-wow-delay=".5s">
                                    {{ __('High-speed broadband internet for your home. Unlimited data, 24/7 uptime.') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-xs-6 col-sm-6">
                            <div class="feature-block text-center">
                                <div class="icon-box"><i class="bi bi-building-fill-check"></i></div>
                                <h4 class="wow fadeInUp" data-wow-delay=".3s">{{ __('Corporate Internet') }}</h4>
                                <p class="wow fadeInUp" data-wow-delay=".5s">
                                    {{ __('Dedicated business-grade connectivity with SLA guarantees and priority support.') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-xs-6 col-sm-6">
                            <div class="feature-block text-center">
                                <div class="icon-box"><i class="bi bi-hdd-network-fill"></i></div>
                                <h4 class="wow fadeInUp" data-wow-delay=".3s">{{ __('Data Connectivity') }}</h4>
                                <p class="wow fadeInUp" data-wow-delay=".5s">
                                    {{ __('Fiber optic point-to-point links for enterprise and campus connectivity needs.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- =========================================================
            Valuable Clint
        ========================================================== --}}
        <section id="client-logo" class="py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title p-0">
                            <h2 class="text-success">{{ __('Our Valuable Clients') }}</h2>
                        </div>
                    </div>
                </div>
                @php
                    $clients = $siteData?->valuable_clients ?? [];
                    if (count($clients) === 0) {
                        $clients = [
                            ['name' => 'Google'],
                            ['name' => 'Microsoft'],
                            ['name' => 'Amazon'],
                            ['name' => 'Facebook'],
                            ['name' => 'Twitter'],
                            ['name' => 'Apple'],
                            ['name' => 'Intel'],
                            ['name' => 'IBM'],
                            ['name' => 'Oracle'],
                        ];
                    }
                @endphp
                <div class="marquee-container">
                    <div class="marquee-content">
                        @foreach ($clients as $client)
                            <div class="client-item-marquee">
                                @if (!empty($client['link']))
                                    <a class="text-decoration-none" href="{{ $client['link'] }}" target="_blank"
                                        title="{{ $client['name'] }}">
                                @endif

                                <div class="client-name-design">
                                    @if (!empty($client['logo']))
                                        <img class="client-logo-img" src="{{ site_image($client['logo']) }}"
                                            alt="{{ $client['name'] }}">
                                    @endif

                                    @if (!empty($client['name']))
                                        <span>{{ $client['name'] }}</span>
                                    @else
                                        <span>{{ $client['name'] }}</span>
                                    @endif
                                </div>
                                @if (!empty($client['link']))
                                    </a>
                                @endif
                            </div>
                        @endforeach
                        {{-- Duplicate items for infinite scroll effect --}}
                        @if (count($clients) < 6)
                            @foreach ($clients as $client)
                                <div class="client-item-marquee">
                                    @if (!empty($client['link']))
                                        <a class="text-decoration-none" href="{{ $client['link'] }}" target="_blank"
                                            title="{{ $client['name'] }}">
                                    @endif

                                    <div class="client-name-design">
                                        @if (!empty($client['logo']))
                                            <img class="client-logo-img" src="{{ site_image($client['logo']) }}"
                                                alt="{{ $client['name'] }}">
                                        @endif

                                        @if (!empty($client['name']))
                                            <span>{{ $client['name'] }}</span>
                                        @else
                                            <span>{{ $client['name'] }}</span>
                                        @endif
                                    </div>
                                    @if (!empty($client['link']))
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             GALLERY
        ========================================================== --}}
        <section id="gallery" class="bg-success">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title p-0">
                            <h2>{{ __('LATEST WORKS') }}</h2>
                        </div>
                        <div x-data="{ filter: 'all' }">
                            <div class="recent-work-mixMenu">
                                <ul>
                                    <li><button type="button" @click="filter='all'"
                                            :class="{ 'active': filter === 'all' }">{{ __('All') }}</button>
                                    </li>
                                    @php $cats = $siteData?->gallery_categories ?? []; @endphp
                                    @if (count($cats) > 0)
                                        @foreach ($cats as $cat)
                                            @php $catKey = $cat['key'] ?? ($cat['label'] ?? ''); @endphp
                                            <li><button type="button" @click="filter='{{ $catKey }}'"
                                                    :class="{ 'active': filter === '{{ $catKey }}' }">{{ $cat['label'] ?? ($cat['key'] ?? '') }}</button>
                                            </li>
                                        @endforeach
                                    @else
                                        <li><button type="button" @click="filter='category-1'"
                                                :class="{ 'active': filter === 'category-1' }">{{ __('Equipment') }}</button>
                                        </li>
                                        <li><button type="button" @click="filter='category-2'"
                                                :class="{ 'active': filter === 'category-2' }">{{ __('Server') }}</button>
                                        </li>
                                        <li><button type="button" @click="filter='category-3'"
                                                :class="{ 'active': filter === 'category-3' }">{{ __('Illustration') }}</button>
                                        </li>
                                        <li><button type="button" @click="filter='category-4'"
                                                :class="{ 'active': filter === 'category-4' }">{{ __('Media') }}</button>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <div class="recent-work-pic container">
                                <ul id="gallery-images" class="row d-flex justify-content-center">
                                    @php $galleryItems = $siteData?->gallery_items ?? []; @endphp
                                    @if (count($galleryItems) > 0)
                                        @foreach ($galleryItems as $index => $item)
                                            @php $itemCat = $item['category'] ?? 'category-1'; @endphp
                                            <li :class="filter === 'all' || filter === '{{ $itemCat }}' ?
                                                'gallery-show' : 'gallery-hide'"
                                                class="mix {{ $itemCat }} col-md-2 col-sm-3 col-4 position-relative"
                                                data-my-order="{{ $index + 1 }}">
                                                <div class="gallery-item-wrapper position-relative">
                                                    <a class="gallery-items-link d-block" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                        data-bs-image="{{ site_image($item['image']) }}"
                                                        data-bs-caption="{{ $item['caption'] ?? '' }}">
                                                        <img class="img-thumbnail"
                                                            src="{{ site_image($item['image']) }}"
                                                            alt="{{ $item['caption'] ?? '' }}">
                                                        <div class="overlay">
                                                            <h3>{{ $item['caption'] ?? __('View') }}</h3>
                                                            <i class="bi bi-diagram-3-fill"></i>
                                                        </div>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        {{-- Fallback static gallery --}}
                                        <li :class="filter === 'all' || filter === 'category-1' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-1 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/spliceing.jpg') }}"
                                                    data-bs-caption="{{ __('Splicing') }}">
                                                    <img class="img-thumbnail" src="images/gallery/spliceing.jpg"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Splicing') }}</h3><i
                                                            class="bi bi-diagram-3-fill"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                        <li :class="filter === 'all' || filter === 'category-1' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-1 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/Clever.png') }}"
                                                    data-bs-caption="{{ __('Clever') }}">
                                                    <img class="img-thumbnail" src="images/gallery/Clever.png"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Clever') }}</h3><i
                                                            class="bi bi-diagram-3-fill"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                        <li :class="filter === 'all' || filter === 'category-1' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-1 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/crimping.jpg') }}"
                                                    data-bs-caption="{{ __('Crimping') }}">
                                                    <img class="img-thumbnail" src="images/gallery/crimping.jpg"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Crimping') }}</h3><i
                                                            class="bi bi-diagram-3-fill"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                        <li :class="filter === 'all' || filter === 'category-2' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-2 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/server.jpg') }}"
                                                    data-bs-caption="{{ __('Server') }}">
                                                    <img class="img-thumbnail" src="images/gallery/server.jpg"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Server') }}</h3><i class="bi bi-server"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                        <li :class="filter === 'all' || filter === 'category-2' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-2 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/rack.jpg') }}"
                                                    data-bs-caption="{{ __('Rack') }}">
                                                    <img class="img-thumbnail" src="images/gallery/rack.jpg"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Rack') }}</h3><i class="bi bi-server"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                        <li :class="filter === 'all' || filter === 'category-3' ? 'gallery-show' :
                                            'gallery-hide'"
                                            class="mix category-3 col-md-2 col-sm-3 col-4 position-relative">
                                            <div class="gallery-item-wrapper position-relative">
                                                <a class="gallery-items-link d-block" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                    data-bs-image="{{ asset('images/gallery/Patchcord.jpeg') }}"
                                                    data-bs-caption="{{ __('Patchcord') }}">
                                                    <img class="img-thumbnail" src="images/gallery/Patchcord.jpeg"
                                                        alt="">
                                                    <div class="overlay">
                                                        <h3>{{ __('Patchcord') }}</h3><i class="bi bi-ethernet"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Bootstrap Gallery Modal -->
                            <div class="modal fade" id="galleryModal" tabindex="-1"
                                aria-labelledby="galleryModalLabel" aria-hidden="true" style="z-index: 2050;">
                                <div class="modal-dialog modal-dialog-centered"
                                    style="max-width: fit-content; margin: 1.75rem auto;">
                                    <div class="modal-content border-0 bg-transparent shadow-none position-relative mx-auto"
                                        style="width: fit-content;">
                                        <!-- Float Close Button -->
                                        <button type="button"
                                            class="btn border-0 text-white position-absolute top-0 end-0 m-2 shadow-none"
                                            data-bs-dismiss="modal" aria-label="Close"
                                            style="font-size: 2rem; z-index: 1055; text-shadow: 0 2px 10px rgba(0,0,0,0.8);">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="modal-body text-center p-0">
                                            <img id="galleryModalImage" src=""
                                                class="img-fluid rounded-4 shadow-lg"
                                                style="max-height: 80vh; max-width: 90vw; object-fit: contain; border: 4px solid rgba(255,255,255,0.15);">
                                            <!-- Caption -->
                                            <h5 class="text-white mt-3 fw-bold font-sans-serif" id="galleryModalLabel"
                                                style="text-shadow: 0 2px 8px rgba(0,0,0,0.8); font-size: 1.25rem;">
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- =========================================================
             PRICING TABLE
        ========================================================== --}}
        <section id="pricing-table">
            <div class="container">
                <div class="row">
                    <div class="title">
                        <h2 class="p-0">{{ $siteData?->packages_section_title ?? __('INTERNET PACKAGE PLAN') }}
                        </h2>
                        <h5 class="text-success mb-3">
                            {{ $siteData?->packages_section_subtitle ?? __('We offer the best Internet Package Plan for You') }}
                        </h5>
                    </div>
                </div>

                @php
                    $pkgColors = ['', 'pricing-box-2', 'pricing-box-3', ''];
                @endphp

                @if ($packages->isNotEmpty())
                    <div class="px-md-5 position-relative">
                        <div class="swiper pricing-swiper">
                            <div class="swiper-wrapper">
                                @foreach ($packages as $index => $package)
                                    <div class="swiper-slide h-auto d-flex">
                                        <x-package-card :package="$package" :color-class="$pkgColors[$index % 4]" />
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="swiper-pagination mt-4"></div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="swiper-button-prev pricing-prev-btn"></div>
                        <div class="swiper-button-next pricing-next-btn"></div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">
                            {{ __('No packages available.') }}
                        </p>
                    </div>
                @endif
                <div class="text-center">
                    <a href="{{ route('all-packages') }}" class="all-pack-btn btn btn-sm rounded-pill px-5">
                        {{ __('View All Packages') }}
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================================================
             TEAM
        ========================================================== --}}
        <section id="team" class="bg-success">
            <div class="container">
                <div class="row">
                    <div class="title">
                        <h2>{{ $siteData?->team_title ?? __('CREATIVE TEAM') }}</h2>
                        @if ($siteData?->team_subtitle)
                            <p>{!! nl2br(e($siteData->team_subtitle)) !!}</p>
                        @endif
                    </div>

                    <div class="col-md-12">
                        @php
                            $teamMembers = $siteData?->team_members ?? [];
                            if (count($teamMembers) === 0) {
                                $teamMembers = [
                                    [
                                        'name' => __('TEAM MEMBER 1'),
                                        'role' => __('Staff'),
                                        'bio' => __('Dedicated team member committed to providing excellent service.'),
                                    ],
                                    [
                                        'name' => __('TEAM MEMBER 2'),
                                        'role' => __('Staff'),
                                        'bio' => __('Dedicated team member committed to providing excellent service.'),
                                    ],
                                    [
                                        'name' => __('TEAM MEMBER 3'),
                                        'role' => __('Staff'),
                                        'bio' => __('Dedicated team member committed to providing excellent service.'),
                                    ],
                                    [
                                        'name' => __('TEAM MEMBER 4'),
                                        'role' => __('Staff'),
                                        'bio' => __('Dedicated team member committed to providing excellent service.'),
                                    ],
                                ];
                            }
                        @endphp

                        <div id="teamCarousel" class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                @foreach ($teamMembers as $index => $member)
                                    <div class="swiper-slide p-1">
                                        <div class="block wow fadeInLeft w-100 h-100 d-flex flex-column"
                                            data-wow-delay=".3s">
                                            <img src="{{ isset($member['image']) && $member['image'] ? site_image($member['image']) : asset('images/team-demo.png') }}"
                                                alt="{{ $member['name'] ?? '' }}">
                                            <div class="team-overlay">
                                                <h3>{{ strtoupper($member['name'] ?? '') }}
                                                    <span>{{ $member['role'] ?? '' }}</span>
                                                </h3>
                                                <span class="icon"><i class="bi bi-chat-quote"></i></span>
                                                <p>{{ $member['bio'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{-- Swiper Pagination --}}
                            <div class="swiper-pagination"></div>
                            {{-- Swiper Navigation --}}
                            @if (count($teamMembers) > 1)
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             CUSTOMER REVIEWS / TESTIMONIALS
        ========================================================== --}}
        @if (count($reviews) > 0)
            <section id="reviews" class="pb-1 pt-5" style="background: #f8f9fc;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="title text-center mb-2" style="text-align: center; margin-bottom: 3rem;">
                                <h2 class="text-success mb-0" style="font-weight: bold;">
                                    {{ __('What Our Clients Say') }}</h2>
                                <p>
                                    {{ __('Feedback and reviews from our active internet subscribers') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-md-5 position-relative">
                        <div class="swiper reviews-swiper pt-2 pb-3">
                            <div class="swiper-wrapper">
                                @foreach ($reviews as $review)
                                    <div class="swiper-slide h-auto d-flex">
                                        <div class="card w-100 border-0 shadow-sm p-4"
                                            style="border-radius: 20px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #ffffff; height: 100%;">
                                            <div class="d-flex align-items-center mb-3"
                                                style="display: flex; align-items: center; margin-bottom: 1rem;">
                                                <div class="avatar-circle d-flex align-items-center justify-content-center fw-bold text-success me-3"
                                                    style="width: 48px; height: 48px; min-width: 48px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 1rem; font-size: 1.2rem;">
                                                    {{ strtoupper(substr($review->pppUser?->customer?->customer_name ?? ($review->pppUser?->username ?? 'C'), 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 fw-bold text-dark"
                                                        style="font-size: 0.95rem; margin: 0; font-weight: 700; color: #1a1f36;">
                                                        {{ $review->pppUser?->customer?->customer_name ?? __('Valued Customer') }}
                                                    </h5>
                                                    <span
                                                        style="font-size: 0.75rem;">{{ __('Active Member') }}</span>
                                                </div>
                                            </div>

                                            <div class="mb-2"
                                                style="color: #ffc107; display: flex; gap: 2px; margin-bottom: 0.5rem;">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="bi {{ $review->rating >= $i ? 'bi-star-fill' : 'bi-star' }}"
                                                        style="font-size: 0.85rem;"></i>
                                                @endfor
                                            </div>

                                            <p class="card-text text-secondary"
                                                style="font-size: 0.85rem; font-style: italic; color: #4f566b; line-height: 1.5; margin: 0;">
                                                "{{ $review->comment }}"
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="swiper-pagination mt-4"></div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="swiper-button-prev reviews-prev-btn"></div>
                        <div class="swiper-button-next reviews-next-btn"></div>
                    </div>
                </div>
            </section>
        @endif



        {{-- =========================================================
             BLOG
        ========================================================== --}}
        <section id="blog">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h2 class="text-success">{{ $siteData?->blog_title ?? __('Blog') }}</h2>
                            @if ($siteData?->blog_subtitle)
                                <p>{!! nl2br(e($siteData->blog_subtitle)) !!}</p>
                            @endif
                        </div>

                        @php
                            $blogPosts = $siteData?->blog_posts ?? [];
                            if (count($blogPosts) === 0) {
                                $blogPosts = [
                                    [
                                        'title' => __('Latest News & Updates 1'),
                                        'author' => __('Admin'),
                                        'excerpt' => __(
                                            'Stay updated with the latest news, offers, and updates from our network team.',
                                        ),
                                    ],
                                    [
                                        'title' => __('Latest News & Updates 2'),
                                        'author' => __('Admin'),
                                        'excerpt' => __(
                                            'Stay updated with the latest news, offers, and updates from our network team.',
                                        ),
                                    ],
                                    [
                                        'title' => __('Latest News & Updates 3'),
                                        'author' => __('Admin'),
                                        'excerpt' => __(
                                            'Stay updated with the latest news, offers, and updates from our network team.',
                                        ),
                                    ],
                                    [
                                        'title' => __('Latest News & Updates 4'),
                                        'author' => __('Admin'),
                                        'excerpt' => __(
                                            'Stay updated with the latest news, offers, and updates from our network team.',
                                        ),
                                    ],
                                ];
                            }
                            $blogChunks = array_chunk($blogPosts, 3);
                        @endphp

                        <div class="px-md-5 position-relative">
                            <div class="swiper blog-swiper">
                                <div class="swiper-wrapper">
                                    @foreach ($blogPosts as $post)
                                        <div class="swiper-slide h-auto d-flex">
                                            <div class="block w-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    @if (!empty($post['image']))
                                                        <img src="{{ site_image($post['image']) }}"
                                                            alt="{{ $post['title'] ?? '' }}" class="img-thumbnail">
                                                    @else
                                                        <img src="{{ site_image(siteUrlSettings('site_logo')) }}"
                                                            alt="{{ $post['title'] ?? '' }}" class="img-thumbnail">
                                                    @endif
                                                    <div class="content">
                                                        <h4>
                                                            <a
                                                                href="{{ $post['link'] ?? '#' }}">{{ $post['title'] ?? '' }}</a>
                                                        </h4>
                                                        <small style="color: var(--primary-color-light)">{{ __('By') }}
                                                            {{ $post['author'] ?? __('Admin') }}
                                                            @if (!empty($post['date']))
                                                                /
                                                                {{ \Carbon\Carbon::parse($post['date'])->format('M d, Y') }}
                                                            @endif
                                                        </small>
                                                        <p style="color: var(--dark-color-light) !important;">{{ $post['excerpt'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="content pt-0">
                                                    <a href="{{ $post['link'] ?? '#' }}"
                                                        class="btn btn-read text-success">{{ __('Read More') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                <div class="swiper-pagination mt-4"></div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="swiper-button-prev blog-prev-btn"></div>
                            <div class="swiper-button-next blog-next-btn"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- =========================================================
             TESTIMONIALS
        ========================================================== --}}
        <section id="testimonial">
            <div class="container">
                <div class="row">
                    <div class="title">
                        <h2 class="text-white">{{ $siteData?->testimonial_title ?? __('Testimonial') }}</h2>
                        @if ($siteData?->testimonial_subtitle)
                            <p>{!! nl2br(e($siteData->testimonial_subtitle)) !!}</p>
                        @endif
                    </div>

                    @php $testimonials = $siteData?->testimonials ?? []; @endphp
                    <div class="px-md-5 position-relative">
                        <div class="swiper testimonial-swiper">
                            <div class="swiper-wrapper">
                                @if (count($testimonials) > 0)
                                    @foreach ($testimonials as $testimonial)
                                        <div class="swiper-slide h-auto d-flex">
                                            <div class="media w-100 wow fadeInLeft" data-wow-delay=".3s">
                                                <div class="media-left">
                                                    <a href="#">
                                                        <img src="{{ !empty($testimonial['image']) ? site_image($testimonial['image']) : asset('images/team-demo.png') }}"
                                                            alt="{{ $testimonial['name'] ?? '' }}">
                                                    </a>
                                                </div>
                                                <div class="media-body">
                                                    <a href="#">
                                                        <h4 class="media-heading">{{ $testimonial['name'] ?? '' }}
                                                        </h4>
                                                    </a>
                                                    <p>{{ $testimonial['message'] ?? '' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Fallback testimonials --}}
                                    @foreach (['Satisfied Customer', 'Happy Client', 'Regular User', 'Business Owner'] as $name)
                                        <div class="swiper-slide h-auto d-flex">
                                            <div class="media w-100 wow fadeInLeft" data-wow-delay=".3s">
                                                <div class="media-left">
                                                    <a href="#"><img src="{{ asset('images/team-demo.png') }}"
                                                            alt=""></a>
                                                </div>
                                                <div class="media-body">
                                                    <a href="#">
                                                        <h4 class="media-heading">{{ __($name) }}</h4>
                                                    </a>
                                                    <p>{{ __('Excellent internet service with fast speeds and reliable uptime. Customer support is always responsive and helpful. Highly recommended!') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Pagination -->
                            <div class="swiper-pagination mt-4"></div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="swiper-button-prev testimonial-prev-btn"></div>
                        <div class="swiper-button-next testimonial-next-btn"></div>
                    </div>
                </div>
            </div>
        </section>


        {{-- =========================================================
             CONTACT
        ========================================================== --}}
        <section id="contact-form">
            <div class="container">
                <div class="row">
                    <div class="title">
                        <h2 class="text-success">{{ $siteData?->contact_title ?? __('CONTACT US') }}</h2>
                        @if ($siteData?->contact_subtitle)
                            <p>{!! nl2br(e($siteData->contact_subtitle)) !!}</p>
                        @endif
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="map">
                            <div id="googleMap">
                                @if ($siteData?->google_map_embed)
                                    <iframe src="{{ $siteData->google_map_embed }}" width="600" height="450"
                                        style="border:0;" allowfullscreen="" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                @elseif(siteUrlSettings('site_map'))
                                    <iframe src="{{ siteUrlSettings('site_map') }}" width="600" height="450"
                                        style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                @else
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d97559.35009863286!2d90.89949961876307!3d24.672873925245927!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3756e83b9c19e2e5%3A0xa7695289d8c1a5c1!2sMadan%20Upazila!5e0!3m2!1sen!2sbd!4v1770660584969!5m2!1sen!2sbd"
                                        width="600" height="450" style="border:0;" allowfullscreen=""
                                        loading="lazy"></iframe>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12 mt-4 mt-md-0">
                        <livewire:CommentSubmit />
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
                            <a href="#top">
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
        </footer>

    </div>{{-- end scrollspy wrapper --}}

    {{-- Back to Top --}}
    <button type="button" class="btn btn-floating" id="btn-back-to-top">
        <i class="bi bi-arrow-up-circle"></i>
    </button>

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

            // Initial setup
            const isLight = document.documentElement.classList.contains('theme-light');
            updateToggleIcon(isLight);

            themeToggle.addEventListener('click', function() {
                const currentlyLight = document.documentElement.classList.toggle('theme-light');
                localStorage.setItem('site-theme', currentlyLight ? 'light' : 'dark');
                updateToggleIcon(currentlyLight);
            });
        });
    </script>
    <livewire:package-purchase-form />
</body>

</html>
