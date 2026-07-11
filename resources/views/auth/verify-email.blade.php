<x-guest-layout>
    <x-authentication-card class="box__sm">
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        
        <div class="box__right">
            <form class="form" method="POST" action="{{ route('verification.send') }}">
                @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                    <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                        <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                    </div>
                @endif
                <h2 class="form__title">{{ __('Email Verification') }}</h2>
                @csrf

                <div class="mb-4 form__text">
                    {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
                    </div>
                @endsession

                <x-validation-errors class="mb-4" />

                <button type="submit" class="form__button">{{ __('Resend Verification Email') }}</button>
            </form>

            <div class="form__text p-3">
                Go For Edit <a href="{{ route('profile.show') }}" class="form__link">{{ __('Profile') }}</a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf

                    <button type="submit" class="form__button text-red-600">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>


    </x-authentication-card>
</x-guest-layout>
