<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DmController;
use Illuminate\Http\Request;

Route::prefix('v1/dmlist')->middleware('auth:sanctum')->group(function(){
    Route::get('/',[DmController::class,'dmListback'])->name('dm.list');
    Route::get('dm/{partner}',[DmController::class,'dmback'])->whereNumber('partner'); //読む
    Route::get('dm', [DmController::class,'dmback'])->name('dm.show');
    Route::post('dm', [DmController::class,'dmsendback'])->name('dm.send'); //送る

});
