<?php

namespace App\Filament\Resources\CandidateApplicationResource\Pages;

use App\Filament\Resources\CandidateApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCandidateApplications extends ListRecords
{
    protected static string $resource = CandidateApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle candidature'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toutes')
                ->badge(fn () => $this->getModel()::count()),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => $this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'processing' => Tab::make('En traitement')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing'))
                ->badge(fn () => $this->getModel()::where('status', 'processing')->count())
                ->badgeColor('info'),
            'analyzed' => Tab::make('Analysées')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'analyzed'))
                ->badge(fn () => $this->getModel()::where('status', 'analyzed')->count())
                ->badgeColor('success'),
            'rejected' => Tab::make('Rejetées')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge(fn () => $this->getModel()::where('status', 'rejected')->count())
                ->badgeColor('danger'),
        ];
    }
}
