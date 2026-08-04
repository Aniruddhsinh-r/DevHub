<?php

namespace App\Filament\App\Pages;

use App\Models\Invitation;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class InvitationRegister extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'invitation/{token}';

    protected string $view = 'filament.app.pages.invitation-register';

    public static function canAccess(): bool
    {
        return true;
    }

    protected static bool $shouldRegisterNavigation = false;

    public ?Invitation $invitation = null;

    public ?array $data = [];

    public function mount(string $token): void
    {
        $this->invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $this->form->fill([
            'email' => $this->invitation->email,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->email()
                ->required()
                ->disabled(),
            TextInput::make('password')
                ->password()
                ->required(),
            TextInput::make('name')
                ->required(),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->getFormSchema()),
        ];
    }
}