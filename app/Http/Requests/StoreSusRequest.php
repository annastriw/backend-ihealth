<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pakai Auth middleware
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array|size:10',
            'answers.*' => 'required|integer|min:1|max:5'
        ];
    }
}
