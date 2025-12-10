<?php

namespace App\Http\Requests\Layer;

use Illuminate\Foundation\Http\FormRequest;

class StoreLayerRequest extends FormRequest
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
            'namespace' => 'required|string|max:225',
            'geometry' => 'required_if:writeable,true|string|max:30',
            'geometryType' => 'required_if:writeable,true|string|max:30',
            'properties' => 'required_if:writeable,true|array',
            'datasource' => 'sometimes|nullable|string|max:30',
            'writeable' => 'required|boolean',
            'autoload' => 'required|boolean',
            'default_style_id' => 'sometimes|nullable|int',
            'select_style_id' => 'sometimes|nullable|int',
            'layer_group_id' => 'sometimes|nullable|int',
            'zindex' => 'sometimes|nullable|int|min:0|max:9',
        ];
    }
}
