<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuration Filament
        if (class_exists(\Filament\Facades\Filament::class)) {
            Filament::serving(function () {
                Filament::registerNavigationGroups([
                    NavigationGroup::make('Gestion des utilisateurs')
                        ->icon('heroicon-o-users'),
                    NavigationGroup::make('Candidatures')
                        ->icon('heroicon-o-document-text'),
                    NavigationGroup::make('Statistiques')
                        ->icon('heroicon-o-chart-bar'),
                    NavigationGroup::make('Configuration')
                        ->icon('heroicon-o-cog-6-tooth'),
                ]);
            });
        }
    }
}
