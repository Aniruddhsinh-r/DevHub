<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Users\RelationManagers\FollowersRelationManager;
use App\Filament\Resources\Users\RelationManagers\FollowingRelationManager;
use App\Filament\Resources\Users\RelationManagers\ArticlesRelationManager;

class Profile extends Page
{
    protected static ?string $slug = 'my-profile';

    protected static ?int $navigationSort = 4;

    protected string $view = 'components.filament.profile';

    public Model $ownerRecord;

    public string $pageClass;

    public array $relationManagers = [];

}