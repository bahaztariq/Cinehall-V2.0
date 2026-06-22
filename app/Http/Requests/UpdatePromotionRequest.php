<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdatePromotionRequest extends FormRequest
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
            'name'                => ['sometimes', 'string', 'max:255'],
            'discount_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'start_date'          => ['sometimes', 'date'],
            'end_date'            => ['sometimes', 'date', 'after_or_equal:start_date'],
        ];
    }
}
