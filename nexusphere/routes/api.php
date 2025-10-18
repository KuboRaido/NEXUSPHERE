<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DmController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function(){
    Route::get('dmlist',[DmController::class,'dmlistback'])->name('dm.list'); // ← 認証なし
});
Route::middleware('auth:sanctum')->prefix('v1')->group(function(){
    Route::get('dm/{partner}',[DmController::class,'dmback'])->whereNumber('partner'); //読む
    Route::get('dm', [DmController::class,'dmback'])->name('dm.show');
    Route::post('dm', [DmController::class,'dmsendback'])->name('dm.send'); //送る
    Route::post('dm/{partner}/read',[DmController::class,'read']);
    Route::get('profile',[ProfileController::class,'profileBack']); 

});
