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
            <div class="box__left">
                <form class="form" method="POST" action="{{ route('register') }}">
                    @csrf
                    @if (!siteUrlSettings('site_logo') && !siteUrlSettings('site_icon'))
                        <div class="d-desktop-only" style="text-align: center; margin-bottom: 20px;">
                            <h2 class="neon-text audiowide-bold" style="font-size: 2rem; color: #06ad73; text-shadow: 0 0 5px #06ad73;">{{ siteUrlSettings('site_name') ?? 'Code Pagol' }}</h2>
                        </div>
                    @endif
                    <h2 class="form__title">Sign Up</h2>
                    <x-validation-errors class="mb-4" />
                    <div class="input-group">
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            autofocus autocomplete="name" placeholder="Name">
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
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="username" placeholder="E-mail">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                <path
                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z" />
                            </svg></span>
                    </div>
                    <div class="input-group">
                        <input id="password" type="password" name="password" autocomplete="new-password"
                            placeholder="Password" required>
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                <path
                                    d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5" />
                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                            </svg></span>
                    </div>
                    <div class="input-group">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            autocomplete="new-password" placeholder="Confirm Password" required>
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16">
                                <path
                                    d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                            </svg></span>
                    </div>
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <label class="checkbox-group" for="terms">
                            <input type="checkbox" name="terms" id="terms" required>
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' =>
                                    '<a target="_blank" href="' . route('terms.show') . '" class="">' . __('Terms of Service') . '</a>',
                                'privacy_policy' =>
                                    '<a target="_blank" href="' . route('policy.show') . '" class="">' . __('Privacy Policy') . '</a>',
                            ]) !!}
                        </label>
                    @endif

                    <button type="submit" class="form__button">{{ __('Create an Account') }}</button>

                    <p class="form__text">{{ __('Already have an account?') }} <a wire:navigate.hover
                            wire:current="active" class="form__link" href="{{ route('login') }}">Sign in!</a></p>
                </form>
            </div>

            {{-- login form not --}}
            <div class="box__right box__image-container">
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
        </div>
    </div>
</x-guest-layout>
