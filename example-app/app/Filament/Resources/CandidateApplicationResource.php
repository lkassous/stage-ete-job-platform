<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateApplicationResource\Pages;
use App\Models\CandidateApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;

class CandidateApplicationResource extends Resource
{
    protected static ?string $model = CandidateApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Candidatures';

    protected static ?string $navigationLabel = 'Candidatures';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Candidatures';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du candidat')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Candidat')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(1),

                Section::make('Fichiers')
                    ->schema([
                        Forms\Components\FileUpload::make('cv_file_path')
                            ->label('CV (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->directory('cv_files')
                            ->required(),
                        Forms\Components\FileUpload::make('cover_letter_path')
                            ->label('Lettre de motivation (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->directory('cover_letters'),
                    ])->columns(2),

                Section::make('Statut et analyse')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'processing' => 'En cours de traitement',
                                'analyzed' => 'Analysé',
                                'rejected' => 'Rejeté',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes administrateur')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Analyse IA')
                    ->schema([
                        Forms\Components\KeyValue::make('ai_analysis')
                            ->label('Résultats de l\'analyse IA')
                            ->keyLabel('Critère')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter un critère')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && $record->ai_analysis),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Candidat')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'analyzed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'processing' => 'En traitement',
                        'analyzed' => 'Analysé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('cv_file_path')
                    ->label('CV')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('cover_letter_path')
                    ->label('Lettre')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('analyzed_at')
                    ->label('Analysé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Non analysé'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'processing' => 'En traitement',
                        'analyzed' => 'Analysé',
                        'rejected' => 'Rejeté',
                    ]),
                Tables\Filters\Filter::make('has_ai_analysis')
                    ->label('Avec analyse IA')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('ai_analysis')),
                Tables\Filters\Filter::make('submitted_today')
                    ->label('Soumis aujourd\'hui')
                    ->query(fn (Builder $query): Builder => $query->whereDate('submitted_at', today())),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_cv')
                    ->label('Télécharger CV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (CandidateApplication $record): bool => $record->cv_file_path !== null)
                    ->url(fn (CandidateApplication $record): string => Storage::disk('public')->url($record->cv_file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('download_cover_letter')
                    ->label('Télécharger Lettre')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (CandidateApplication $record): bool => $record->cover_letter_path !== null)
                    ->url(fn (CandidateApplication $record): string => Storage::disk('public')->url($record->cover_letter_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du candidat')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Nom complet'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('Téléphone')
                            ->placeholder('Non renseigné'),
                        Infolists\Components\TextEntry::make('user.linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('Non renseigné'),
                    ])->columns(2),

                Infolists\Components\Section::make('Candidature')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'analyzed' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'En attente',
                                'processing' => 'En traitement',
                                'analyzed' => 'Analysé',
                                'rejected' => 'Rejeté',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label('Soumis le')
                            ->dateTime('d/m/Y à H:i'),
                        Infolists\Components\TextEntry::make('analyzed_at')
                            ->label('Analysé le')
                            ->dateTime('d/m/Y à H:i')
                            ->placeholder('Non analysé'),
                    ])->columns(3),

                Infolists\Components\Section::make('Fichiers')
                    ->schema([
                        Infolists\Components\TextEntry::make('cv_file_path')
                            ->label('CV')
                            ->formatStateUsing(fn ($state) => $state ? 'CV disponible' : 'Aucun CV')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('cover_letter_path')
                            ->label('Lettre de motivation')
                            ->formatStateUsing(fn ($state) => $state ? 'Lettre disponible' : 'Aucune lettre')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                    ])->columns(2),

                Infolists\Components\Section::make('Notes administrateur')
                    ->schema([
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('')
                            ->placeholder('Aucune note')
                            ->prose(),
                    ])
                    ->visible(fn ($record) => $record->admin_notes),

                Infolists\Components\Section::make('Analyse IA')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('ai_analysis')
                            ->label('')
                            ->keyLabel('Critère')
                            ->valueLabel('Résultat'),
                    ])
                    ->visible(fn ($record) => $record->ai_analysis),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidateApplications::route('/'),
            'create' => Pages\CreateCandidateApplication::route('/create'),
            'view' => Pages\ViewCandidateApplication::route('/{record}'),
            'edit' => Pages\EditCandidateApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
