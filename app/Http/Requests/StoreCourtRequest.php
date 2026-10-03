<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'exists:venues,id'],
            'name' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'string', 'in:futsal,badminton,basketball,tennis,mini_soccer,volleyball'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
