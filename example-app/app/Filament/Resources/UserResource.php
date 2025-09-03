<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestion des utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Compte et sécurité')
                    ->schema([
                        Forms\Components\Select::make('user_type')
                            ->label('Type d\'utilisateur')
                            ->options([
                                'admin' => 'Administrateur',
                                'candidate' => 'Candidat',
                            ])
                            ->required()
                            ->default('candidate'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Section::make('Informations de blocage')
                    ->schema([
                        Forms\Components\DateTimePicker::make('blocked_at')
                            ->label('Bloqué le')
                            ->disabled(),
                        Forms\Components\Textarea::make('blocked_reason')
                            ->label('Raison du blocage')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record && $record->blocked_at)
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom complet')
                    ->searchable(['first_name', 'last_name', 'name'])
                    ->sortable()
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('user_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'admin',
                        'primary' => 'candidate',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'candidate' => 'Candidat',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('candidateApplications_count')
                    ->label('Candidatures')
                    ->counts('candidateApplications')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('blocked_at')
                    ->label('Bloqué le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Non bloqué'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->label('Type d\'utilisateur')
                    ->options([
                        'admin' => 'Administrateur',
                        'candidate' => 'Candidat',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut du compte')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs seulement')
                    ->falseLabel('Inactifs seulement'),
                Tables\Filters\Filter::make('blocked')
                    ->label('Utilisateurs bloqués')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('blocked_at')),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('block')
                    ->label('Bloquer')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->isActive() && $record->user_type !== 'admin')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison du blocage')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->block($data['reason'], auth()->user());
                    })
                    ->successNotificationTitle('Utilisateur bloqué avec succès'),
                Tables\Actions\Action::make('unblock')
                    ->label('Débloquer')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->isActive())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->unblock();
                    })
                    ->successNotificationTitle('Utilisateur débloqué avec succès'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->user_type === 'admin'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations personnelles')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Nom complet'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Téléphone')
                            ->placeholder('Non renseigné'),
                        Infolists\Components\TextEntry::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('Non renseigné'),
                    ])->columns(2),

                Infolists\Components\Section::make('Informations du compte')
                    ->schema([
                        Infolists\Components\TextEntry::make('user_type')
                            ->label('Type d\'utilisateur')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'success',
                                'candidate' => 'primary',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'admin' => 'Administrateur',
                                'candidate' => 'Candidat',
                                default => $state,
                            }),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Statut du compte')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i'),
                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('Email vérifié le')
                            ->dateTime('d/m/Y à H:i')
                            ->placeholder('Non vérifié'),
                    ])->columns(2),

                Infolists\Components\Section::make('Informations de blocage')
                    ->schema([
                        Infolists\Components\TextEntry::make('blocked_at')
                            ->label('Bloqué le')
                            ->dateTime('d/m/Y à H:i'),
                        Infolists\Components\TextEntry::make('blocked_reason')
                            ->label('Raison du blocage'),
                        Infolists\Components\TextEntry::make('blockedBy.name')
                            ->label('Bloqué par'),
                    ])
                    ->visible(fn ($record) => $record->blocked_at)
                    ->columns(1),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\TextEntry::make('candidateApplications_count')
                            ->label('Nombre de candidatures')
                            ->state(fn ($record) => $record->candidateApplications()->count()),
                        Infolists\Components\TextEntry::make('pending_applications_count')
                            ->label('Candidatures en attente')
                            ->state(fn ($record) => $record->candidateApplications()->where('status', 'pending')->count()),
                        Infolists\Components\TextEntry::make('analyzed_applications_count')
                            ->label('Candidatures analysées')
                            ->state(fn ($record) => $record->candidateApplications()->where('status', 'analyzed')->count()),
                    ])
                    ->visible(fn ($record) => $record->user_type === 'candidate')
                    ->columns(3),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
