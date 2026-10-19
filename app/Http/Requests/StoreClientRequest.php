<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'            => 'required|string|max:255',
            'trade_name'              => 'nullable|string|max:255',
            'rfc'                     => 'required|string|max:13|unique:clients,rfc',
            'contact_email'           => 'required|email|max:255|unique:users,email',
            'contact_phone'           => 'nullable|string|max:20',
            'fiscal_regime'           => 'nullable|string|max:255',
            'fiscal_zip_code'         => 'nullable|string|max:10',
            'tax_status_certificate'  => 'nullable|file|mimes:pdf|max:5120',
            'requires_purchase_order' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name'            => 'Razón Social',
            'trade_name'              => 'Nombre Comercial',
            'rfc'                     => 'RFC',
            'contact_email'           => 'Correo Electrónico de Contacto',
            'contact_phone'           => 'Teléfono de Contacto',
            'fiscal_regime'           => 'Régimen Fiscal',
            'fiscal_zip_code'         => 'Código Postal Fiscal',
            'tax_status_certificate'  => 'Constancia de Situación Fiscal',
            'requires_purchase_order' => 'Requiere Orden de Compra (PO)',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required'        => 'El campo :attribute es obligatorio.',
            'rfc.required'                 => 'El campo :attribute es obligatorio.',
            'rfc.unique'                   => 'Este :attribute ya se encuentra registrado en el sistema.',
            'contact_email.required'       => 'El campo :attribute es obligatorio.',
            'contact_email.email'          => 'El :attribute debe ser una dirección de correo válida.',
            'contact_email.unique'         => 'Ya existe una cuenta de usuario con este :attribute.',
            'tax_status_certificate.mimes' => 'La :attribute debe ser un archivo en formato PDF.',
            'tax_status_certificate.max'   => 'La :attribute no debe superar los 5 MB.',
        ];
    }
}
