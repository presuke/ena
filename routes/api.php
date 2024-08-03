<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Room as RoomV1;
use App\Http\Controllers\v1\Player as PlayerV1;
use App\Http\Controllers\v1\Play as PlayV1;
use App\Http\Controllers\v1\Log as LogV1;

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

Route::prefix('/v1')->group(function () {
    Route::prefix('/room')->group(function () {
        Route::get('getAll', [RoomV1::Class, 'getAll']);
        Route::get('get', [RoomV1::Class, 'get']);
        Route::post('create', [RoomV1::Class, 'create']);
        Route::post('remove', [RoomV1::Class, 'remove']);
        Route::post('restart', [RoomV1::Class, 'restart']);
    });
    Route::prefix('/player')->group(function () {
        Route::post('create', [PlayerV1::Class, 'create']);
    });
    Route::prefix('/log')->group(function () {
        Route::get('serverTime', [LogV1::Class, 'getServerTime']);
        Route::post('write', [LogV1::Class, 'write']);
    });
});
