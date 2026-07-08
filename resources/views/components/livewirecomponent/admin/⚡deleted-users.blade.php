<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Article;
use App\Events\UserCreate;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithPagination;
    public string $search = '';

    #[On('echo:users,UserCreate')]
    public function refresUsersList()
    {
        $this->dispatch('$refresh');
    }


    public function mount(): void
    {
        $this->search = request()->query('search', '');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function restore(int $id): void
    {
        $user = User::onlyTrashed()->findOrFail($id);
        Gate::authorize('remove', $user);
        $user->restore();
        UserCreate::dispatch();
        $this->dispatch('live-notification', message: 'User restored successfully.');
    }

    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type) {
        if ($type === 'adminUserDelete') {
            $this->forceDelete($id);
        }
    }
    public function forceDelete(int $id): void
    {
        $user = User::onlyTrashed()->findOrFail($id);
        Gate::authorize('remove', $user);
        DB::transaction(function () use ($user) {
            $user->forceDelete();
            Article::where('user_id',$user->id)->forceDelete();
        });
        UserCreate::dispatch();
        $this->dispatch('live-notification', message: 'User permanently deleted successfully.');
    }

    public function render()
    {
        return view('admin.users.deleted-users', [
            'users' => User::onlyTrashed()
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->latest('deleted_at')
            ->paginate(6),
        ]);
    }
};

?>
