<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cuenta Fiscal (Con Factura / CFDI)
        BankAccount::updateOrCreate(
            ['type' => 'fiscal'],
            [
                'bank_name'      => 'BBVA México',
                'clabe'          => '012320001155998842',
                'account_number' => '0115599884',
                'beneficiary'    => 'Ópalo Certificados de Confianza S.A. de C.V.',
                'rfc'            => 'OPC200415AB3',
                'instructions'   => 'Favor de ingresar su razón social o folio de solicitud en el concepto de pago.',
                'is_active'      => true,
            ]
        );

        // 2. Cuenta No Fiscal (Sin Factura / Pago Directo Operativo)
        BankAccount::updateOrCreate(
            ['type' => 'non_fiscal'],
            [
                'bank_name'      => 'Santander México',
                'clabe'          => '014320655088223319',
                'account_number' => '5579091234567890',
                'beneficiary'    => 'Carlos Azael Montejo Vázquez',
                'rfc'            => null,
                'instructions'   => 'Indicar nombre de la empresa o contacto en la referencia.',
                'is_active'      => true,
            ]
        );
    }
}
