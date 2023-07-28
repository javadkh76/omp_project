<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChargeController;

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
Route::controller(UserController::class)->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});
Route::controller(CardController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/cards', 'showCards');
    Route::post('/cards', 'addCard');
    Route::delete('/cards/{card}', 'removeCard');
});
Route::controller(ChargeController::class)->middleware('auth:sanctum')->group(function(){
    Route::get('/charges', 'showCharges');
    Route::post('/charges', 'createCharge');
    Route::delete('/charges/{charge}', 'removePendingCharge');
});
Route::any('/charges/verify/{id}', [ChargeController::class, 'verify']);
