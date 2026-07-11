@if (file_exists(public_path('images/front_logo_auto_200.png')))
    <img src="{{ asset('images/front_logo_auto_200.png') }}" alt="Picture" class="auth-logo">
@else
    <h2 class="box__title neon-text audiowide-bold">{{ env('APP_NAME') }}</h2>
@endif