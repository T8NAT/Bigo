<?php

use App\Http\Controllers\DiamondRechargeController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\GeideaGatewayController;
use App\Http\Controllers\RechargePrecheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisableRechargeController;

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

Route::post('/recharge', [DiamondRechargeController::class, 'recharge']);
Route::post('/recharge/precheck', [RechargePrecheckController::class, 'precheck']);
Route::post('/recharge/disable', [DisableRechargeController::class, 'disable']);

//Route::controller(GatewayController::class)->group(function (){
//    Route::post('/payment','payOrder');
//    Route::post('/success','successPayment');
//    Route::get('/error','errorPayment');
//});

Route::controller(GeideaGatewayController::class)->group(function(){

    Route::post('/payment','payOrder');

});
