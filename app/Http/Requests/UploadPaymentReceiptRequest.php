<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'        => ['required', 'uuid', 'exists:evaluation_orders,id'],
            'receipt'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:15360'],
            'bank_account_id' => ['nullable', 'uuid', 'exists:bank_accounts,id'],
            'amount'          => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'La orden de evaluación es requerida.',
            'order_id.exists'   => 'La orden de evaluación no existe.',
            'receipt.required'  => 'El comprobante bancario es obligatorio.',
            'receipt.mimes'     => 'El formato debe ser PDF, JPG, PNG o WEBP.',
            'receipt.max'       => 'El archivo no debe exceder 15MB.',
        ];
    }
}
