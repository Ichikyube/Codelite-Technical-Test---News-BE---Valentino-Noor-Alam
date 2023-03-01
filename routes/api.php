<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\NewsController;
use App\Http\Controllers\API\V1\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/login',[AuthController::class, 'login'])->name('userLogin');
Route::get('/me', [AuthController::class, 'me'])->middleware("auth:sanctum");
Route::get('/logout',[AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/gantipassword/{token}',[AuthController::class, 'gantipassword'])->name('newpassword');

Route::get('/', [NewsController::class,'index']);
Route::prefix('news')->controller(NewsController::class)
    ->middleware('auth:sanctum')
    ->group(function(){
    Route::post('/post', 'store');
    Route::get('/edit/{id}', 'edit');
    Route::post('/update/{id}', 'update');
    Route::post('/delete/{id}', 'destroy');
});