<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBloodPressureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blood_pressure_systolic' => ['required', 'integer', 'min:1', 'max:300'],
            'blood_pressure_diastolic' => ['required', 'integer', 'min:1', 'max:300'],
        ];
    }
}