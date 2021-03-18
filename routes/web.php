<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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
    return view('landing');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/forgot_password', function () {
    return view('forgot_password');
});


Route::post('/register', 'AuthController@doRegister');
Route::post('/login', 'AuthController@doLogin')->name('login');
Route::post('/forgot_password', 'AuthController@doPasswordRecovery');
Route::get('/reset-password/{token}', function ($token) {
    return view('reset_password', ['token' => $token]);
})->middleware('guest')->name('password.reset');


Route::get('/dashboard', 'PagesController@dashboard');


Route::get('/profile', function () {
    return view('profile');
});
Route::get('/logout', 'AuthController@doLogout');

Route::post('/graph_data', 'PagesController@getData');
Route::get('/wallets', 'PagesController@showWallets');
Route::post('/wallet_actions', 'PagesController@doWalletActions');
Route::post('/contact_us', 'PagesController@contactUs');


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', 'AuthController@handleGoogleCallback');

Route::get('/auth/fb/redirect', function () {
    return Socialite::driver('facebook')->redirect();
});

Route::get('/auth/fb/callback', 'AuthController@handleFBCallback');


Route::get('/privacy', function () {
    return view('privacy');
});
Route::get('/tos', function () {
    return view('tos');
});

Route::get('/settings', 'PagesController@showSettings');
Route::post('/settings', 'PagesController@updateSettings');
