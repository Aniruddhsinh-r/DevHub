<?php

namespace App\Filament\Resources\Invitations;

use App\Filament\Resources\Invitations\Pages\ManageInvitations;
use App\Actions\SendInvitation;
use App\Models\Invitation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use RuntimeException;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;
use Filament\Tables\Table;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $recordTitleAttribute = 'email';

    protected static string | UnitEnum | null $navigationGroup = 'Invites';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Invitation $record) => $record->status !== 'accepted')
                    ->action(function ($record) {
                        try {
                            app(SendInvitation::class)->handle($record['email']);

                            Notification::make()
                                ->title('Invitation resent successfully.')
                                ->success()
                                ->send();

                        } catch (RuntimeException $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()->successNotificationTitle('Invitation deleted successfully.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->successNotificationTitle('Invitation deleted successfully.'),
                ]),
            ])->emptyStateHeading(fn ($livewire) => match ($livewire->activeTab ?? $livewire->activityTab ?? 'all') {
                'all'      => 'No invitations have been sent yet.',
                'pending'  => 'No pending invitations found.',
                'accepted' => 'No accepted invitations found.',
                'expired'  => 'No expired invitations found.',
                default    => 'No invitations found.',
            })
                ->searchPlaceholder('Search invitation');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvitations::route('/'),
        ];
    }
}
