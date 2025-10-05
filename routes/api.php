<?php

use App\Http\Controllers\AgentRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderRequestController;
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
    Route::post('sendotp', [RegisterController::class, 'sendOTP']);
    Route::post('verifyotp', [RegisterController::class, 'verifyOTP']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [LogoutController::class, 'logout']);
        Route::get('user', [UserController::class, 'user']);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // referral
    Route::get('user/referrals', [UserController::class, 'referred_users']);
    Route::get('user/referred_by', [UserController::class, 'referrer']);

    // order
    Route::get('order', [OrderController::class, 'list']);
    Route::post('order', [OrderController::class, 'store']);
    Route::get('order/{order}', [OrderController::class, 'show']);
    Route::post('order/{order}/invalidate', [OrderController::class, 'cancel']);

    // order request
    Route::get('order-request', [OrderRequestController::class, 'order_request_list']);
    Route::get('order-request/mine', [OrderRequestController::class, 'my_order_requests']);
    Route::post('order-request/{order}', [OrderRequestController::class, 'create_order_request']);
    Route::put('order-request/{order_request}/update', [OrderRequestController::class, 'update_order_request']);
    Route::put('order-request/{order_request}/close', [OrderRequestController::class, 'close_order_request']);

    // agent request
    Route::get('agent-request', [AgentRequestController::class, 'agent_request_list']);
    Route::get('agent-request/mine', [AgentRequestController::class, 'my_agent_requests']);
    Route::post('agent-request/{agent}/order/{order}', [AgentRequestController::class, 'create_agent_request']);
    Route::put('agent-request/{agent_request}/update', [AgentRequestController::class, 'update_agent_request']);
    Route::put('agent-request/{agent_request}/close', [AgentRequestController::class, 'close_agent_request']);

    // location
    Route::get('location', [LocationController::class, 'get_location']);
    Route::post('location', [LocationController::class, 'store_location']);
    Route::put('location/{location}', [LocationController::class, 'update_current_location']);

    // shipment
    Route::get('shipment', [ShipmentController::class, 'shipment_list']);
    Route::get('shipment/{shipment}', [ShipmentController::class, 'view_shipment']);
    Route::put('shipment/{shipment}/update-location', [ShipmentController::class, 'update_location']);
    Route::put('shipment/{shipment}/update-status', [ShipmentController::class, 'shipment_status']);

});

Route::get('/test', function (Request $request) {
    return "Yes, Server is up and running";
});
