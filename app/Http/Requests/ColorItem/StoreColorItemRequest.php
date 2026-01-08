<?php

namespace App\Http\Requests\ColorItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'color_group_id' => 'required|exists:color_groups,id',
            'label' => 'required|string|max:30',
            'color' => 'required|string|max:30',
        ];
    }
}
