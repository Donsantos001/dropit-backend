<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Enums\ShipmentStatus;
use App\Models\AgentRequest;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class OrderRequestController extends Controller
{
    protected User $user;
    protected OrderRequest $order_request;
    protected Shipment $shipment;

    /**
     * Inject the User model into the controller.
     *
     * @param User $user
     * @param AgentRequest $agent_request
     * @param Shipment $shipment
     */
    public function __construct(OrderRequest $order_request, User $user, Shipment $shipment)
    {
        $this->user = $user;
    }

    /**
     * list order requests
     * 
     * @return [json] agent request object list
     */
    public function order_request_list(Request $request)
    {
        $user = $request->user();
        $order_requests = $user->order_requests()->with('order')->get();
        return ResponseBuilder::asSuccess()
            ->withData(['requests' => $order_requests])
            ->build();
    }

    /**
     * create an order request
     * 
     * @return [json] order request object
     */
    public function create_order_request(Request $request, Order $order)
    {
        if ($order->user_id === $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to request for this order')
                ->build();
        }

        if (!in_array($order->status, [OrderStatus::CREATED, OrderStatus::OPEN])) {
            return ResponseBuilder::asError(400)
                ->withMessage('You can only request for created or open orders')
                ->build();
        }

        $existing_request = OrderRequest::where('user_id', $request->user()->id)
            ->where('order_id', $order->id)
            ->first();
        if ($existing_request) {
            return ResponseBuilder::asError(400)
                ->withMessage('You have already requested for this order')
                ->build();
        }

        $order_request = new $this->order_request();
        $order_request->user_id = $request->user()->id;
        $order_request->order_id = $order->id;
        $order_request->message = $request->message;
        $order_request->status =  RequestStatus::REQUESTED;
        $order_request->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Order request created successfully')
            ->build();
    }

    /**
     * update an order request
     * 
     * @return [json] order request object
     */
    public function update_order_request(Request $request, OrderRequest $order_request)
    {
        if ($order_request->user_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to update this order request')
                ->build();
        }
        if ($order_request->status !== RequestStatus::REQUESTED) {
            return ResponseBuilder::asError(400)
                ->withMessage('You can only update requested order requests')
                ->build();
        }

        if (!in_array($request->status, [RequestStatus::ACCEPTED, RequestStatus::REJECTED])) {
            return ResponseBuilder::asError(400)
                ->withMessage('Invalid status')
                ->build();
        }
        $order_request->status = $request->status;
        $order_request->save();

        if ($order_request->status === RequestStatus::ACCEPTED) {
            $all_order_requests = OrderRequest::where('order_id', $order_request->order_id)
                ->where('id', '!=', $order_request->id)->get();
            $all_agent_requests = AgentRequest::where('order_id', $order_request->order_id)
                ->get();
            foreach ($all_order_requests as $request) {
                $request->status = RequestStatus::CLOSED;
            }
            foreach ($all_agent_requests as $request) {
                $request->status = RequestStatus::CLOSED;
            }
            $all_order_requests->saveAll();
            $all_agent_requests->saveAll();


            if (in_array($order_request->order->status, [OrderStatus::CREATED, OrderStatus::OPEN])) {
                $order_request->order->status = OrderStatus::PROGRESS;
                $order_request->order->save();
            }

            $shipment = new $this->shipment();
            $shipment->order_id = $order_request->order_id;
            $shipment->agent_id = $order_request->user_id;
            $shipment->status = ShipmentStatus::DELAYED;
            $shipment->vehicle_type = $request->vehicle_type ?? $order_request->order->preferred_vehicle;
            $shipment->current_location_id = $order_request->order->pickup_location_id;

            // set agent location when in transit
            // $shipment->current_location_id = $shipment->agent()->location->id;

            $shipment->save();
            $order_request->order->status = OrderStatus::PROGRESS;
            $order_request->order->save();
        }
        return ResponseBuilder::asSuccess()
            ->withMessage('Order request updated successfully')
            ->build();
    }
}
