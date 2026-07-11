<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand-lg" style="display: none;">
    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation">
        <span class="navbar-toggle-icon">
            <span class="toggle-line"></span>
        </span>
    </button>
    <a class="navbar-brand me-1 me-sm-3" href="{{ url('/') }}">
        <div class="d-flex align-items-center">
            @if (siteUrlSettings('site_logo'))
                <img class="me-2" style="width: 190px; height: 53px;" src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo"/>
            @else
                @if (siteUrlSettings('site_icon'))
                    <img class="me-2" src="{{ site_image(siteUrlSettings('site_icon')) }}" alt="" width="40" />
                    <span class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</span>
                @else
                    <span class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</span>
                @endif
            @endif
        </div>
    </a>
    <div class="collapse navbar-collapse scrollbar" id="navbarStandard">
        @include('layouts.partials.searchbar')
    </div>
    @include('layouts.partials.userpanel')
</nav>

<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand-lg" style="display: none;" data-move-target="#navbarVerticalNav" data-navbar-top="combo">
    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation">
        <span class="navbar-toggle-icon">
            <span class="toggle-line"></span>
        </span>
    </button>
    <a class="navbar-brand me-1 me-sm-3" href="{{ url('/') }}">
        <div class="d-flex align-items-center">
            @if (siteUrlSettings('site_logo'))
                <img class="me-2" style="width: 190px; height: 53px;" src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo"/>
            @else
                @if (siteUrlSettings('site_icon'))
                    <img class="me-2" src="{{ site_image(siteUrlSettings('site_icon')) }}" alt="" width="40" />
                    <span class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</span>
                @else
                    <span class="font-sans-serif text-success">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</span>
                @endif
            @endif
        </div>
    </a>
    @include('layouts.partials.navpanel')
    @include('layouts.partials.userpanel')
</nav>
