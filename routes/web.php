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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/home');
});
Route::get('/home', [ArticleController::class, 'home'])->name('home');
Route::get('/articles', [ArticleController::class, 'displayArticle'])->name('show.articles');

Route::middleware('guest')->group(function () {
    Route::resource('/register', RegisteredUserController::class);
    Route::resource('/login', LoginUserController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/articles/{user}/published', [ArticleController::class, 'userpublished'])->name('user.published');
    Route::get('/articles/{user}/drafts', [ArticleController::class, 'showDraftarticle'])->name('drafts');
    Route::get('/user/article/{user}', [ArticleController::class, 'userarticleshow'])->name('userArticle');
    Route::post('/logout', [LoginUserController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth','author'])->group(function () {
    Route::resource('/profile', ProfileController::class)->except(['show']);
    Route::get('/user/{user}', [ProfileController::class, 'show'])->name('userprofile');
    Route::get('/articles/myarticle', [ArticleController::class, 'published'])->name('publishedarticle');
    Route::resource('/articles', ArticleController::class)->except(['index']);
    Route::post('/article/{article}/like', [LikeController::class, 'like'])->name('likearticle');
    Route::post('/article/{article}/bookmark', [BookmarkController::class, 'bookmark'])->name('bookmarkarticle');
    Route::get('/{user}/bookmark', [BookmarkController::class, 'show'])->name('show.bookmarks');
    Route::get('/profile/followers/{user}', [FollowerController::class, 'followers'])->name('followers');
    Route::get('/profile/followings/{user}', [FollowerController::class, 'followings'])->name('followings');
    Route::post('/follow/{id}', [FollowerController::class, 'follow'])->name('follow');
    Route::post('/article/comment', [CommentController::class, 'create'])->name('postcomment');
});


Route::middleware(['auth','admin'])->controller(AdminController::class)->group(function () {
    Route::get('/admin/dashboard', 'index')->name('admin.dashboard');
    Route::get('/admin/users', 'user')->name('admin.users');
    Route::get('/admin/users/{user}', 'showuser')->name('admin.show.user');
    Route::delete('/admin/user/remove/{user}', 'userRemove')->name('admin.user.remove');
    Route::get('/admin/categories', 'show')->name('admin.categories');
    Route::post('/admin/categories', 'create');
    Route::get('/admin/articles', 'articles')->name('admin.articles');
    Route::get('/admin/articles/{article}', 'showArticle')->name('admin.article.show');
    Route::get('/admin/articles/{user}/published', 'userPublished')->name('admin.user.published');
    Route::delete('/admin/categories/delete/{category}', 'destroy')->name('admin.category.delete');
});

Route::middleware('guest')->controller(ForgotPasswordController::class)->group(function () {
    Route::get('/password/forgot', 'create')->name('password.forgot');
    Route::post('/password/forgot', 'store')->name('password.forgot.post');
    Route::get('/password/forgot/otp/{email}', 'OTPform')->name('password.forgot.otp');
    Route::post('/password/forgot/otp/{email}', 'OTPverify')->name('password.forgot.otp.post');
    Route::get('/password/reset/password/{id}', 'resetform')->name('password.reset');
    Route::post('/password/reset/password/{id}', 'reset')->name('password.reset.post');
});
