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