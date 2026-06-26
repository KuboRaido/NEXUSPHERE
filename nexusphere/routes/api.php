<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CircleController;

Route::middleware('auth:sanctum')->prefix('v1')->group(function(){
    Route::get('users/search',[UserController::class,'search'])->name('users.search'); // ← ユーザー検索追加
    Route::get('circle',[CircleController::class,'circleback'])->name('circle.back');
    Route::get('circle/{circle}/dm',[DmController::class,'dmCircleBack'])->name('dm.circle');
    Route::get('group/{group}/dm',[DmController::class,'dmGroup'])->name('dm.group'); // グループ会話取得
    Route::get('dm/{partner}',[DmController::class,'dmback'])->whereNumber('partner'); //読む
    Route::get('dm', [DmController::class,'dmback'])->name('dm.show');
    Route::get('friends',[UserController::class,'group']);
    Route::get('dmlist',[DmController::class,'dmlistback'])->name('dm.list');
    Route::get('group',[UserController::class,'groupAssign'])->name('groupAssign');
    Route::post('dm', [DmController::class,'dmsendback'])->name('dm.send'); //送る
    Route::post('dm/createRoom',[DmController::class,'dmGroupCreate'])->name('dm.Group'); // パスをJSに合わせて修正
    Route::post('dm/{partner}/read',[DmController::class,'read']);
    Route::post('circle/{circle}/join',[CircleController::class,'join']);
    Route::post('circle/{circle}/dm',[DmController::class,'read']);
    Route::post('group/{group}/dm',[DmController::class,'read']);
    Route::post('dm/group',[DmController::class,'dmGroupJoin'])->name('groupJoin');
    Route::post('/circle/{circle}/edit/update',[CircleController::class,'update'])->name('circle-update');//プロフィール更新処理
    Route::post('/profile/edit/update',[ProfileController::class,'update'])->name('profile-update');//プロフィール更新処理
});