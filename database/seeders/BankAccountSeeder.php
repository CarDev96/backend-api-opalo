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
                'bank_name'      => 'BANREGIO',
                'clabe'          => '058320000001232350',
                'account_number' => '13401246-0011',
                'beneficiary'    => 'SERVICIOS INTEGRALES OPALO',
                'rfc'            => 'SOB151104AS6',
                'instructions'   => 'Favor de ingresar su razón social o folio de orden en el concepto de pago.',
                'is_active'      => true,
            ]
        );

        // 2. Cuenta No Fiscal (Misma cuenta Banregio para ambos casos)
        BankAccount::updateOrCreate(
            ['type' => 'non_fiscal'],
            [
                'bank_name'      => 'BANREGIO',
                'clabe'          => '058320000001232350',
                'account_number' => '13401246-0011',
                'beneficiary'    => 'SERVICIOS INTEGRALES OPALO',
                'rfc'            => 'SOB151104AS6',
                'instructions'   => 'Indicar nombre de la empresa o contacto en la referencia.',
                'is_active'      => true,
            ]
        );
    }
}
