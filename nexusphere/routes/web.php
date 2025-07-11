<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Container\Attributes\Database;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/post',function(){
    return view('post');
});

Route::post('/User',[UserController::class,'register']);