<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\v1\Log as LogV1;
use App\Http\Controllers\v1\Regist as RegistV1;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/auth')->group(function () {
    Route::post('regist', [RegisterController::Class, 'store']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/v1')->group(function () {
    Route::prefix('/log')->group(function () {
        Route::get('serverTime', [LogV1::Class, 'getServerTime']);
        Route::post('write', [LogV1::Class, 'write']);
        Route::get('getMyHybridInverterNumbers', [LogV1::Class, 'getMyHybridInverterNumbers']);
        Route::get('getHybridInverterDatas', [LogV1::Class, 'getHybridInverterDatas']);
    });
    Route::prefix('/regist')->group(function () {
        Route::post('read', [RegistV1::Class, 'read']);
    });
});
