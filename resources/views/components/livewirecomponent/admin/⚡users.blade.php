<?php

use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Enums\UserRole;
use App\Models\User;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithPagination;

    public $search = '';
    public function mount() {
        $this->search = request()->query('search', '');
    }


    public function render()
    {
        return view('admin.users.users', [
            'users' => User::where('name', 'LIKE', "%{$this->search}%")->where('id', '!=', auth()->id())->withoutRole(UserRole::SUPERADMIN)->latest()->paginate(6),
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

        DB::transaction(function () use ($user) {
            $user->views()->delete();
            $user->comments()->delete();
            $user->bookmarks()->delete();
            $user->likes()->delete();
            $user->articles()->delete();
            $user->notifications()->delete();
            $user->delete();
        });
        $this->dispatch('live-notification', message: 'User removed successfully.');
    }
};
?>
