<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

Route::middleware('guest')->group(function(){
   Route::get('/register',[UserController::class,'newLoginForm'])->name('newLogin');
   Route::post('/register',[UserController::class,'register']);
   Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
   Route::post('/login',[LoginController::class,'login']);
});

Route::middleware('auth')->group(function () {
   Route::get('/dmlist',[DmController::class,'dmlistfront'])->name('dm-list');
   Route::get('dm',[DmController::class,'dmfront'])->name('dm');
   Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
   Route::get('/post', [PostController::class, 'post'])->name('post');
   Route::get('/create', [PostController::class, 'create'])->name('create');
});

Route::get('/', function () {return view('welcome');});