<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCholesterolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cholesterol_level' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}