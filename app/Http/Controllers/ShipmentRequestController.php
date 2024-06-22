<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentRequest;
use Illuminate\Http\Request;

class ShipmentRequestController extends Controller
{
    /**
     * list shipment requests
     * 
     * @return [json] ShipmentRequest object list for customer
     */
    public function in_request_list(Request $request)
    {
        // $requests = $request->user()->shipments()->with('shipment_request')->where('active', true)->with('shipment.order')->get();
        $requests = ShipmentRequest::where('active', true)->whereHas('shipment', function ($query) use ($request) {
            $query->whereHas('order', function ($inner_query) use ($request) {
                $inner_query->where('user_id', $request->user()->id);
            });
        })->with('shipment.order')->get();
        return response()->json($requests);
    }

    /**
     * list shipment requests for agent
     * 
     * @return [json] ShipmentRequest object list
     */
    public function out_request_list(Request $request)
    {
        $requests = $request->user()->shipment_requests()->with('shipment.order')->get();
        return response()->json($requests);
    }

    /**
     * make shipment request for shipment
     * 
     * @return [json] ShipmentRequest object list
     */
    public function request(Request $request)
    {
        $shipment = Shipment::where('id', $request->shipment_id)->first();
        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }

        $shipment_request = ShipmentRequest::updateOrCreate([
            'agent_id' => $request->user()->id,
            'shipment_id' => $shipment->id
        ], [
            'price' => $request->price,
            'active' => true,
        ]);
        return response()->json([
            'message' => 'Shipment Request placed successfully',
            'shipment_request' => $shipment_request
        ]);
    }

    /**
     * accept shipment request for shipment
     * 
     * @return [json] Response object list
     */
    public function accept_request(Request $request)
    {
        // Find the active shipment request by user and shipment request ID
        $shipmentRequest = ShipmentRequest::where('active', true)
            ->where('id', $request->shipment_request_id)
            ->whereHas('shipment.order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$shipmentRequest) {
            return response()->json(['error' => 'No active shipment request found with the provided detail'], 404);
        }
        $shipmentRequest->accept();

        // Deactivate all other shipment requests for the same shipment
        ShipmentRequest::where('shipment_id', $shipmentRequest->shipment_id)
            ->update(['active' => false]);

        return response()->json([
            'message' => 'Shipment Request accepted successfully',
            'shipment_request' => $shipmentRequest
        ]);
    }
}
