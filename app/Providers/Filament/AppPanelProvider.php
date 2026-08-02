<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile as AuthEditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Pages\Auth\Register as AuthRegister;
use App\Filament\Pages\Auth\Login as AuthLogin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Illuminate\Support\Facades\Vite;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\App\Pages\Bookmark;
use Filament\Actions\Action;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->login(AuthLogin::class)
            ->registration(AuthRegister::class)
            ->path('')
            ->homeUrl('/home')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->userMenuItems([
                Action::make('bookmarks')
                    ->url(fn (): string => Bookmark::getUrl())
                    ->icon('heroicon-s-bookmark'),
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->topNavigation()
            ->brandName('DevHub')
            ->profile(AuthEditProfile::class)
            ->passwordReset()
            ->emailVerification()
            ->font(family: 'source sans 2')
            ->sidebarWidth('280px')
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->assets([
                Css::make('app', Vite::asset('resources/css/app.css')),
                Js::make('app', Vite::asset('resources/js/app.js')),
            ]);
    }
}
