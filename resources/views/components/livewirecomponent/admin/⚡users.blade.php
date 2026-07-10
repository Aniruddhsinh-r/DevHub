<?php

use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Invitation;
use Livewire\Component;
use App\Enums\UserRole;
use App\Events\ArticleCreate;
use App\Events\UserCreate;
use App\Models\User;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithPagination;

    public $search = '';
    public function mount() {
        $this->search = request()->query('search', '');
    }

    #[On('echo:users,UserCreate')]
    public function refresUsersList()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('admin.users.users', [
            'users' => User::where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
            })->where('id', '!=', auth()->id())->withoutRole(UserRole::SUPERADMIN)->latest()->paginate(6),
        ]);
    }

    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type) {
        if ($type === 'adminUserDelete') {
            $this->remove($id);
        }
    }

    public function remove($userId) {
        $user = User::findOrFail($userId);
        Gate::authorize('remove', $user);
        DB::table('sessions')->where('user_id', $user->id)->delete();

        DB::transaction(function () use ($user) {
            $user->views()->delete();
            $user->comments()->delete();
            $user->bookmarks()->delete();
            $user->likes()->delete();
            $user->articles()->delete();
            $user->notifications()->delete();
            $user->delete();
            $user->remember_token = null;
            $user->save();
            Invitation::where('email',$user->email)->delete();
        });
        ArticleCreate::dispatch();
        UserCreate::dispatch();
        $this->dispatch('live-notification', message: 'User removed successfully.');
    }
};
?>
