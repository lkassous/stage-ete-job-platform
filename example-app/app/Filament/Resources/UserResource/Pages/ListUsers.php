<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvel utilisateur'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->badge(fn () => $this->getModel()::count()),
            'candidates' => Tab::make('Candidats')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_type', 'candidate'))
                ->badge(fn () => $this->getModel()::where('user_type', 'candidate')->count()),
            'admins' => Tab::make('Administrateurs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_type', 'admin'))
                ->badge(fn () => $this->getModel()::where('user_type', 'admin')->count()),
            'active' => Tab::make('Actifs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)->whereNull('blocked_at'))
                ->badge(fn () => $this->getModel()::where('is_active', true)->whereNull('blocked_at')->count()),
            'blocked' => Tab::make('Bloqués')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)->orWhereNotNull('blocked_at'))
                ->badge(fn () => $this->getModel()::where('is_active', false)->orWhereNotNull('blocked_at')->count())
                ->badgeColor('danger'),
        ];
    }
}
