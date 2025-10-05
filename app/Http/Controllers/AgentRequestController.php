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

class AgentRequestController extends Controller
{

    protected User $user;
    protected AgentRequest $agent_request;
    protected Shipment $shipment;

    /**
     * Inject the User model into the controller.
     *
     * @param User $user
     * @param AgentRequest $agent_request
     * @param Shipment $shipment
     */
    public function __construct(AgentRequest $agent_request, User $user, Shipment $shipment)
    {
        $this->user = $user;
        $this->agent_request = $agent_request;
        $this->shipment = $shipment;
    }

    /**
     * list agent requests
     * 
     * @return [json] agent request object list
     */
    public function agent_request_list(Request $request)
    {
        $user = $request->user();
        $agent_requests = $user->agent_requests()->with('order')->get();

        return ResponseBuilder::asSuccess()
            ->withData(['requests' => $agent_requests])
            ->build();
    }

    /**
     * create an agent request
     * 
     * @return [json] agent request object
     */
    public function create_agent_request(Request $request, User $agent, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to request an agent for this order')
                ->build();
        }

        if ($order->user_id === $agent->id) {
            return ResponseBuilder::asError(400)
                ->withMessage('You cannot request yourself as an agent')
                ->build();
        }

        $existing_request = AgentRequest::where('agent_id', $agent->id)
            ->where('order_id', $order->id)
            ->first();
        if ($existing_request) {
            return ResponseBuilder::asError(400)
                ->withMessage('You have already requested this agent')
                ->build();
        }

        $agent_request = new $this->agent_request();
        $agent_request->agent_id = $agent->id;
        $agent_request->order_id = $order->id;
        $agent_request->status = RequestStatus::REQUESTED;
        $agent_request->message = $request->message;
        $agent_request->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Agent request created successfully')
            ->build();
    }

    /**
     * update agent request status i.e accept or reject
     * 
     * @return [json] agent request object
     */
    public function update_agent_request(Request $request, AgentRequest $agent_request)
    {
        if ($agent_request->agent_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to update this request')
                ->build();
        }

        if ($agent_request->status !== RequestStatus::REQUESTED) {
            return ResponseBuilder::asError(400)
                ->withMessage('You can only update requested order requests')
                ->build();
        }

        if (!in_array($request->status, [RequestStatus::ACCEPTED, RequestStatus::REJECTED])) {
            return ResponseBuilder::asError(400)
                ->withMessage('Invalid status')
                ->build();
        }

        $agent_request->status = $request->status;
        $agent_request->save();


        if ($agent_request->status === RequestStatus::ACCEPTED) {
            $all_agent_requests = AgentRequest::where('order_id', $agent_request->order_id)
                ->where('id', '!=', $agent_request->id)->get();
            $all_order_requests = OrderRequest::where('order_id', $agent_request->order_id)
                ->get();
            foreach ($all_agent_requests as $request) {
                $request->status = RequestStatus::CLOSED;
            }
            foreach ($all_order_requests as $request) {
                $request->status = RequestStatus::CLOSED;
            }
            $all_agent_requests->saveAll();
            $all_order_requests->saveAll();

            if (in_array($agent_request->order->status, [OrderStatus::CREATED, OrderStatus::OPEN])) {
                $agent_request->order->status = OrderStatus::PROGRESS;
                $agent_request->order->save();
            }

            $shipment = new $this->shipment();
            $shipment->order_id = $agent_request->order_id;
            $shipment->agent_id = $agent_request->agent_id;
            $shipment->status = ShipmentStatus::DELAYED;
            $shipment->vehicle_type = $request->vehicle_type ?? $agent_request->order->preferred_vehicle;
            $shipment->current_location_id = $agent_request->order->pickup_location_id;

            // set agent location when in transit
            // $shipment->current_location_id = $shipment->agent()->location->id;

            $shipment->save();
            $agent_request->order->status = OrderStatus::PROGRESS;
            $agent_request->order->save();
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Agent request updated successfully')
            ->build();
    }
}
