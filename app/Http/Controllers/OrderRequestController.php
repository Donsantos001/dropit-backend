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
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class OrderRequestController extends Controller
{
    protected User $user;
    protected OrderRequest $order_request;
    protected Shipment $shipment;
    protected Order $order;
    protected AgentRequest $agent_request;
    protected ConnectionInterface $db;

    /**
     * Inject the User model into the controller.
     *
     * @param User $user
     * @param AgentRequest $agent_request
     * @param Shipment $shipment
     * @param Order $order
     * @param OrderRequest $order_request
     */
    public function __construct(ConnectionInterface $db, OrderRequest $order_request, User $user, Shipment $shipment, Order $order, AgentRequest $agent_request)
    {
        $this->user = $user;
        $this->order_request = $order_request;
        $this->shipment = $shipment;
        $this->order = $order;
        $this->agent_request = $agent_request;
        $this->db = $db;
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
     * get my order requests (on my order)
     * 
     * @return [json] agent request object list
     */
    public function my_order_requests(Request $request)
    {
        $user = $request->user();
        // $order_requests = OrderRequest::whereHas('order', function ($query) use ($user) {
        //     $query->where('user_id', $user->id);
        // })->with('order')->get();
        $order_requests = $user->orders()->with('order_requests')->get()->pluck('order_requests')->flatten();

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

        if (!in_array($order->status, [OrderStatus::CREATED->value, OrderStatus::OPEN->value])) {
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
            ->withData(['request' => $order_request])
            ->build();
    }

    /**
     * update an order request
     * 
     * @return [json] order request object
     */
    public function update_order_request(Request $request, OrderRequest $order_request)
    {
        if ($order_request->user_id === $request->user()->id) {
            return ResponseBuilder::asError(403)
            ->withMessage('You are not authorized to update this order request')
            ->build();
        }
        if ($order_request->status !== RequestStatus::REQUESTED->value) {
            return ResponseBuilder::asError(400)
            ->withMessage('You can only update requested order requests')
            ->build();
        }
        
        if (!in_array($request->status, [RequestStatus::ACCEPTED->value, RequestStatus::REJECTED->value])) {
            return ResponseBuilder::asError(400)
            ->withMessage('Invalid status')
            ->build();
        }
        $this->db->beginTransaction();
        $order_request->status = $request->status;
        $order_request->save();

        if ($order_request->status === RequestStatus::ACCEPTED->value) {
            OrderRequest::where('order_id', $order_request->order_id)
                ->where('id', '!=', $order_request->id)
                ->update(['status' => RequestStatus::CLOSED->value]);

            AgentRequest::where('order_id', $order_request->order_id)
                ->update(['status' => RequestStatus::CLOSED->value]);


            if (in_array($order_request->order->status, [OrderStatus::CREATED->value, OrderStatus::OPEN->value])) {
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
        $this->db->commit();
        return ResponseBuilder::asSuccess()
            ->withMessage('Order request updated successfully')
            ->build();
    }

    /**
     * close an order request
     * 
     * @return [json] order request object
     */
    public function close_order_request(Request $request, OrderRequest $order_request)
    {
        if ($order_request->user_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to close this order request')
                ->build();
        }
        if ($order_request->status !== RequestStatus::REQUESTED->value) {
            return ResponseBuilder::asError(400)
                ->withMessage('You can only close requested order requests')
                ->build();
        }
        $order_request->status = RequestStatus::CLOSED;
        $order_request->save();
        return ResponseBuilder::asSuccess()
            ->withMessage('Order request closed successfully')
            ->build();
    }
}
