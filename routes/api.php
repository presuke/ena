<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//認証系
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');          // 登入 API
    Route::post('register', 'register');    // 註冊 (取得 JWT)
    Route::post('logout', 'logout');        // 登出
    Route::post('refresh', 'refresh');      // 重新生成 JWT
});

// 認証不要なルーティング
Route::prefix('/v1/log')->group(function () {
    Route::get('serverTime', [LogV1::class, 'getServerTime']);
});

Route::prefix('/v1/regist')->group(function () {
    Route::get('getGridPrice', [RegistV1::class, 'getGridPrice']);
    Route::post('readRegistSetting', [RegistV1::class, 'readRegistSetting']);
    Route::post('reportRegistSetting', [RegistV1::class, 'reportRegistSetting']);
    Route::post('recordGridPrice', [RegistV1::class, 'recordGridPrice']);
});


// 認証が必要なルーティング
Route::middleware('auth:api')->group(function () {
    Route::prefix('/v1/log')->group(function () {
        Route::get('getMyHybridInverters', [LogV1::class, 'getMyHybridInverters']);
        Route::get('getHybridInverterDatas', [LogV1::class, 'getHybridInverterDatas']);
        Route::post('write', [LogV1::class, 'write']);
    });

    Route::prefix('/v1/regist')->group(function () {
        Route::get('readRegistSetting', [RegistV1::class, 'readRegistSetting']);
        Route::post('recordSettingHybridInverter', [RegistV1::class, 'recordSettingHybridInverter']);
    });

    Route::get('/auth/confirm', [AuthController::class, 'confirm']);
});
