<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CircleController;
use App\Http\Controllers\PcrController;

Route::prefix('v1')->group(function(){
    Route::get('dmlist',[DmController::class,'dmlistback'])->name('dm.list'); // ← 認証なし
});
Route::middleware('auth:sanctum')->prefix('v1')->group(function(){
    Route::get('dm/{partner}',[DmController::class,'dmback'])->whereNumber('partner'); //読む
    Route::get('dm', [DmController::class,'dmback'])->name('dm.show');
    Route::post('dm', [DmController::class,'dmsendback'])->name('dm.send'); //送る
    Route::post('dm/{partner}/read',[DmController::class,'read']);
    Route::post('/profile/edit/update',[ProfileController::class,'update'])->name('profile-update');//プロフィール更新処理
    Route::get('users/search',[UserController::class,'search'])->name('users.search'); // ← ユーザー検索追加
    Route::get('circle',[CircleController::class,'circleback'])->name('circle.back');
    Route::get('post',[PcrController::class,'postback'])->name('post.back');
});
