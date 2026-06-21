<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Follow;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;
    public $search = '';

    public function mount() {
        $this->search = request()->query('following', '');
    }

    public function render()
    {
        return view('follow.followings', [
            'followings' => Follow::with('followed')
            ->where('follower_id', $this->user->id)
            ->when($this->search, function ($query) {
                $query->whereHas('followed', function ($userQuery) {
                    $userQuery->where('name', 'LIKE', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10)
        ]);
    }
};
?>

