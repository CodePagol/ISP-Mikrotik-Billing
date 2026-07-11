<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Navigation\NavigationItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');

        return $panel
            ->default()
            ->spa(hasPrefetching: true)
            ->id('portal')
            ->path('')
            ->domain('portal.'.$baseDomain)
            ->favicon(site_image(siteUrlSettings('site_favicon'), 'images/favicon.png'))
            ->brandLogo(site_image(siteUrlSettings('site_logo'), 'images/favicon.png'))
            ->brandName(siteUrlSettings('site_name') ?? 'Code Pagol')
            ->brandLogoHeight('3.5rem')
            ->login(Login::class)
            ->registration((siteUrlSettings('portal_registration_enabled') ?? 1) ? Register::class : null)
            ->authGuard('ppp')
            ->profile(EditProfile::class)
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => match(siteUrlSettings('portal_theme_preset') ?? 'indigo') {
                    'emerald' => Color::Emerald,
                    'blue'    => Color::Blue,
                    'orange'  => Color::Orange,
                    'dark'    => Color::Slate,
                    'indigo'  => Color::Indigo,
                    'custom'  => Color::hex(siteUrlSettings('portal_primary_color') ?? '#6366f1'),
                    default   => Color::hex('#06ad73'),
                },
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                NavigationItem::make('Recharge via Voucher')
                    ->url('/recharge/voucher')
                    ->icon('heroicon-o-ticket')
                    ->sort(5),
            ])
            ->assets([
                Css::make('portal-custom-styles', Vite::asset('resources/css/app.css')),
                Js::make('portal-custom-js', Vite::asset('resources/js/app.js'))->module(),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => view('components.portal-dynamic-theme')->render(),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => view('components.theme-customizer')->render(),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
