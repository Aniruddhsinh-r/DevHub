<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

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
            'users' => User::role('author')->where('name', 'LIKE', "%{$this->search}%")->paginate(6),
        ]);
    }

    public function remove($userId) {
        $user = User::find($userId);
        $this->authorize('remove', $user);

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
