<?php

namespace App\Filament\Resources\CandidateApplicationResource\Pages;

use App\Filament\Resources\CandidateApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidateApplication extends EditRecord
{
    protected static string $resource = CandidateApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si le statut passe à "analyzed", mettre à jour analyzed_at
        if ($data['status'] === 'analyzed' && $this->record->status !== 'analyzed') {
            $data['analyzed_at'] = now();
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Candidature mise à jour avec succès';
    }
}
