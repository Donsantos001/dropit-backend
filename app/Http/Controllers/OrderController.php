<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * return the list of orders for a particular user
     * 
     * @return [json] order object list
     */
    public function list(Request $request)
    {
        $user = $request->user();
        return response()->json($user->orders()->with('shipment')->get());
    }

    /**
     * create a new order
     * 
     * @return [json] order object list
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'item_name' => 'required|string',
            'receiver_firstname' => 'required|string',
            'receiver_lastname' => 'required|string',
            'receiver_phone_no' => 'required|string',
            'receiver_email' => 'required|string',
            'delivery_address' => 'required|string',
            'pickup_address' => 'required|string',
            'payment_method' => 'required|string|in:cash,paystack',
            'preferred_vehicle' => 'required|string|in:bike,car,lorry,truck',
            'schedule_type' => 'required|string|in:now,later',
            'schedule_time' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $order = $user->orders()->create([
            'status' => 'pending',
            ...($request->only([
                'item_name',
                'receiver_firstname',
                'receiver_lastname',
                'receiver_phone_no',
                'receiver_email',
                'delivery_address',
                'pickup_address',
                'payment_method',
                'preferred_vehicle',
                'schedule_time',
            ])),
        ]);

        $order->shipment()->create([
            'current_location' => $request->pickup_address,
        ]);

        return response()->json($order, 201);
    }

    /**
     * invalidate order
     * 
     * @return [json] order object list
     */
    public function cancel(Request $request)
    {
        $order = $request->user()->orders()->find($request->order_id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        if ($order->status === "progress") {
            return response()->json(['error' => 'Order is in progress, cannot be cancelled']);
        }
        $order->update(['status' => 'cancelled']);
        return response()->json($order, 200);
    }
}
