<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Container\Attributes\Database;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dbtest', [DatabaseController::class,'index']);

Route::get('/dm',function(){
    return view('dm');
});