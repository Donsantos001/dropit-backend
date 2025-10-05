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
    Route::get('order/request', [OrderRequestController::class, 'order_request_list']);
    Route::post('order/{order}/request', [OrderRequestController::class, 'create_order_request']);
    Route::post('order/request/{order_request}/update', [OrderRequestController::class, 'update_order_request']);

    // agent request
    Route::get('agent/request', [AgentRequestController::class, 'agent_request_list']);
    Route::post('agent/{agent}/order/{order}/request', [AgentRequestController::class, 'create_agent_request']);
    Route::post('agent/request/{agent_request}/update', [AgentRequestController::class, 'update_agent_request']);

    // location
    Route::get('location', [LocationController::class, 'get_location']);
    Route::post('location', [LocationController::class, 'store_location']);
    Route::post('location/{location}', [LocationController::class, 'update_current_location']);

    // shipment
    Route::get('shipment', [ShipmentController::class, 'shipment_list']);
    Route::get('shipment/{shipment}', [ShipmentController::class, 'view_shipment']);
    Route::post('shipment/{shipment}/updatelocation', [ShipmentController::class, 'update_location']);
    Route::post('shipment/{shipment}/updatestatus', [ShipmentController::class, 'shipment_status']);


    // as customer
    // Route::post('shipment/acceptagent', [ShipmentController::class, 'accept_agent']);
    // Route::post('shipment/acceptrequest', [ShipmentRequestController::class, 'accept_request']);
    // Route::get('shipment/inrequest', [ShipmentRequestController::class, 'in_request_list']);

    // as agent
    // Route::get('shipment', [ShipmentController::class, 'shipment_list']);
    // Route::post('updateshipment', [ShipmentController::class, 'shipment_status']);
    // Route::get('pendingshipment', [ShipmentController::class, 'open_shipments']);
    // Route::get('shipment/outrequest', [ShipmentRequestController::class, 'out_request_list']);
    // Route::post('shipment/outrequest', [ShipmentRequestController::class, 'request']);
});

Route::get('/test', function (Request $request) {
    return "Yes, Server is up and running";
});
