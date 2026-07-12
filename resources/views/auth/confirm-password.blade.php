<x-guest-layout>
    <x-authentication-card class="box__sm">
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="box__right">
            <form class="form" method="POST" action="{{ route('password.confirm') }}">
                @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                    <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                        <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                    </div>
                @endif
                <h2 class="form__title">{{ __('Confirm Password') }}</h2>
                @csrf

                <div class="mb-4 form__text">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>

                <div class="input-group">
                    <x-input class="form-control" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus placeholder="Password" />
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-bounding-box" viewBox="0 0 16 16">
                            <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5M.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5"/>
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                        </svg>
                    </span>
                </div>

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ $value }}
                    </div>
                @endsession

                <x-validation-errors class="mb-4" />

                <button type="submit" class="form__button">{{ __('Confirm') }}</button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
