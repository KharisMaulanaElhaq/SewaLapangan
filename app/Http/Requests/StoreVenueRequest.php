<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'dp_percent' => ['required', 'integer', 'between:10,100'],
            'cancel_free_hours' => ['required', 'integer', 'min:0'],
            'cancel_refund_percent' => ['required', 'integer', 'between:0,100'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'dp_percent.between' => 'Persentase DP harus berada di antara 10% dan 100%.',
            'cancel_refund_percent.between' => 'Persentase pengembalian DP harus berada di antara 0% dan 100%.',
        ];
    }
}
