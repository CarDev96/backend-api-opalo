<?php

namespace App\Repositories;

use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;

class BankAccountRepository
{
    /**
     * Obtiene las cuentas activas indexadas por tipo (fiscal y non_fiscal)
     */
    public function getAccountsGrouped(): array
    {
        $accounts = BankAccount::where('is_active', true)->get();

        return [
            'fiscal'     => $accounts->firstWhere('type', 'fiscal'),
            'non_fiscal' => $accounts->firstWhere('type', 'non_fiscal'),
        ];
    }

    /**
     * Guarda o actualiza ambas configuraciones bancarias
     */
    public function savePaymentAccounts(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Guardar Cuenta Fiscal
            $fiscal = BankAccount::updateOrCreate(
                ['type' => 'fiscal'],
                [
                    'bank_name'      => trim($data['fiscal']['bank_name']),
                    'clabe'          => trim($data['fiscal']['clabe']),
                    'beneficiary'    => trim($data['fiscal']['beneficiary']),
                    'rfc'            => strtoupper(trim($data['fiscal']['rfc'] ?? '')),
                    'account_number' => trim($data['fiscal']['account_number'] ?? ''),
                    'instructions'   => trim($data['fiscal']['instructions'] ?? ''),
                    'is_active'      => true,
                ]
            );

            // 2. Guardar Cuenta No Fiscal
            $nonFiscal = BankAccount::updateOrCreate(
                ['type' => 'non_fiscal'],
                [
                    'bank_name'      => trim($data['non_fiscal']['bank_name']),
                    'clabe'          => trim($data['non_fiscal']['clabe']),
                    'beneficiary'    => trim($data['non_fiscal']['beneficiary']),
                    'account_number' => trim($data['non_fiscal']['account_number'] ?? ''),
                    'instructions'   => trim($data['non_fiscal']['instructions'] ?? ''),
                    'is_active'      => true,
                ]
            );

            return [
                'fiscal'     => $fiscal,
                'non_fiscal' => $nonFiscal,
            ];
        });
    }
}
