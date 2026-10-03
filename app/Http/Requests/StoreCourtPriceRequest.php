<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_id' => ['required', 'exists:courts,id'],
            'day_type' => ['required', 'string', 'in:weekday,weekend,all'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
