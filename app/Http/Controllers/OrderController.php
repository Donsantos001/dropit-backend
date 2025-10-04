<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class OrderController extends Controller
{

    protected User $user;
    protected Order $order;
    protected Location $location;


    /**
     * Inject the Order model into the controller.
     *
     * @param Order $order
     */
    public function __construct(Order $order, User $user, Location $location)
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
        $orders = $request->user()->orders()
            // ->with('shipment')
            ->get();
        return ResponseBuilder::asSuccess()
            ->withData(['orders' => $orders])
            ->build();
    }

    /** 
     * return the data for a particular order
     * 
     * @param int $id
     * @return [json] order object
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to view this order')
                ->build();
        }
        return ResponseBuilder::asSuccess()
            ->withData(['order' => $order->load('shipment')])
            ->build();
    }

    /**
     * create a new order
     * 
     * @return [json] order object list
     */
    public function store(OrderStoreRequest $request)
    {
        $recipient = new $this->user();
        $recipient->first_name = $request->receiver->first_name;
        $recipient->last_name = $request->receiver->last_name;
        $recipient->phone_number = $request->receiver->phone_number;
        $recipient->email = $request->receiver->email;
        $recipient->address = $request->receiver->address;
        $recipient->user_id = $request->user()->id;
        $recipient->save();

        $pickup_location = new $this->location();
        $pickup_location->address = $request->pickup->address;
        $pickup_location->state = $request->pickup->state;
        $pickup_location->country = $request->pickup->country;
        $pickup_location->latitude = $request->pickup->latitude;
        $pickup_location->longitude = $request->pickup->longitude;
        $pickup_location->user_id = $request->user()->id;
        $pickup_location->save();

        $delivery_location = new $this->location();
        $delivery_location->address = $request->delivery->address;
        $delivery_location->state = $request->delivery->state;
        $delivery_location->country = $request->delivery->country;
        $delivery_location->latitude = $request->delivery->latitude;
        $delivery_location->longitude = $request->delivery->longitude;
        $delivery_location->user_id = $request->user()->id;
        $delivery_location->save();


        $order = new $this->order();
        $order->item_name = $request->item_name;
        $order->preferred_vehicle = $request->preferred_vehicle;
        $order->status = OrderStatus::CREATED->value;
        $order->schedule_type = $request->schedule_type;
        $order->schedule_time = $request->schedule_time ?  $request->schedule_time : null;

        $order->payment_method = $request->payment_method;
        $order->price = $request->price;
        $order->paid = false;

        $order->recipient_id = $recipient->id;
        $order->pickup_location_id = $pickup_location->id;
        $order->delivery_location_id = $delivery_location->id;
        $order->user_id = $request->user()->id;
        $order->save();

        return ResponseBuilder::asSuccess()
            ->withData(['order' => $order])
            ->withMessage('Order created successfully.')
            ->build();
    }

    /**
     * cancel order
     * 
     * @return [json] order object list
     */
    public function cancel(Request $request, Order $order)
    {
        if ($order->status === OrderStatus::PROGRESS->value) {
            return ResponseBuilder::asError(422)
                ->withMessage('Order is in progress, cannot be cancelled')
                ->build();
        }
        $order->update(['status' => OrderStatus::CANCELLED->value]);
        return ResponseBuilder::asSuccess()
            ->withData(['order' => $order])
            ->withMessage('Order cancelled successfully.')
            ->build();
    }
}
