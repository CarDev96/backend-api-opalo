<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Cuenta Fiscal (Con Factura)
            'fiscal.bank_name'       => 'required|string|max:100',
            'fiscal.clabe'           => 'required|digits:18',
            'fiscal.beneficiary'     => 'required|string|max:150',
            'fiscal.rfc'             => 'required|string|min:12|max:13',
            'fiscal.account_number'  => 'nullable|string|max:30',
            'fiscal.instructions'    => 'nullable|string|max:500',

            // Cuenta No Fiscal (Sin Factura)
            'non_fiscal.bank_name'      => 'required|string|max:100',
            'non_fiscal.clabe'          => 'required|digits:18',
            'non_fiscal.beneficiary'    => 'required|string|max:150',
            'non_fiscal.account_number' => 'nullable|string|max:30',
            'non_fiscal.instructions'   => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fiscal.bank_name.required'   => 'El nombre del banco para la cuenta fiscal es obligatorio.',
            'fiscal.clabe.required'       => 'La CLABE fiscal es obligatoria.',
            'fiscal.clabe.digits'         => 'La CLABE fiscal debe tener exactamente 18 dígitos.',
            'fiscal.beneficiary.required' => 'El beneficiario fiscal es obligatorio.',
            'fiscal.rfc.required'         => 'El RFC emisor para cuenta fiscal es obligatorio.',

            'non_fiscal.bank_name.required'   => 'El banco para la cuenta sin factura es obligatorio.',
            'non_fiscal.clabe.required'       => 'La CLABE sin factura es obligatoria.',
            'non_fiscal.clabe.digits'         => 'La CLABE sin factura debe tener 18 dígitos.',
            'non_fiscal.beneficiary.required' => 'El beneficiario sin factura es obligatorio.',
        ];
    }
}
