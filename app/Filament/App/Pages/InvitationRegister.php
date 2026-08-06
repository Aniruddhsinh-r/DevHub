<?php

namespace App\Filament\App\Pages;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationRegister extends SimplePage implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'invitation/{token}';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return true;
    }

    public ?Invitation $invitation = null;

    public ?array $data = [];

    public function mount(string $token): void
    {
        if (Auth::check()) {
            Notification::make()->title('You are already logged in to our system.')->warning()->send();
            $this->redirect('/home', navigate: true);
        } else {
            $invitation = Invitation::where('token', $token)->first();

            if (! $invitation) {
                abort(410);
            }
            if ($invitation->status === 'accepted') {
                abort(409);
            }
            if ($invitation->status === 'expired' || $invitation->expires_at?->isPast()) {
                abort(410);
            }

            $this->invitation = $invitation;
            $this->data['email'] = $invitation->email;
        }
    }

    public function getTitle(): string
    {
        return 'Complete your registration';
    }

    public function getHeading(): string
    {
        return 'Complete your registration';
    }

    public function getSubheading(): ?string
    {
        return 'Set a name and password to activate your account.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('email')
                        ->label('Email address')
                        ->dehydrated()
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Full name')
                        ->placeholder('e.g. John Doe')
                        ->required()
                        ->autofocus()
                        ->trim()
                        ->maxLength(50)
                        ->columnSpanFull(),
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->confirmed()
                        ->minLength(8)
                        ->maxLength(255)
                        ->required(),
                ])
                ->extraAttributes(['class' => 'max-w-md mx-auto w-full'])
                ->id('invitation-register-form')
                ->livewireSubmitHandler('register')
                ->footer([
                    Actions::make([
                        Action::make('register')
                            ->label('Create account')
                            ->submit('register')
                            ->color('primary'),
                    ])->fullWidth(),
                ]),
            ])
            ->statePath('data');
    }

    public function register(): void
    {
        $data = $this->data;

        if (User::where('email', $this->invitation->email)->exists()) {
            abort(409);
        }

        if ($this->invitation->email !== $data['email']) {
            $this->data['email'] = $this->invitation->email;
            Notification::make()->title('This Invitation email is not valid.')->danger()->send();
            return;
        }

        $expired = Invitation::where('email', $this->invitation->email)->first();
        if ($expired->expires_at->isPast()) {
            abort(410);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $this->invitation->email,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole(UserRole::AUTHOR);
        $this->invitation->update(['status' => 'accepted']);
        Auth::login($user);
        Notification::make()->title('Welcome to DevHub! Your account has been created.')->success()->send();

        $this->redirect('/home', navigate: false);
    }
}
