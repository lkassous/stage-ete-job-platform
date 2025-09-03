<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('block')
                ->label('Bloquer l\'utilisateur')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn (): bool => $this->record->isActive() && $this->record->user_type !== 'admin')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Raison du blocage')
                        ->required()
                        ->rows(3)
                        ->placeholder('Expliquez pourquoi vous bloquez cet utilisateur...'),
                ])
                ->action(function (array $data): void {
                    $this->record->block($data['reason'], auth()->user());
                    $this->refreshFormData(['blocked_at', 'blocked_reason', 'blocked_by', 'is_active']);
                })
                ->successNotificationTitle('Utilisateur bloqué avec succès'),
            Actions\Action::make('unblock')
                ->label('Débloquer l\'utilisateur')
                ->icon('heroicon-o-lock-open')
                ->color('success')
                ->visible(fn (): bool => !$this->record->isActive())
                ->requiresConfirmation()
                ->modalHeading('Débloquer l\'utilisateur')
                ->modalDescription('Êtes-vous sûr de vouloir débloquer cet utilisateur ? Il pourra à nouveau accéder à son compte.')
                ->modalSubmitActionLabel('Oui, débloquer')
                ->action(function (): void {
                    $this->record->unblock();
                    $this->refreshFormData(['blocked_at', 'blocked_reason', 'blocked_by', 'is_active']);
                })
                ->successNotificationTitle('Utilisateur débloqué avec succès'),
        ];
    }
}
