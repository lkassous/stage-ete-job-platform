<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer le nom complet
        if (isset($data['first_name']) && isset($data['last_name'])) {
            $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Utilisateur créé avec succès';
    }
}
