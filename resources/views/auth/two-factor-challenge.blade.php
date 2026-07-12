<x-guest-layout>
    <x-authentication-card class="box__sm">
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="box__right" x-data="{ recovery: false }">
            <form class="form" method="POST" action="{{ route('two-factor.login') }}">
                @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                    <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                        <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                    </div>
                @endif
                <h2 class="form__title">{{ __('Two-Factor Authentication') }}</h2>
                @csrf
                <div class="mb-4 form__text" x-show="! recovery">
                    {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                </div>
    
                <div class="mb-4 form__text" x-cloak x-show="recovery">
                    {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                </div>

                <div class="input-group" x-show="! recovery">
                    <x-input class="form-control" id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" placeholder="Code" />
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-bootstrap-reboot" width="16" height="16" viewBox="0 0 122.88 119.36"><path d="M102.58,84.44a5.07,5.07,0,0,1,8.77,5.08,59.65,59.65,0,0,1-81.15,22,5.83,5.83,0,0,1-.69-.39,59.66,59.66,0,0,1-21.7-81,5.14,5.14,0,0,1,.46-.78A59.63,59.63,0,0,1,89.5,8a59.22,59.22,0,0,1,21.7,21.55l1-3.89a5.42,5.42,0,1,1,10.49,2.71L119,42.69a5.52,5.52,0,0,1-.48,1.23,5.43,5.43,0,0,1-6,3.28L98,44.52a5.42,5.42,0,0,1,2-10.66l2.33.43a49.56,49.56,0,0,0-85.31.37l-.14.26A49.55,49.55,0,0,0,34.9,102.57h0a49.54,49.54,0,0,0,67.66-18.14Zm-22-14.06h0l5.75,5.75h0l3.52,3.52L84.15,85.4l-3.52-3.52-5.57,5.57L69.31,81.7l5.57-5.57-3-3-6.41,6.42-5.75-5.75,6.42-6.42-2-2-2-2,0,0a16.95,16.95,0,0,1-23.92,0h0l-.28-.3a16.92,16.92,0,0,1,.28-23.63h0L44,33.64a16.93,16.93,0,0,1,24,23.93h0l0,0L80.63,70.38ZM61.31,40.23a7.67,7.67,0,0,0-10.77,0L44.73,46h0a7.68,7.68,0,0,0-.19,10.58l.2.19h0a7.68,7.68,0,0,0,10.77,0L61.31,51h0a7.68,7.68,0,0,0,0-10.77Z"/>
                        </svg>
                    </span>
                </div>

                <div class="input-group" x-cloak x-show="recovery">
                    <x-input class="form-control" id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" placeholder="Recovery Code" />
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-bootstrap-reboot" width="16" height="16" viewBox="0 0 122.88 119.36"><path d="M102.58,84.44a5.07,5.07,0,0,1,8.77,5.08,59.65,59.65,0,0,1-81.15,22,5.83,5.83,0,0,1-.69-.39,59.66,59.66,0,0,1-21.7-81,5.14,5.14,0,0,1,.46-.78A59.63,59.63,0,0,1,89.5,8a59.22,59.22,0,0,1,21.7,21.55l1-3.89a5.42,5.42,0,1,1,10.49,2.71L119,42.69a5.52,5.52,0,0,1-.48,1.23,5.43,5.43,0,0,1-6,3.28L98,44.52a5.42,5.42,0,0,1,2-10.66l2.33.43a49.56,49.56,0,0,0-85.31.37l-.14.26A49.55,49.55,0,0,0,34.9,102.57h0a49.54,49.54,0,0,0,67.66-18.14Zm-22-14.06h0l5.75,5.75h0l3.52,3.52L84.15,85.4l-3.52-3.52-5.57,5.57L69.31,81.7l5.57-5.57-3-3-6.41,6.42-5.75-5.75,6.42-6.42-2-2-2-2,0,0a16.95,16.95,0,0,1-23.92,0h0l-.28-.3a16.92,16.92,0,0,1,.28-23.63h0L44,33.64a16.93,16.93,0,0,1,24,23.93h0l0,0L80.63,70.38ZM61.31,40.23a7.67,7.67,0,0,0-10.77,0L44.73,46h0a7.68,7.68,0,0,0-.19,10.58l.2.19h0a7.68,7.68,0,0,0,10.77,0L61.31,51h0a7.68,7.68,0,0,0,0-10.77Z"/>
                        </svg>
                    </span>
                </div>

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ $value }}
                    </div>
                @endsession

                <x-validation-errors class="mb-4" />
                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="form__button" style="color: #000000"
                                    x-show="! recovery"
                                    x-on:click="
                                        recovery = true;
                                        $nextTick(() => { $refs.recovery_code.focus() })
                                    ">
                        {{ __('Use a recovery code') }}
                    </button>

                    <button type="button" class="form__button"
                                    x-cloak
                                    x-show="recovery"
                                    x-on:click="
                                        recovery = false;
                                        $nextTick(() => { $refs.code.focus() })
                                    ">
                        {{ __('Use an authentication code') }}
                    </button>

                    <x-button-success class="form__button">
                        {{ __('Log in') }}
                    </x-button-success>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
