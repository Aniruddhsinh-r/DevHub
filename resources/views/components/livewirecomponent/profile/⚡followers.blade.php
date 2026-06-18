<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;
    public $search = '';

    public function mount() {
        $this->search = request()->query('follower', '');
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
