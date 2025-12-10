<?php

namespace App\Http\Requests\LayerGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreLayerGroupRequest extends FormRequest
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
            'name' => 'required|string|max:225',
            'parent_layer_group_id' => 'sometimes|nullable|int',
            'layers' => 'sometimes|nullable|array',
        ];
    }
}
