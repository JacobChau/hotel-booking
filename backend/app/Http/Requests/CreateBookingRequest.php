<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'title' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'booking_number' => 'sometimes|string|unique:bookings,booking_number',
            'status' => 'sometimes|string|in:upcoming,confirmed,cancelled,past',
            'total' => 'sometimes|numeric|min:0',
            'nights' => 'sometimes|integer|min:1',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
