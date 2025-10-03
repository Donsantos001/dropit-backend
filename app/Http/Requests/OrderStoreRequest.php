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
            'receiver_firstname' => 'required|string',
            'receiver_lastname' => 'required|string',
            'receiver_phone_no' => 'required|string',
            'receiver_email' => 'required|string',
            'delivery_address' => 'required|string',
            'pickup_address' => 'required|string',
            'payment_method' => 'required|string|in:cash,paystack',
            'preferred_vehicle' => 'required|string|in:bike,car,lorry,truck',
            'schedule_type' => 'required|string|in:now,later',
            'schedule_time' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
