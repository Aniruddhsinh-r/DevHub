<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\articleController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\commentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/home');
});
Route::get('/home', [articleController::class, 'home'])->name('home');
Route::get('/articles', [articleController::class, 'displayArticle'])->name('showArticle');
Route::get('/articles/search', [SearchController::class, 'articleSearch']);

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    Route::get('/login', [LoginUserController::class, 'index'])->name('login');
    Route::post('/login', [LoginUserController::class, 'store'])->name('login');
});

Route::middleware(['auth','author'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit/{id}', [ProfileController::class, 'edit'])->name('profileedit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/articles/create', [articleController::class, 'index'])->name('articleForm');
    Route::post('/articles/create', [articleController::class, 'create'])->name('createArticle');
    Route::get('/articles/myarticle', [articleController::class, 'published'])->name('publishedarticle');
    Route::get('/edit/{article}', [articleController::class, 'editArticle'])->name('editArticle');
    Route::patch('/edit/{article}', [articleController::class, 'update'])->name('updateArticle');
    Route::delete('/delete/{article}', [articleController::class, 'destroy'])->name('deleteArticle');
    Route::post('/article/{article}/like', [LikeController::class, 'like'])->name('likearticle');
    Route::post('/article/{article}/bookmark', [BookmarkController::class, 'bookmark'])->name('bookmarkarticle');
    Route::get('/{user}/bookmark', [BookmarkController::class, 'show'])->name('showBookmars');
    Route::get('/profile/followers/{user}', [FollowerController::class, 'followers']);
    Route::get('/profile/followings/{user}', [FollowerController::class, 'followings']);
    Route::post('/follow/{id}', [FollowerController::class, 'follow'])->name('follow');
    Route::post('/article/comment', [commentController::class, 'create'])->name('postcomment');
    });

    Route::middleware('auth')->group(function () {
    Route::get('/articles/{user}/published', [articleController::class, 'userpublished']);
    Route::get('/articles/{user}/drafts', [articleController::class, 'showDraftarticle'])->name('drafts');
    Route::get('/articles/{article}', [articleController::class, 'show'])->name('specificArticle');

    Route::get('/user/{user}', [ProfileController::class, 'show'])->name('userprofile');
    Route::get('/user/article/{user}', [articleController::class, 'userarticleshow'])->name('userArticle');
    Route::post('/logout', [LoginUserController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth','admin'])->controller(AdminController::class)->group(function () {
    Route::get('/admin/dashboard', 'index')->name('admin.dashboard');
    Route::get('/admin/users', 'user');
    Route::get('/admin/users/{user}', 'showuser');
    Route::delete('/admin/user/remove/{user}', 'userRemove');
    Route::get('/admin/categories', 'show');
    Route::post('/admin/categories', 'create');
    Route::get('/admin/articles', 'articles');
    Route::get('/admin/articles/{article}', 'showArticle');
    Route::get('/admin/articles/{user}/published', 'userPublished');
    Route::delete('/admin/categories/delete/{category}', 'destroy');
    Route::post('/search/users', [SearchController::class, 'userSearch']);
});

Route::middleware('guest')->controller(ForgotPasswordController::class)->group(function () {
    Route::get('/password/forgot', 'create')->name('password.forgot');
    Route::post('/password/forgot', 'store')->name('password.forgot.post');
    Route::get('/password/forgot/otp/{email}', 'OTPform')->name('password.forgot.otp');
    Route::post('/password/forgot/otp/{email}', 'OTPverify')->name('password.forgot.otp.post');
    Route::get('/password/reset/password/{id}', 'resetform')->name('password.reset');
    Route::post('/password/reset/password/{id}', 'reset')->name('password.reset.post');
});
