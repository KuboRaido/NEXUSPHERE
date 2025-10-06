<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ProfileController;

Route::get('/', [PostController::class, 'index']);
Route::get('/feed', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::post('/posts/{post}/like', [PostController::class, 'like']);
Route::post('/posts/{post}/comment', [PostController::class, 'comment']);

Route::get('/register', [RegisterController::class, 'show']);
Route::post('/register', [RegisterController::class, 'register']);

// プロフィール関連（ログイン必須）
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});
