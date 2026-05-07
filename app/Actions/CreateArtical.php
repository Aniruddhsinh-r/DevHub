<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateArtisan
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $attributes, ?User $user = null): void
    {

        // unset($attributes['_token']);

        // // dd([
        // //     'all' => $attributes,
        // // ]);
        // /** @var User */
        // $user ??= Auth::user();

        // $data = collect($attributes)->only([
        //     'title', 'description', 'status', 'links',
        // ])->toArray();

        // if ($attributes['image'] ?? false) {
        //     $data['image_path'] = $attributes['image']->store('ideas', 'public');
        // }

        // DB::transaction(function () use ($data, $attributes) {
        //     $idea = $this->user->ideas()->create($data);

        //     // $steps = collect($attributes['steps'] ?? [])->map(fn ($step): array => ['description' => $step]);
        //     $steps = collect($attributes['steps'] ?? [])->map(fn ($step) => ['description' => $step['description'],'completed' => $step['completed'] ?? false,])->toArray();

        //     $idea->steps()->createMany($steps);
        // });
    }
}
