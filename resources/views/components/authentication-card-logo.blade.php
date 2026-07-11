<div class="box__mobile box__image-container">
    {{-- Mobile --}}
    <div class="mobile-view">
        @if (siteUrlSettings('site_logo'))
            <img src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo" class="box__image"/>
        @else
            <h2 class="box__title neon-text audiowide-bold">
                @if (siteUrlSettings('site_icon'))
                    <img class="me-2" src="{{ site_image(siteUrlSettings('site_icon')) }}" alt="" width="40" style="vertical-align: middle;" />
                @endif
                {{ siteUrlSettings('site_name') ?? 'Code Pagol' }}
            </h2>
        @endif
    </div>
</div>

<div class="box__left box__image-container">
    {{-- Desktop --}}
    <div class="desktop-view">
        @if (siteUrlSettings('site_logo'))
            <img src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="logo" class="box__image"/>
        @else
            @if (siteUrlSettings('site_icon'))
                <h2 class="box__title neon-text audiowide-bold">
                    <img class="me-2" src="{{ site_image(siteUrlSettings('site_icon')) }}" alt="" width="40" style="vertical-align: middle;" />
                    {{ siteUrlSettings('site_name') ?? 'Code Pagol' }}
                </h2>
            @else
                <img src="{{ asset('images/front_logo_300_500.png') }}" alt="Desktop Picture" class="box__image">
            @endif
        @endif
    </div>
</div>
