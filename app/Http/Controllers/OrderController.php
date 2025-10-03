<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class OrderController extends Controller
{

    protected Order $order;


    /**
     * Inject the Order model into the controller.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * return the list of orders for a particular user
     * 
     * @return [json] order object list
     */
    public function list(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('shipment')->get();
        return ResponseBuilder::asSuccess()
            ->withData(['orders' => $orders])
            ->build();
    }

    /**
     * create a new order
     * 
     * @return [json] order object list
     */
    public function store(OrderStoreRequest $request)
    {
        $order = new $this->order();
        $order->item_name = $request->item_name;
        $order->receiver_firstname = $request->receiver_firstname;
        $order->receiver_lastname = $request->receiver_lastname;
        $order->receiver_phone_no = $request->receiver_phone_no;
        $order->receiver_email = $request->receiver_email;
        $order->delivery_address = $request->delivery_address;
        $order->pickup_address = $request->pickup_address;
        $order->payment_method = $request->payment_method;
        $order->preferred_vehicle = $request->preferred_vehicle;
        $order->schedule_type = $request->schedule_type;
        $order->schedule_time = $request->schedule_time ?  $request->schedule_time : null;
        $order->status = 'pending';
        $order->user_id = $request->user()->id;
        $order->save();

        $shipment = new Shipment();
        $shipment->current_location = $request->pickup_address;
        $shipment->order_id = $order->id;
        $shipment->save();

        return ResponseBuilder::asSuccess()
            ->withData(['order' => $order])
            ->withMessage('Order created successfully.')
            ->build();
    }

    /**
     * invalidate order
     * 
     * @return [json] order object list
     */
    public function cancel(Request $request, Order $order)
    {
        if ($order->status === "progress") {
            return ResponseBuilder::asError(422)
                ->withMessage('Order is in progress, cannot be cancelled')
                ->build();
        }
        $order->update(['status' => 'cancelled']);
        return ResponseBuilder::asSuccess()
            ->withData(['order' => $order])
            ->withMessage('Order cancelled successfully.')
            ->build();
    }
}
