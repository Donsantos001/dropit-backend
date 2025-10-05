<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Enums\OrderStatus;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Location;
use App\Models\Order;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class OrderController extends Controller
{

    protected Recipient $recipient;
    protected Order $order;
    protected Location $location;


    /**
     * Inject the Order model into the controller.
     *
     * @param Order $order
     */
    public function __construct(Order $order, Recipient $recipient, Location $location)
    {
        $this->order = $order;
        $this->recipient = $recipient;
        $this->location = $location;
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
        $recipient = new $this->recipient();
        $recipient->first_name = $request->recipient['first_name'];
        $recipient->last_name = $request->recipient['last_name'];
        $recipient->phone_number = $request->recipient['phone_number'];
        $recipient->email = $request->recipient['email'];
        $recipient->address = $request->recipient['address'];
        $recipient->user_id = $request->user()->id;
        $recipient->save();

        $pickup = new $this->location();
        $pickup->address = $request->pickup_location['address'];
        $pickup->state = $request->pickup_location['state'];
        $pickup->country = $request->pickup_location['country'];
        $pickup->latitude = $request->pickup_location['latitude'];
        $pickup->longitude = $request->pickup_location['longitude'];
        $pickup->type = LocationType::PICKUP;
        $pickup->user_id = $request->user()->id;
        $pickup->save();

        $delivery = new $this->location();
        $delivery->address = $request->delivery_location['address'];
        $delivery->state = $request->delivery_location['state'];
        $delivery->country = $request->delivery_location['country'];
        $delivery->latitude = $request->delivery_location['latitude'];
        $delivery->longitude = $request->delivery_location['longitude'];
        $delivery->type = LocationType::DELIVERY;
        $delivery->user_id = $request->user()->id;
        $delivery->save();


        $order = new $this->order();
        $order->item_name = $request->item_name;
        $order->item_weight = $request->item_weight;
        $order->item_description = $request->item_description;
        $order->preferred_vehicle = $request->preferred_vehicle;
        $order->status = OrderStatus::CREATED->value;
        $order->schedule_type = $request->schedule_type;
        $order->schedule_time = $request->schedule_time ?  Carbon::parse($request->schedule_time) : now();

        $order->payment_method = $request->payment_method;
        $order->price = $request->price ?? 2300;
        $order->paid = false;

        $order->recipient_id = $recipient->id;
        $order->pickup_location_id = $pickup->id;
        $order->delivery_location_id = $delivery->id;
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
        $order->status = OrderStatus::CANCELLED;
        $order->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Order cancelled successfully.')
            ->build();
    }
}
