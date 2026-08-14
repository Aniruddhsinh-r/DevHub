<?php

namespace App\Filament\Resources\Invitations\Pages;

use App\Actions\SendInvitation;
use App\Filament\Resources\Invitations\InvitationResource;
use App\Models\Invitation;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class ManageInvitations extends ManageRecords
{
    protected static string $resource = InvitationResource::class;

    public function mount(): void
    {
        parent::mount();
        Invitation::where('status', 'pending')->where('expires_at', '<', now())->update(['status' => 'expired']);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('all')->badge(Invitation::count()),
            'pending' => Tab::make('pending')->badge(Invitation::query()->where('status', 'pending')->count())->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'accepted' => Tab::make('accepted')->badge(Invitation::query()->where('status', 'accepted')->count())->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'accepted')),
            'expired' => Tab::make('expired')->badge(Invitation::query()->where('status', 'expired')->count())->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data, CreateAction $action) {
                    try {
                        app(SendInvitation::class)->handle($data['email']);

                        Notification::make()
                            ->title('Invitation sent successfully.')
                            ->success()
                            ->send();

                    } catch (RuntimeException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
