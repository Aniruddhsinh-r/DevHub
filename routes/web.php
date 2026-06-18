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

Route::get('/', function () {return redirect('/home');});
// Route::get('/home', [ArticleController::class, 'home'])->name('home');
Route::livewire('/home', 'livewirecomponent.home.home-page')->name('home');
// Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::livewire('/articles', 'livewirecomponent.article.article-card')->name('articles.index');

Route::middleware('guest')->group(function () {
    // Route::get('/register', [RegisteredUserController::class, 'create'])->name('register.create')->middleware('throttle:register');
    // Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store')->middleware('throttle:register');
    Route::livewire('/register', 'livewirecomponent.auth.register')->name('register.create')->middleware('throttle:register');
    // Route::get('/login', [LoginUserController::class, 'create'])->name('login');
    // Route::post('/login', [LoginUserController::class, 'store'])->name('login.store')->middleware('throttle:login');
    Route::livewire('/login', 'livewirecomponent.auth.login')->name('login')->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    // Route::get('/articles/{user}/published', [ArticleController::class, 'userpublished'])->name('user.published');
    Route::livewire('/articles/{user}/published', 'livewirecomponent.article.my-published')->name('user.published');
    // Route::get('/articles/drafts', [ArticleController::class, 'draftArticle'])->name('drafts');
    Route::livewire('/articles/draft', 'livewirecomponent.article.drafts')->name('drafts');
    // Route::post('/logout', [LoginUserController::class, 'destroy'])->name('logout');
    Route::livewire('/logout', 'livewirecomponent.auth.logout')->name('logout');
});

Route::middleware(['auth','author'])->group(function () {
    // Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::livewire('/profile/edit', 'livewirecomponent.profile.edit-profile')->name('profile.edit');
    // Route::resource('/profile', ProfileController::class)->except(['edit','index','update','show']);
    Route::livewire('/profile', 'livewirecomponent.profile.my-profile')->name('profile.index');
    Route::livewire('/profile/{user}', 'livewirecomponent.profile.profile')->name('profile.show');
    // Route::get('/articles/myarticle', [ArticleController::class, 'myArticles'])->name('publishedarticle');
    Route::livewire('/articles/myarticle', 'livewirecomponent.article.my-articles')->name('publishedarticle');
    Route::resource('/articles', ArticleController::class)->except(['index','show','create','store','edit','update','destroy']);
    Route::livewire('/articles/create', 'livewirecomponent.article.create-article')->name('articles.create');
    Route::livewire('/articles/edit/{article}', 'livewirecomponent.article.edit-article')->name('articles.edit');
    Route::livewire('/articles/{article}', 'livewirecomponent.article.show-article')->name('articles.show');
    Route::livewire('/articles/destroy/{article}', 'livewirecomponent.article.delete-article')->name('articles.destroy');
    // Route::post('/article/{article}/like', [LikeController::class, 'like'])->name('articles.like');
    // Route::post('/article/{article}/bookmark', [BookmarkController::class, 'bookmark'])->name('articles.bookmark');
    Route::get('/bookmarks', [BookmarkController::class, 'show'])->name('show.bookmarks');
    Route::livewire('/bookmarks', 'livewirecomponent.bookmark.bookmarks')->name('show.bookmarks');
    // Route::get('/profile/followers/{user}', [FollowerController::class, 'followers'])->name('followers');
    Route::livewire('/profile/followers/{user}', 'livewirecomponent.profile.followers')->name('followers');
    // Route::get('/profile/followings/{user}', [FollowerController::class, 'followings'])->name('followings');
    Route::livewire('/profile/followings/{user}', 'livewirecomponent.profile.followings')->name('followings');
    // Route::post('/follow/{user}', [FollowerController::class, 'follow'])->name('user.follow');
    // Route::post('/article/comment', [CommentController::class, 'store'])->name('post.comment');
});

Route::middleware(['auth','admin'])->controller(AdminController::class)->group(function () {
    Route::get('/admin/dashboard', 'index')->name('admin.dashboard');
    Route::get('/admin/users', 'user')->name('admin.users');
    Route::get('/admin/users/{user}', 'showUser')->name('admin.show.user');
    Route::delete('/admin/user/remove/{user}', 'userRemove')->name('admin.user.remove');
    Route::get('/admin/categories', 'show')->name('admin.categories');
    Route::post('/admin/categories', 'create')->name('admin.category.post');
    Route::get('/admin/articles', 'articles')->name('admin.articles');
    Route::get('/admin/articles/{article}', 'showArticle')->name('admin.article.show');
    Route::get('/admin/articles/{user}/published', 'userArticle')->name('admin.user.published');
    Route::delete('/admin/categories/delete/{category}', 'destroy')->name('admin.category.delete');
});

Route::middleware('guest')->controller(ForgotPasswordController::class)->group(function () {
    Route::get('/password/forgot', 'create')->name('password.forgot');
    Route::post('/password/forgot', 'store')->name('password.forgot.post');
    Route::get('/password/forgot/otp', 'OTPform')->name('password.forgot.otp');
    Route::post('/password/forgot/otp', 'OTPverify')->name('password.forgot.otp.post')->middleware('throttle:otp');
    Route::get('/password/reset/password', 'resetform')->name('password.reset');
    Route::post('/password/reset/password', 'reset')->name('password.reset.post');
});
