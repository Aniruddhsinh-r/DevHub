<?php

use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\UserController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'adminlogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/category/create', [CategoryController::class, 'create']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::put('/category/{category}/update', [CategoryController::class, 'update']);
        Route::delete('/category/{category}/delete', [CategoryController::class, 'delete']);
        Route::get('/category/{category}', [CategoryController::class, 'show']);
        Route::post('/article/create', [ArticleController::class, 'create']);
        Route::get('/article/{slug}', [ArticleController::class, 'show']);
        Route::get('/myarticles', [ArticleController::class, 'myArticle']);
        Route::put('/article/{slug}/update', [ArticleController::class, 'update']);
        Route::delete('/article/{slug}/delete', [ArticleController::class, 'delete']);
        Route::delete('/article/{slug}/forcedelete', [ArticleController::class, 'forceDelete']);
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/admin/articles', [ArticleController::class, 'adminArticles']);
        Route::post('/article/{slug}/like', [LikeController::class, 'store']);
        Route::delete('/article/{slug}/dislike', [LikeController::class, 'destroy']);
        Route::post('/article/{slug}/bookmark', [BookmarkController::class, 'store']);
        Route::get('/user/bookmark', [BookmarkController::class, 'index']);
        Route::delete('/article/{slug}/remove', [BookmarkController::class, 'destroy']);
        Route::post('/article/{slug}/comment', [CommentController::class, 'store']);
        Route::post('/article/{slug}/comment/reply', [CommentController::class, 'reply']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile/update', [UserController::class, 'update']);
        Route::put('/admin/users/{uuid}/edit', [UserController::class, 'edit']);
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::post('/admin/invitation/send', [InvitationController::class, 'store']);
        Route::post('/admin/invitation/{invitation}/resend', [InvitationController::class, 'resend']);
        Route::get('/admin/invitations', [InvitationController::class, 'index']);
        Route::get('/admin/invitation/{invitation}', [InvitationController::class, 'show']);
        Route::delete('/admin/invitation/{invitation}/delete', [InvitationController::class, 'delete']);
        Route::delete('/users/{uuid}/delete', [UserController::class, 'delete']);
        Route::delete('/users/{uuid}/forcedelete', [UserController::class, 'forceDelete']);
        Route::get('/users/{uuid}', [UserController::class, 'show']);
        Route::post('/user/{uuid}/follow', [FollowController::class, 'store']);
        Route::delete('/user/{uuid}/unfollow', [FollowController::class, 'destroy']);
    });
});

// Route::middleware(['web', 'auth:web'])->prefix('v1')->group(function () {
//     Route::post('/category/create', [CategoryController::class, 'create']);
//     Route::post('/categorys', [CategoryController::class, 'show']);
// });
