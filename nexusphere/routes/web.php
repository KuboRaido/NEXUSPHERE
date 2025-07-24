<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DatabaseController;
use Illuminate\Container\Attributes\Database;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\controllers\DmController;

Route::get('/', function () {return view('welcome');});

Route::get('/post',function(){return view('post');});

Route::post('/User',[UserController::class,'register']);

Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
Route::post('/login',[LoginController::class,'login']);

Route::get('/newlogin',[UserController::class,'newloginform'])->name('newlogin');
Route::post('/newlogin',[UserController::class,'register']);

Route::get('/register',[UserController::class,'register'])->name('register');
Route::post('/Dm',[DmController::class,'dm']);