<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\CandidateApplication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Utilisateurs', User::count())
                ->description('Tous les utilisateurs')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Candidats Actifs', User::candidates()->active()->count())
                ->description('Candidats non bloqués')
                ->descriptionIcon('heroicon-m-user-check')
                ->color('success'),

            Stat::make('Utilisateurs Bloqués', User::blocked()->count())
                ->description('Comptes suspendus')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('danger'),

            Stat::make('Total Candidatures', CandidateApplication::count())
                ->description('Toutes les candidatures')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('En Attente', CandidateApplication::where('status', 'pending')->count())
                ->description('Candidatures à traiter')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Analysées', CandidateApplication::where('status', 'analyzed')->count())
                ->description('Avec analyse IA')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),
        ];
    }
}
