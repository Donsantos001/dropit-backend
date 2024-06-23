<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentRequestController;
use App\Http\Controllers\UserController;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [LogoutController::class, 'logout']);
        Route::get('user', [UserController::class, 'user']);
        Route::post('sendotp', [RegisterController::class, 'sendOTP']);
        Route::post('verifyotp', [RegisterController::class, 'verifyOTP']);
    });
});

Route::middleware(['auth:sanctum', 'email-verified'])->group(function () {
    // referral
    Route::get('user/referrals', [UserController::class, 'referred_users']);
    Route::get('user/referred_by', [UserController::class, 'referrer']);

    // order
    Route::get('order', [OrderController::class, 'list']);
    Route::post('order', [OrderController::class, 'store']);
    Route::post('order/invalidate', [OrderController::class, 'cancel']);

    // as customer
    Route::post('shipment/acceptagent', [ShipmentController::class, 'accept_agent']);
    Route::post('shipment/acceptrequest', [ShipmentRequestController::class, 'accept_request']);
    Route::get('shipment/inrequest', [ShipmentRequestController::class, 'in_request_list']);

    // as agent
    Route::get('shipment', [ShipmentController::class, 'shipment_list']);
    Route::post('updateshipment', [ShipmentController::class, 'shipment_status']);
    Route::get('pendingshipment', [ShipmentController::class, 'open_shipments']);
    Route::get('shipment/outrequest', [ShipmentRequestController::class, 'out_request_list']);
    Route::post('shipment/outrequest', [ShipmentRequestController::class, 'request']);
});

Route::get('/test', function (Request $request) {
    return "Yes, Server is up and running";
});
