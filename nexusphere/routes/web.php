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
   Route::get('/profile/{user}',[ProfileController::class,'profileOther'])->name('profile-other'); //他人のプロフィール
   Route::get('/profile/edit',[ProfileController::class,'edit'])->name('profile-edit');//プロフィール編集画面
   Route::post('/profile/edit',[ProfileController::class,'update'])->name('profile-update');//プロフィール更新処理
   Route::get('/post', [PostController::class, 'post'])->name('post');
   Route::get('/create', [PostController::class, 'create'])->name('create');
});

Route::get('/', function () {return view('welcome');});