<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            // 'company_id' =>  'required',
            // 'invoice_serie' => 'required',
            // 'invoice_number' => 'required',
            // 'invoice_date' => 'required',
            // 'due_date' => 'required',
            // 'payment_method' =>  '',
            // 'payment_term' =>  '',
            // 'status' =>  '',
            // 'total' => 'required',
            // 'tax_amount' => 'required',
            // 'grand_total' => 'required',
            // 'unpaid_amount' =>  '',
            // 'discount' =>  '',
            // 'final_price' => 'required',
            // 'currency' =>  '',
            // 'uuid_sat' =>  '',
            // 'user_id' => 'required',
            // 'cfdi_xml' =>  '',
            // 'acuse_xml' =>  '',
            // 'print_xml' =>  '',
            // 'terms_and_conditions' =>  '',
            // 'vat_amount' =>  '',
            // 'payment_type' => 'required',
            // 'cfdi_use' => 'required',
            // 'iva_included' => 'required',
            // 'exchange_rate' => 'required',
        ];

            
    }
}
