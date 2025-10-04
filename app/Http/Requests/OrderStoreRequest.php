<?php

namespace App\Http\Requests;

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
            'payment_method' => 'required|string|in:cash,paystack',
            'preferred_vehicle' => 'required|string|in:bike,car,lorry,truck',
            'schedule_type' => 'required|string|in:now,later',
            'schedule_time' => 'nullable|date_format:Y-m-d H:i:s',
            'receiver.first_name' => 'required|string|max:255',
            'receiver.last_name' => 'required|string|max:255',
            'receiver.phone_number' => 'required|string|max:255',
            'receiver.email' => 'required|email|max:255',
            'receiver.address' => 'required|string',
            'pickup.address' => 'required|string',
            'pickup.state' => 'required|string|max:255',
            'pickup.country' => 'required|string|max:255',
            'pickup.latitude' => 'required|numeric',
            'pickup.longitude' => 'required|numeric',
            'delivery.address' => 'required|string',
            'delivery.state' => 'required|string|max:255',
            'delivery.country' => 'required|string|max:255',
            'delivery.latitude' => 'required|numeric',
            'delivery.longitude' => 'required|numeric',
        ];
    }
}
