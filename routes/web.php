<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\articleController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\commentController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\LoginUserController;
use Illuminate\Support\Facades\Route;

// Route::get('/home', function () {
//     return view('components.home');
// })->name('home');
Route::post('/article/comment', [commentController::class, 'create'])->name('postcomment');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/profile/edit/{id}', [ProfileController::class, 'edit'])->name('profileedit');
// Route::patch('/profile/{profile}', [ProfileController::class, 'update'])->name('profile.update');
Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::get('/login', [LoginUserController::class, 'index'])->name('login');
Route::post('/login', [LoginUserController::class, 'store'])->name('login');
Route::post('/logout', [LoginUserController::class, 'destroy'])->name('logout');

/** articals */
Route::get('/articles/create', [articleController::class, 'index'])->name('articleForm');
Route::post('/articles/create', [articleController::class, 'create'])->name('createArticle');
Route::get('/articles', [articleController::class, 'displayArticle'])->name('showArticle');
Route::get('/article/{user}/article', [articleController::class, 'published'])->name('publishedarticle');
Route::get('/home', [articleController::class, 'showDraftarticle'])->name('home');
Route::get('/edit/{article}', [articleController::class, 'editArticle'])->name('editArticle');
Route::patch('/edit/{article}', [articleController::class, 'update'])->name('updateArticle');
Route::delete('/delete/{article}', [articleController::class, 'destroy'])->name('deleteArticle');
Route::get('/article/{article}', [articleController::class, 'show'])->name('specificArticle');
Route::post('/article/{article}/like', [LikeController::class, 'like'])->name('likearticle');
Route::post('/article/{article}/bookmark', [BookmarkController::class, 'bookmark'])->name('bookmarkarticle');
Route::get('/{user}/bookmark', [BookmarkController::class, 'show'])->name('showBookmars');


Route::get('/user/{user:name}', [ProfileController::class, 'show'])->name('userprofile');
Route::get('/user/article/{user}', [articleController::class, 'userarticleshow'])->name('userArticle');
Route::post('/follow/{id}', [FollowerController::class, 'follow'])->name('follow');

Route::get('/admin/dashboard', [AdminController::class, 'index']);
Route::get('/admin/user', [AdminController::class, 'user']);
Route::delete('/admin/user/remove/{user}', [AdminController::class, 'userRemove']);
Route::get('/admin/categories', [AdminController::class, 'show']);
Route::post('/admin/categories', [AdminController::class, 'create']);
Route::delete('/admin/categories/delete/{category}', [AdminController::class, 'destroy']);

/** forget psswords */
Route::middleware('guest')->controller(ForgotPasswordController::class)->group(function () {
    Route::get('/password/forgot', 'create')->name('password.forgot');
    Route::post('/password/forgot', 'store')->name('password.forgot.post');
    Route::get('/password/reset', 'resetform')->name('password.reset');
    Route::post('/password/reset', 'reset')->name('password.reset.post');
});
