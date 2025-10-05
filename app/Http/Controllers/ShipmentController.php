<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Http\Requests\LocationStoreRequest;
use App\Models\Shipment;
use App\Models\ShipmentRequest;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ShipmentController extends Controller
{
    protected Shipment $shipment;

    /**
     * Inject the Shipment model into the controller.
     *
     * @param Shipment $shipment
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }


    /**
     * list shipment
     * 
     * @return [json] order object list
     */
    public function shipment_list(Request $request)
    {
        $user = $request->user();
        $shipments = $user->shipments()->with('order')->get();

        return ResponseBuilder::asSuccess()
            ->withData(['shipments' => $shipments])
            ->build();
    }

    /**
     * view shipment
     */
    public function view_shipment(Request $request, Shipment $shipment)
    {
        if ($shipment->agent_id !== $request->user()->id) {
            return ResponseBuilder::asError(403)
                ->withMessage('You are not authorized to view this shipment')
                ->build();
        }
        $shipment->load(['agent', 'order', 'requests']);

        return ResponseBuilder::asSuccess()
            ->withData(['shipment' => $shipment])
            ->build();
    }

    /**
     * update shipment status
     * 
     * @return [json] shipment object
     */
    public function shipment_status(Request $request, Shipment $shipment)
    {
        $request->validate([
            'status' => 'string|required|in:' . ShipmentStatus::IN_TRANSIT . ',' . ShipmentStatus::DELIVERED,
        ]);
        $shipment = $request->user()->shipments()->where('id', $shipment->id)->first();
        if (!$shipment) {
            return ResponseBuilder::asError(404)
                ->withMessage('Shipment not found')
                ->build();
        }

        $shipment->status = $request->status;
        $shipment->save();

        return ResponseBuilder::asSuccess()
            ->withData(['shipment' => $shipment])
            ->withMessage('Shipment status updated successfully')
            ->build();
    }


    /**
     * update shipment location
     * 
     * @return [json] shipment object
     */
    public function update_location(LocationStoreRequest $request, Shipment $shipment)
    {
        $shipment->current_location->latitude = $request->latitude;
        $shipment->current_location->longitude = $request->longitude;
        $shipment->current_location->address = $request->address;
        $shipment->current_location->state = $request->state;
        $shipment->current_location->country = $request->country;
        $shipment->current_location->save();

        return ResponseBuilder::asSuccess()
            ->withData(['shipment' => $shipment->load('current_location')])
            ->withMessage('Shipment location updated successfully')
            ->build();
    }
}
