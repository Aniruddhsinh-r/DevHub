<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return redirect('/home');})->name('/');
Route::livewire('/home', 'livewirecomponent.home.home-page')->name('home');
Route::livewire('/articles', 'livewirecomponent.article.article-card')->name('articles.index');
Route::livewire('/invitation/{email}', 'livewirecomponent.invitation')->name('invitation')->middleware('signed');

Route::middleware('guest')->group(function () {
    Route::livewire('/password/forgot', 'livewirecomponent.auth.password-forgot')->name('password.forgot');
    Route::livewire('/password/forgot/otp', 'livewirecomponent.auth.verify-otp')->name('password.forgot.otp')->middleware('throttle:otp');
    Route::livewire('/password/reset/password', 'livewirecomponent.auth.reset-password')->name('password.reset');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/register', 'livewirecomponent.auth.register')->name('register.create');
    Route::livewire('/login', 'livewirecomponent.auth.login')->name('login');
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
    Route::livewire('/bookmarks', 'livewirecomponent.bookmark.bookmarks')->name('show.bookmarks');
    Route::livewire('/profile/followers/{user}', 'livewirecomponent.profile.followers')->name('followers');
    Route::livewire('/profile/followings/{user}', 'livewirecomponent.profile.followings')->name('followings');
});

Route::middleware(['auth','admin'])->group(function () {
    Route::livewire('/admin/dashboard', 'livewirecomponent.admin.dashboard')->name('admin.dashboard');
    Route::livewire('/admin/users', 'livewirecomponent.admin.users')->name('admin.users');
    Route::livewire('/admin/users/{user}', 'livewirecomponent.admin.users-profile')->name('admin.show.user');
    Route::livewire('/admin/user/{user}/followers', 'livewirecomponent.admin.admin-followers')->name('user.followers');
    Route::livewire('/admin/user/create', 'livewirecomponent.admin.create-user')->name('admin.users.create');
    Route::livewire('/admin/articles/create', 'livewirecomponent.admin.create-articles')->name('admin.articles.create');
    Route::livewire('/admin/users/edit/{user}', 'livewirecomponent.admin.user-edit')->name('admin.edit.user');
    Route::livewire('/admin/categories', 'livewirecomponent.admin.category-list')->name('admin.categories');
    Route::livewire('/admin/articles', 'livewirecomponent.admin.article-list')->name('admin.articles');
    Route::livewire('/admin/articles/drafts', 'livewirecomponent.admin.admin-drafts')->name('admin.drafts');
    Route::livewire('/admin/article/{article}/update', 'livewirecomponent.admin.admin-article-update')->name('admin.articles.edit');
    Route::livewire('/admin/articles/{article}', 'livewirecomponent.admin.show-article')->name('admin.article.show');
    Route::livewire('/admin/articles/{user}/published', 'livewirecomponent.admin.user-articles')->name('admin.user.published');
    Route::livewire('/admin/profile', 'livewirecomponent.admin.admin-profile')->name('admin.profile');
    Route::livewire('/admin/profile/edit', 'livewirecomponent.admin.admin-profile-edit')->name('admin-profile.edit');
});
