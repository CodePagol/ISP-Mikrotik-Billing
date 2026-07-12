@php
    $logo = siteUrlSettings('site_logo');
    $icon = siteUrlSettings('site_icon');
    $name = siteUrlSettings('site_name') ?? 'Code Pagol';
@endphp

<div class="flex items-center">
    @if ($logo && file_exists(public_path($logo)))
        <img src="{{ asset($logo) }}" style="width: 190px; height: 53px;" alt="Logo">
    @elseif ($icon)
        <img src="{{ asset($icon) }}" style="width: 190px; height: 53px;" alt="Icon">
        <span class="ml-2 font-bold text-xl">{{ $name }}</span>
    @else
        <span class="font-bold text-xl text-primary-600">{{ $name }}</span>
    @endif
</div>