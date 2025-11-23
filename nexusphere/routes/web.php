<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\PrcController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CircleController;

Route::middleware('guest')->group(function(){
    Route::get('/newLogin',[UserController::class,'newLoginForm'])->name('newLogin');
    Route::post('/newLogin',[UserController::class,'register']);
    Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
    Route::post('/login',[LoginController::class,'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dmlist',[DmController::class,'dmlistfront'])->name('dm-list');
    Route::get('dm',[DmController::class,'dmfront'])->name('dm');

    Route::get('/profile', [ProfileController::class, 'profileFront'])->name('profile');  //自分のプロフィール
    Route::get('/profile/edit',[ProfileController::class,'edit'])->name('profile.edit');//プロフィール編集画面
    Route::get('/profile/{id}',[ProfileController::class,'profileOther'])->name('profile.other'); //他人のプロフィール
    Route::post('/profile/logout',[LoginController::class,'logout'])->name('logout');

    Route::get('/post', [PrcController::class, 'post'])->name('post');
    Route::post('/post',[PrcController::class,'store'])->name('post.back');

    Route::get('/home',[PrcController::class,'index'])->name('home');

    Route::get('circle',[CircleController::class,'circleFront'])->name('circle');
    Route::post('circle',[CircleController::class,'circleCreate']);
    Route::get('circle/create',[CircleController::class,'circleCreateFront'])->name('circle.create');
    Route::get('circle/profile',[CircleController::class,'circleProfileFront'])->name('circle.profile');
    Route::get('circle/post',[CircleController::class,'circlePostFront'])->name('circle.post');
});

// コメント投稿
Route::post('/posts/{postId}/comment', [PrcController::class, 'comment'])
    ->name('posts.comment');

// いいね
Route::post('/posts/{postId}/like', [PrcController::class, 'like'])
    ->name('posts.like');



Route::get('/', function () {return view('welcome');});