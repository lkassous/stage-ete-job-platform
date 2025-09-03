<?php

namespace App\Filament\Resources\CandidateApplicationResource\Pages;

use App\Filament\Resources\CandidateApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidateApplication extends CreateRecord
{
    protected static string $resource = CandidateApplicationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_at'] = now();
        $data['status'] = 'pending';

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Candidature créée avec succès';
    }
}
