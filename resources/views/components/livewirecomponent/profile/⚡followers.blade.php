<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Follow;
use Livewire\Attributes\On;
use App\Events\UserCreate;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;
    public $search = '';

    public function mount() {
        $this->search = request()->query('follower', '');
    }

    #[On('echo:users,UserCreate')]
    public function refresUsersList()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('follow.followers', [
            'followers' => Follow::with('follower')
            ->where('followed_id', $this->user->id)
            ->when($this->search, function ($query) {
                $query->whereHas('follower', function ($userQuery) {
                    $userQuery->where('name', 'LIKE', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10)
        ]);
    }
};
?>
