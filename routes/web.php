<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',  function () {
    return view('login');
});

Route::get('/register',  function () {
    return view('register');
});

Route::get('/forgot_password',  function () {
    return view('forgot_password');
});


Route::post('/register',  'AuthController@doRegister');
Route::post('/login',  'AuthController@doLogin');
Route::post('/forgot_password',  'AuthController@doPasswordRecovery');
Route::get('/reset-password/{token}', function ($token) {
    return view('reset_password', ['token' => $token]);
})->middleware('guest')->name('password.reset');
