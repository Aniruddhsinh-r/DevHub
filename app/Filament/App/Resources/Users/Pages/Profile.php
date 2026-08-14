<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use App\Filament\Pages\Auth\EditProfile as AuthEditProfile;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class Profile extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'My Profile';

    public function mount(int|string|null $record = null): void
    {
        $this->record = auth()->user();
    }

    public function getRecord(): Model
    {
        return auth()->user();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Profile')
                ->url(AuthEditProfile::getUrl()),
        ];
    }
}
