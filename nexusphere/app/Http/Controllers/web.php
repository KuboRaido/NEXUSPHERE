<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\PrcController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CircleController;

Route::middleware('guest')->group(function(){
    Route::get('/newlogin',[UserController::class,'newLoginForm'])->name('newLogin');
    Route::post('/newlogin',[UserController::class,'register'])->name('register');
    Route::get('/',[LoginController::class,'showLoginForm'])->name('login');
    Route::post('/',[LoginController::class,'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dmlist',[DmController::class,'dmlistfront'])->name('dm-list');
    Route::get('dm',[DmController::class,'dmfront'])->name('dm');

    Route::get('/profile', [ProfileController::class, 'profileFront'])->name('profile');  //自分のプロフィール
    Route::get('/profile/edit',[ProfileController::class,'edit'])->name('profile.edit');//プロフィール編集画面
    Route::get('/profile/{user_id}',[ProfileController::class,'profileOther'])->name('profile.other'); //他人のプロフィール
    Route::post('/profile/logout',[LoginController::class,'logout'])->name('logout');
    Route::post('/profile',[ProfileController::class,'post'])->name('profile.post'); 

    Route::get('/post', [PrcController::class, 'post'])->name('post');
    Route::post('/post',[PrcController::class,'store'])->name('post.back');
    Route::post('circle/{circle}/post',[PrcController::class,'circleStore'])->name('circlePost.back');

    Route::get('/home',[PrcController::class,'index'])->name('home');

    Route::get('circle',[CircleController::class,'circleFront'])->name('circle');
    Route::post('circle',[CircleController::class,'circleCreate']);
    Route::get('circle/create',[CircleController::class,'circleCreateFront'])->name('circle.create');
    Route::get('circle/{circle}',[CircleController::class,'circleProfileFront'])->name('circle.profile');
    Route::get('circle/{circle}/post',[CircleController::class,'circlePostFront'])->name('circle.post');
    Route::get('circle/{circle}/edit',[CircleController::class,'circleEdit'])->name('circle.edit');
    Route::get('circle/{circle}/dm',[CircleController::class,'circleDmFront'])->name('circle.dm');
    Route::get('circle/{circle}/request',[CircleController::class,'circleRequest'])->name('circle.request');
    Route::post('circle/{circle}',[CircleController::class,'join'])->name('circle.join');
    Route::post('circle/{circle}/request/{circle_request}/approve',[CircleController::class,'approve'])->name('circle.approve');
    Route::post('circle/{circle}/request/{circle_request}/reject',[CircleController::class,'reject'])->name('circle.reject');
});
    
// コメント投稿
Route::post('/posts/{postId}/comment', [PrcController::class, 'comment'])
    ->name('posts.comment');

// いいね
Route::post('/posts/{postId}/like', [PrcController::class, 'like'])
    ->name('posts.like');