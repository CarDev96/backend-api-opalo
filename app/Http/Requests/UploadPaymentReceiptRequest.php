<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'receipt'         => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120', // Máximo 5MB
            'bank_account_id' => 'required|uuid|exists:bank_accounts,id',
            'amount'          => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required'         => 'Debes adjuntar el archivo de comprobante.',
            'receipt.mimes'            => 'Solo se permiten archivos en formato PDF, JPG o PNG.',
            'receipt.max'              => 'El archivo no debe exceder los 5 MB.',
            'bank_account_id.required' => 'Debes seleccionar la cuenta a la que realizaste el depósito.',
            'bank_account_id.exists'   => 'La cuenta bancaria seleccionada no es válida.',
        ];
    }
}
