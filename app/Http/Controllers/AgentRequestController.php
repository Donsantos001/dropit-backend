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
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class AgentRequestController extends Controller
{

    protected User $user;
    protected AgentRequest $agent_request;
    protected Shipment $shipment;
    protected ConnectionInterface $db;


    /**
     * Inject the User model into the controller.
     *
     * @param User $user
     * @param AgentRequest $agent_request
     * @param Shipment $shipment
     */
    public function __construct(ConnectionInterface $db, AgentRequest $agent_request, User $user, Shipment $shipment)
    {
        $this->user = $user;
        $this->agent_request = $agent_request;
        $this->shipment = $shipment;
        $this->db = $db;
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
     * get my agent requests
     * 
     * @return [json] agent request object list
     */
    public function my_agent_requests(Request $request)
    {
        $user = $request->user();
        $agent_requests = AgentRequest::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('order')->get();
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

        if ($agent_request->agent_id  !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to update this request')
                ->build();
        }

        if ($agent_request->status !== RequestStatus::REQUESTED->value) {
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
        $agent_request->status = $request->status;
        $agent_request->save();


        if ($agent_request->status === RequestStatus::ACCEPTED->value) {
            AgentRequest::where('order_id', $agent_request->order_id)
                ->where('id', '!=', $agent_request->id)
                ->update(['status' => RequestStatus::CLOSED->value]);
            OrderRequest::where('order_id', $agent_request->order_id)
                ->update(['status' => RequestStatus::CLOSED->value]);


            if (in_array($agent_request->order->status, [OrderStatus::CREATED->value, OrderStatus::OPEN->value])) {
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
        $this->db->commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Agent request updated successfully')
            ->build();
    }


    /**
     * close an agent request
     * 
     * @return [json] agent request object
     */
    public function close_agent_request(Request $request, AgentRequest $agent_request)
    {
        if ($agent_request->order->user_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to close this agent request')
                ->build();
        }
        if ($agent_request->status !== RequestStatus::REQUESTED->value) {
            return ResponseBuilder::asError(400)
                ->withMessage('You can only close requested agent requests')
                ->build();
        }
        $agent_request->status = RequestStatus::CLOSED;
        $agent_request->save();
        return ResponseBuilder::asSuccess()
            ->withMessage('Agent request closed successfully')
            ->build();
    }
}
