<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- ===============================================-->
        <!--    Document Title-->
        <!-- ===============================================-->
        <title>{{ siteUrlSettings('site_title') ?? config('app.name') }}</title>

        <link rel="shortcut icon" href="{{ site_image(siteUrlSettings('site_favicon'), 'images/favicon.png') }}" type="image/x-icon">

        @vite(['resources/sass/guest.scss', 'resources/js/app.js'])
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
        @livewireStyles
    </head>
    <body>
        <div class="font-sans text-gray-900 dark:text-gray-100 antialiased">
            {{ $slot }}
        </div>
        <section>
            <div class='air air1'></div>
            <div class='air air2'></div>
            <div class='air air3'></div>
            <div class='air air4'></div>
        </section>
        @livewireScripts
    </body>
</html>
