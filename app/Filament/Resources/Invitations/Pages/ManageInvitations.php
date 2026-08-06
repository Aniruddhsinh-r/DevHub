<?php

namespace App\Filament\Resources\Invitations\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use Illuminate\Support\Facades\URL;
use Filament\Actions\CreateAction;
use App\Models\User;
use App\Models\Invitation;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

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
            ->action(function(array $data, CreateAction $action) {
                $email = strtolower($data['email']);
                $deleted = User::onlyTrashed()->where('email',$email)->first();

                if ($deleted) {
                    Notification::make()->title('This email is blocked.')->danger()->send();
                    $action->halt();
                    return ;
                }

                $activeUser = User::where('email', $email)->first();
                if ($activeUser) {
                    Notification::make()->title('This email is already registered to an active account.')->warning()->send();
                    $action->halt();
                    return ;
                }

                $token = substr(md5(rand(0, 9) . $data['email'] . time()), 0, 32);
                $exist = Invitation::where('email',$email)->first();

                if(!$exist) {
                    Invitation::create([
                        'email' => $email,
                        'token' => $token,
                        'expires_at' => now()->addMinutes(30)
                    ]);
                } elseif ($exist?->expires_at > now()) {
                    $remaining = $exist->expires_at->diffForHumans(null, true);
                    Notification::make()->title("Please wait {$remaining} before resending.")->danger()->send();

                    $action->halt();
                    return;
                } else {
                    $exist->update(['status' => 'pending', 'token' => $token, 'expires_at' => now()->addMinutes(30)]);
                    Notification::make()->title("Invitation resend successfully.")->success()->send();
                }

                $message = URL::temporarySignedRoute('invitation-register',
                    now()->addMinutes(180),
                    ['token' => ($token)]
                );

                Mail::to($email)->queue(new InvitationMail($message));
            })->successNotificationTitle('Invite sent successfully.'),
        ];
    }
}
