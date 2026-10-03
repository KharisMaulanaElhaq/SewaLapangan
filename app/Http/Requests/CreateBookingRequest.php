<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_id' => ['required', 'exists:courts,id'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'court_id.required' => 'Lapangan wajib dipilih.',
            'court_id.exists' => 'Lapangan yang dipilih tidak ditemukan.',
            'slots.required' => 'Minimal satu slot waktu harus dipilih.',
            'slots.min' => 'Minimal satu slot waktu harus dipilih.',
            'slots.*.date' => 'Format waktu slot tidak valid.',
        ];
    }
}
