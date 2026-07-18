@php
    $logo = siteUrlSettings('site_logo');
    $icon = siteUrlSettings('site_icon');
    $name = siteUrlSettings('site_name') ?? 'Code Pagol';
@endphp

<div class="flex items-center">
    @if ($logo)
        <img src="{{ site_image($logo) }}" style="width: 190px; height: 53px;" alt="Logo">
    @elseif ($icon)
        <img src="{{ site_image($icon) }}" style="width: 190px; height: 53px;" alt="Icon">
        <span class="ml-2 font-bold text-xl">{{ $name }}</span>
    @else
        <span class="font-bold text-xl text-primary-600">{{ $name }}</span>
    @endif
</div>