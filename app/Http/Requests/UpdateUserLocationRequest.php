<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'address' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:100',
            'rw' => 'nullable|string|max:10',
        ];
    }
}
