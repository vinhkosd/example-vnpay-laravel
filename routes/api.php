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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'payment', 'as'=>'payment.'], function ($router) {
    Route::get('createOrder', ['as' => 'createOrder', 'uses' => 'PaymentController@createOrder']);
    Route::get('processOrder', 'PaymentController@processOrder');
});