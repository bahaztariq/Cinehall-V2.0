<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
