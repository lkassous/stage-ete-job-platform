<?php

namespace App\Filament\Resources\CandidateApplicationResource\Pages;

use App\Filament\Resources\CandidateApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Illuminate\Support\Facades\Storage;

class ViewCandidateApplication extends ViewRecord
{
    protected static string $resource = CandidateApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('change_status')
                ->label('Changer le statut')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Nouveau statut')
                        ->options([
                            'pending' => 'En attente',
                            'processing' => 'En cours de traitement',
                            'analyzed' => 'Analysé',
                            'rejected' => 'Rejeté',
                        ])
                        ->required()
                        ->default($this->record->status),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notes administrateur')
                        ->rows(3)
                        ->default($this->record->admin_notes),
                ])
                ->action(function (array $data): void {
                    $updateData = [
                        'status' => $data['status'],
                        'admin_notes' => $data['admin_notes'],
                    ];

                    if ($data['status'] === 'analyzed' && $this->record->status !== 'analyzed') {
                        $updateData['analyzed_at'] = now();
                    }

                    $this->record->update($updateData);
                    $this->refreshFormData(['status', 'admin_notes', 'analyzed_at']);
                })
                ->successNotificationTitle('Statut mis à jour avec succès'),
            Actions\Action::make('download_cv')
                ->label('Télécharger CV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => $this->record->cv_file_path !== null)
                ->url(fn (): string => Storage::disk('public')->url($this->record->cv_file_path))
                ->openUrlInNewTab(),
            Actions\Action::make('download_cover_letter')
                ->label('Télécharger Lettre')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => $this->record->cover_letter_path !== null)
                ->url(fn (): string => Storage::disk('public')->url($this->record->cover_letter_path))
                ->openUrlInNewTab(),
            Actions\Action::make('add_ai_analysis')
                ->label('Ajouter analyse IA')
                ->icon('heroicon-o-cpu-chip')
                ->color('warning')
                ->visible(fn (): bool => !$this->record->ai_analysis)
                ->form([
                    Forms\Components\Textarea::make('profile_summary')
                        ->label('Résumé du profil')
                        ->required()
                        ->rows(3),
                    Forms\Components\TagsInput::make('key_skills')
                        ->label('Compétences clés')
                        ->required(),
                    Forms\Components\Textarea::make('education')
                        ->label('Formation')
                        ->required()
                        ->rows(2),
                    Forms\Components\Textarea::make('experience')
                        ->label('Expérience')
                        ->required()
                        ->rows(3),
                    Forms\Components\TextInput::make('suitability_score')
                        ->label('Score d\'adéquation (/100)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                    Forms\Components\Textarea::make('recommendations')
                        ->label('Recommandations')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'ai_analysis' => $data,
                        'status' => 'analyzed',
                        'analyzed_at' => now(),
                    ]);
                    $this->refreshFormData(['ai_analysis', 'status', 'analyzed_at']);
                })
                ->successNotificationTitle('Analyse IA ajoutée avec succès'),
        ];
    }
}
