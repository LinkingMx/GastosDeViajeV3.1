<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->font('Raleway')
            ->colors([
                'primary' => [
                    '50' => '#f8f5f1',
                    '100' => '#ece6db',
                    '200' => '#d9cebf',
                    '300' => '#c5b6a3',
                    '400' => '#b29e87',
                    '500' => '#a28a70', // Un tono ligeramente más saturado que el base
                    '600' => '#857151', // Tu color base
                    '700' => '#6e5d48',
                    '800' => '#57493a',
                    '900' => '#40352b',
                    '950' => '#29221c',
                ],
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class, // Dashboard eliminado
            ])
            ->spa() // Activar SPA para mejor experiencia
            ->homeUrl('/admin/travel-requests') // Redirección directa a solicitudes de viajes
            ->sidebarCollapsibleOnDesktop() // Sidebar colapsable para mejor UX
            ->breadcrumbs(false) // Simplificar navegación
            ->databaseNotifications() // Habilitar notificaciones en base de datos
            ->renderHook(
                'panels::head.end',
                fn (): string => '<meta name="csrf-token" content="' . csrf_token() . '">'
            )
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets del dashboard eliminados para mejorar rendimiento
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
