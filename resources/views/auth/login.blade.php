<x-guest-layout>
    <div class="container">
        <div class="box box__sm">
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

            <div class="box__right">
                <form class="form" method="POST" action="{{ route('login') }}">
                    @csrf
                    @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                        <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                            <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                        </div>
                    @endif
                    <h2 class="form__title">Sign in</h2>
                    <x-validation-errors class="mb-4" />

                    <div class="input-group">
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            class="form-control" required autocomplete="username" placeholder="E-mail / User Name">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-person-bounding-box" viewBox="0 0 16 16">
                                <path
                                    d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5M.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5" />
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            </svg>
                        </span>
                    </div>
                    <div class="input-group">
                        <input t id="password" type="password" name="password" placeholder="Password" required
                            autocomplete="current-password">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-key" viewBox="0 0 16 16">
                                <path
                                    d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5" />
                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                            </svg>
                        </span>
                    </div>

                    <label class="checkbox-group">
                        <input type="checkbox" id="remember_me" name="remember">
                        {{ __('Remember me') }}
                    </label>
                    @if (Route::has('password.request'))
                        <p class="form__text">
                            <a wire:navigate.hover wire:current="active" class="form__link"
                                href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                        </p>
                    @endif
                    <button type="submit" class="form__button">Sign In</button>
                    @if (Route::has('register'))
                        <p class="form__text">
                            Don't have an account?
                            <label for="toggle" class="form__text">
                                <a wire:navigate.hover wire:current="active" class="form__link"
                                    href="{{ route('register') }}">Sign up!</a>
                            </label>
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
