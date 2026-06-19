<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\LoginUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return redirect('/home');})->name('/');
Route::livewire('/home', 'livewirecomponent.home.home-page')->name('home');
Route::livewire('/articles', 'livewirecomponent.article.article-card')->name('articles.index');

Route::middleware('guest')->group(function () {
    Route::livewire('/register', 'livewirecomponent.auth.register')->name('register.create')->middleware('throttle:register');
    Route::livewire('/login', 'livewirecomponent.auth.login')->name('login')->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/articles/{user}/published', 'livewirecomponent.article.my-published')->name('user.published');
    Route::livewire('/articles/draft', 'livewirecomponent.article.drafts')->name('drafts');
    Route::post('/logout', function(){
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('home')->with('success', 'Logged out successfully.');})->name('logout');
});

Route::middleware(['auth','author'])->group(function () {
    Route::livewire('/profile/edit', 'livewirecomponent.profile.edit-profile')->name('profile.edit');
    Route::livewire('/profile', 'livewirecomponent.profile.my-profile')->name('profile.index');
    Route::livewire('/profile/{user}', 'livewirecomponent.profile.profile')->name('profile.show');
    Route::livewire('/articles/myarticle', 'livewirecomponent.article.my-articles')->name('publishedarticle');
    Route::livewire('/articles/create', 'livewirecomponent.article.create-article')->name('articles.create');
    Route::livewire('/articles/edit/{article}', 'livewirecomponent.article.edit-article')->name('articles.edit');
    Route::livewire('/articles/{article}', 'livewirecomponent.article.show-article')->name('articles.show');
    Route::livewire('/articles/destroy/{article}', 'livewirecomponent.article.delete-article')->name('articles.destroy');
    Route::livewire('/bookmarks', 'livewirecomponent.bookmark.bookmarks')->name('show.bookmarks');
    Route::livewire('/profile/followers/{user}', 'livewirecomponent.profile.followers')->name('followers');
    Route::livewire('/profile/followings/{user}', 'livewirecomponent.profile.followings')->name('followings');
});

Route::middleware(['auth','admin'])->group(function () {
    Route::livewire('/admin/dashboard', 'livewirecomponent.admin.dashboard')->name('admin.dashboard');
    Route::livewire('/admin/users', 'livewirecomponent.admin.users')->name('admin.users');
    Route::livewire('/admin/users/{user}', 'livewirecomponent.admin.users-profile')->name('admin.show.user');
    Route::livewire('/admin/categories', 'livewirecomponent.admin.category-list')->name('admin.categories');
    Route::livewire('/admin/articles', 'livewirecomponent.admin.article-list')->name('admin.articles');
    Route::livewire('/admin/articles/{article}', 'livewirecomponent.admin.show-article')->name('admin.article.show');
    Route::livewire('/admin/articles/{user}/published', 'livewirecomponent.admin.user-articles')->name('admin.user.published');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/password/forgot', 'livewirecomponent.auth.password-forgot')->name('password.forgot');
    Route::livewire('/password/forgot/otp', 'livewirecomponent.auth.verify-otp')->name('password.forgot.otp');
    Route::livewire('/password/reset/password', 'livewirecomponent.auth.reset-password')->name('password.reset');
});
