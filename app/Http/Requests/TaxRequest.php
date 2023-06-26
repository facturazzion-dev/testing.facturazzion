<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => "required",
            'percentage' => "required",
            // 'quantity_on_hand' => "required|integer",
            // 'quantity_available' => "required|integer|max:$this->quantity_on_hand",
            // 'category_id' => "required",
            // 'product_image_file' => 'image|max:2000',
            'tax_type' => 'required'
        ];
    }

    public function messages()
    {
        return [
          'tax_type.required' => 'The type field is required.'
        ];
    }
}
