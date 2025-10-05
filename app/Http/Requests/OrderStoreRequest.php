<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\ScheduleType;
use App\Enums\VehicleType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_name' => 'required|string',
            'payment_method' => 'required|string|in:' . PaymentMethod::CASH->value . ',' . PaymentMethod::CARD->value . ',' . PaymentMethod::TRANSFER->value,
            'preferred_vehicle' => 'required|string|in:' . VehicleType::MOTORCYCLE->value . ',' . VehicleType::CAR->value . ',' . VehicleType::BUS->value . ',' . VehicleType::TRUCK->value . ',' . VehicleType::LORRY->value,
            'schedule_type' => 'required|string|in:' . ScheduleType::NOW->value . ',' . ScheduleType::LATER->value,
            'schedule_time' => 'nullable|date_format:Y-m-d H:i:s',
            'recipient.first_name' => 'required|string|max:255',
            'recipient.last_name' => 'required|string|max:255',
            'recipient.phone_number' => 'required|string|max:255',
            'recipient.email' => 'required|email|max:255',
            'recipient.address' => 'required|string',
            'pickup_location.address' => 'required|string',
            'pickup_location.state' => 'required|string|max:255',
            'pickup_location.country' => 'required|string|max:255',
            'pickup_location.latitude' => 'required|numeric',
            'pickup_location.longitude' => 'required|numeric',
            'delivery_location.address' => 'required|string',
            'delivery_location.state' => 'required|string|max:255',
            'delivery_location.country' => 'required|string|max:255',
            'delivery_location.latitude' => 'required|numeric',
            'delivery_location.longitude' => 'required|numeric',
        ];
    }
}
