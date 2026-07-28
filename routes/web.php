<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return redirect('/home');})->name('/');
// Route::livewire('/home', 'livewirecomponent.home.home-page')->name('home');
// Route::livewire('/articles', 'livewirecomponent.article.article-card')->name('articles.index');
Route::livewire('/invitation/expire', 'livewirecomponent.pages.already-exist')->name('invite.exist');
Route::livewire('/invitation/{email}', 'livewirecomponent.invitation')->name('invitation');

Route::middleware('guest')->group(function () {
    Route::livewire('/register', 'livewirecomponent.auth.register')->name('register.create');
    Route::livewire('/login', 'livewirecomponent.auth.login')->name('login');
    Route::livewire('/password/forgot', 'livewirecomponent.auth.password-forgot')->name('password.forgot');
    Route::livewire('/password/forgot/otp', 'livewirecomponent.auth.verify-otp')->name('password.forgot.otp')->middleware('throttle:otp');
    Route::livewire('/password/reset/password', 'livewirecomponent.auth.reset-password')->name('password.reset');
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
    // Route::livewire('/app/articles/create', 'livewirecomponent.article.create-article')->name('articles.create');
    // Route::livewire('/app/articles/{record}/edit', 'livewirecomponent.article.edit-article')->name('articles.edit');
    // Route::livewire('/articles/{article}', 'livewirecomponent.article.show-article')->name('articles.show');
    Route::livewire('/bookmarks', 'livewirecomponent.bookmark.bookmarks')->name('show.bookmarks');
    Route::livewire('/profile/followers/{user}', 'livewirecomponent.profile.followers')->name('followers');
    Route::livewire('/profile/followings/{user}', 'livewirecomponent.profile.followings')->name('followings');
});

