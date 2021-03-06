<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/login', function (Request $request) {
   \Illuminate\Support\Facades\Log::debug(print_r($request->all(),true));
   return response()->json(['error'=>false]);
});


Route::prefix('android')->group(function () {
    Route::post('/login', 'AuthController@loginAndroid' );
    Route::middleware('auth:sanctum')->get('/wallets', 'PagesController@showWalletsAndroid' );

});
