<?php

use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LikeController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'adminlogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/category/create', [CategoryController::class, 'create']);
        Route::get('/categories', [CategoryController::class, 'show']);
        Route::put('/category/{category}/update', [CategoryController::class, 'update']);
        Route::delete('/category/{category}/delete', [CategoryController::class, 'delete']);
        Route::post('/article/create', [ArticleController::class, 'create']);
        Route::get('/article/{slug}', [ArticleController::class, 'show']);
        Route::put('/article/{slug}/update', [ArticleController::class, 'update']);
        Route::delete('/article/{slug}/delete', [ArticleController::class, 'delete']);
        Route::delete('/article/{slug}/forcedelete', [ArticleController::class, 'forceDelete']);
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/admin/articles', [ArticleController::class, 'adminArticles']);
        Route::post('/article/{slug}/like', [LikeController::class, 'store']);
        Route::delete('/article/{slug}/dislike', [LikeController::class, 'destroy']);
        Route::post('/article/{slug}/bookmark', [BookmarkController::class, 'store']);
        Route::delete('/article/{slug}/remove', [BookmarkController::class, 'destroy']);
        // Route::post('/article/{slug}/comment', [ArticleController::class, 'create']);
        // Route::post('/article/{slug}/bookmark', [ArticleController::class, 'create']);
        // Route::post('/article/{slug}/unsave', [ArticleController::class, 'create']);
    });
});

// Route::middleware(['web', 'auth:web'])->prefix('v1')->group(function () {
//     Route::post('/category/create', [CategoryController::class, 'create']);
//     Route::post('/categorys', [CategoryController::class, 'show']);
// });
