<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Http\Requests\LocationStoreRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LocationController extends Controller
{
    protected Location $location;

    /**
     * Inject the User model into the controller.
     *
     * @param Location $location
     */
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * get user location
     * 
     * @return [json] location object
     */
    public function get_location(Request $request)
    {
        $location = $request->user()->location->first();

        return ResponseBuilder::asSuccess()
            ->withData(['location' => $location])
            ->build();
    }

    /**
     * store user location
     * @return [json] location object
     */
    public function store_location(LocationStoreRequest $request)
    {
        $user = $request->user();
        $location = new $this->location();
        $location->user_id = $user->id;
        $location->type = $request->type ?? LocationType::CURRENT->value;
        $location->latitude = $request->latitude;
        $location->longitude = $request->longitude;
        $location->address = $request->address;
        $location->state = $request->state;
        $location->country = $request->country;
        $location->save();

        return ResponseBuilder::asSuccess()
            ->withData(['location' => $location])
            ->withMessage('Location saved successfully')
            ->build();
    }

    /**
     * update user location
     * 
     * @return [json] location object
     */
    public function update_location(LocationStoreRequest $request, Location $location)
    {
        $user = $request->user();
        $location = $user->location->first();
        if (!$location) {
            $location = new $this->location();
            $location->user_id = $user->id;
            $location->type = $request->type ?? LocationType::CURRENT->value;
        }
        $location->latitude = $request->latitude;
        $location->longitude = $request->longitude;
        $location->address = $request->address;
        $location->state = $request->state;
        $location->country = $request->country;
        $location->save();

        return ResponseBuilder::asSuccess()
            ->withData(['location' => $location])
            ->withMessage('Location updated successfully')
            ->build();
    }
}
