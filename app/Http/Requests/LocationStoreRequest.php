<?php

namespace App\Http\Requests;

use App\Enums\LocationType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class LocationStoreRequest extends FormRequest
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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'type' => 'sometimes|string|in:' . LocationType::CURRENT->value . ',' . LocationType::DESTINATION->value,
        ];
    }
}
