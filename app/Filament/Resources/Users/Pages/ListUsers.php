<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newUser')
                ->label('Invite User')
                ->icon('heroicon-o-paper-airplane')
                ->url(InvitationResource::getUrl()),
        ];
    }
}
