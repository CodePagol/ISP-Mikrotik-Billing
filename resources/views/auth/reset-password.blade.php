<x-guest-layout>
    <x-authentication-card class="box__sm">
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="box__right">
            <form class="form" method="POST" action="{{ route('password.update') }}">
                @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                    <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                        <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                    </div>
                @endif
                <h2 class="form__title">{{ __('Update Password') }}</h2>
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                
                    <div class="input-group">
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"  required autofocus autocomplete="username" placeholder="E-mail">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                        </svg></span>
                    </div>

                    <div class="input-group">
                        <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Password" required>
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                            <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        </svg></span>
                    </div>
                    <div class="input-group">
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Confirm Password" required>
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                        </svg></span>
                    </div>

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ $value }}
                    </div>
                @endsession

                <x-validation-errors class="mb-4" />

                <button type="submit" class="form__button">{{ __('Reset Password') }}</button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
