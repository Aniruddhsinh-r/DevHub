<?php

use App\Http\Controllers\articleController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\LoginUserController;
use Illuminate\Support\Facades\Route;

// Route::get('/home', function () {
//     return view('components.home');
// })->name('home');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::get('/login', [LoginUserController::class, 'index'])->name('login');
Route::post('/login', [LoginUserController::class, 'store'])->name('login');
Route::post('/logout', [LoginUserController::class, 'destroy'])->name('logout');

/** articals */
Route::get('/articles/create', [articleController::class, 'index'])->name('articleForm');
Route::post('/articles/create', [articleController::class, 'create'])->name('createArticle');
Route::get('/articles', [articleController::class, 'displayArticle'])->name('showArticle');
Route::get('/article/my-published', [articleController::class, 'published'])->name('publishedarticle');
Route::get('/home', [articleController::class, 'showDraftarticle'])->name('home');
Route::get('/edit/{article}', [articleController::class, 'editArticle'])->name('editArticle');
Route::patch('/edit/{article}', [articleController::class, 'update'])->name('updateArticle');
Route::delete('/delete/{article}', [articleController::class, 'destroy'])->name('deleteArticle');
Route::get('/article/{article}', [articleController::class, 'show'])->name('specificArticle');

Route::get('/{user:name}', [ProfileController::class, 'show'])->name('userprofile');

Route::post('/follow/{id}', [FollowerController::class, 'follow'])->name('follow');
/** forget psswords */
Route::middleware('guest')->controller(ForgotPasswordController::class)->group(function () {
    Route::get('/password/forgot', 'create')->name('password.forgot');
    Route::post('/password/forgot', 'store')->name('password.forgot.post');
    Route::get('/password/reset', 'resetform')->name('password.reset');
    Route::post('/password/reset', 'reset')->name('password.reset.post');
});
