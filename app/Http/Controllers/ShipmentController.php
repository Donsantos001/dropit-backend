<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentRequest;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * list shipment
     * 
     * @return [json] order object list
     */
    public function shipment_list(Request $request)
    {
        $user = $request->user();
        return response()->json($user->shipments()->with('order')->get());
    }

    /**
     * update shipment status
     * 
     * @return [json] shipment object
     */
    public function shipment_status(Request $request)
    {
        $request->validate([
            'status' => 'string|required|in:progress,completed'
        ]);
        $user = $request->user();
        $shipment = Shipment::where('id', $request->shipment_id)->where('agent_id', $request->user()->id)->first();
        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }

        $shipment->order->update(['status' => $request->status]);
        return response()->json($shipment);
    }

    /**
     * get open shipments
     * 
     * @return [json] order object list
     */
    public function open_shipments(Request $request)
    {
        $user_id = $request->user()->id;
        $shipments = Shipment::whereNull('agent_id')->whereHas('order', function ($query) use ($user_id) {
            $query->where('user_id', '!=', $user_id)->where('status', 'pending');
        })->with('order')->get();
        return response()->json($shipments);
    }

    /**
     * assign open shipments to agent
     * 
     * @return [json] shipment object
     */
    public function accept_agent(Request $request)
    {
        $user_id = $request->user()->id;
        $shipment = Shipment::where('id', $request->shipment_id)
            ->whereHas('order', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->first();

        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }
        if ($shipment->order->user->id === $request->agent_id) {
            return response()->json(['error' => 'You cannot accept your own shipment'], 403);
        }

        if ($shipment->order->status !== 'pending') {
            return response()->json(['message' => 'Shipment is no longer available'], 400);
        }
        $order = $shipment->order();
        $order->update(['status' => 'progress']);
        $shipment->update(['agent_id' => $request->agent_id]);

        // make shipment request to this shipment inactive
        ShipmentRequest::where('shipment_id', $shipment->id)
            ->update(['active' => false]);

        return response()->json([
            'message' => 'Shipment assigned successfully',
            'shipment' => $shipment
        ]);
    }

    /**
     * assign open shipments to user
     * 
     * @return [json] shipment object
     */
    public function assign_agent(Request $request)
    {
        $shipment = Shipment::where('id', $request->shipment_id)->where('agent_id', $request->user()->id)->first();
        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }


        if ($shipment->order->status !== 'pending') {
            return response()->json(['message' => 'Shipment is no longer available'], 400);
        }
        $shipment->agent_id = $request->agent_id;
        $shipment->save();
        return response()->json([
            'message' => 'Shipment assigned successfully',
            'shipment' => $shipment
        ]);
    }
}
