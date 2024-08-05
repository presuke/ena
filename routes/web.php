<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('login', [LoginController::class, 'destroy'])->middleware('auth');
Route::post('regist', [Logoutcontroller::class, 'destroy'])->middleware('auth');

Route::get('/register', [RegisterController::class, 'create']);
Route::get('/logout', [Logoutcontroller::class, 'destroy'])->middleware('auth');


Route::get('/', function () {
    return view('welcome');
});

Route::get('/index', function () {
    return view('index');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
