<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

// 🔹 未ログイン時
Route::middleware('guest')->group(function(){
    Route::get('/newLogin',[UserController::class,'newLoginForm'])->name('newLogin');
    Route::post('/newLogin',[UserController::class,'register']);
    Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
    Route::post('/login',[LoginController::class,'login']);
});

// 🔹 ログイン後
Route::middleware('auth')->group(function () {
    Route::get('/dmlist',[DmController::class,'dmlistfront'])->name('dm-list');
    Route::get('/dm',[DmController::class,'dmfront'])->name('dm');
    Route::get('/profile', [ProfileController::class, 'profileFront'])->name('profile');  
    Route::get('/profile/{user}',[ProfileController::class,'profileOther'])->name('profile-other'); 
    Route::get('/profile/edit',[ProfileController::class,'edit'])->name('profile-edit');
    Route::post('/profile/edit',[ProfileController::class,'update'])->name('profile-update');
    Route::get('/post', [PostController::class, 'post'])->name('post');
    Route::get('/create', [PostController::class, 'create'])->name('create');
});

// 🔹 投稿機能（全員がアクセス可能）
Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/feed', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
Route::post('/posts/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
Route::get('/post/create', [PostController::class, 'createPost'])->name('posts.create');

//　投稿フォームを表示
Route::get('/create-post', function () {
    return view('create-post');
})->name('posts.create');

//　投稿送信処理
Route::post('/post', [PostController::class, 'store'])->name('posts.store');

// ❌ ← これ削除
// Route::get('/', function () {return view('welcome');});
