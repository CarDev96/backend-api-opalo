<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankAccountRequest;
use App\Repositories\BankAccountRepository;
use Illuminate\Http\JsonResponse;

class BankAccountController extends Controller
{
    public function __construct(
        private BankAccountRepository $bankRepo
    ) {}

    /**
     * Obtiene los datos bancarios vigentes para administración y clientes
     */
    public function index(): JsonResponse
    {
        $data = $this->bankRepo->getAccountsGrouped();

        return response()->json($data);
    }

    /**
     * Guarda la configuración de las 2 cuentas de pago (Fiscal / No fiscal)
     */
    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $saved = $this->bankRepo->savePaymentAccounts($request->validated());

        return response()->json([
            'message' => 'Cuentas bancarias configuradas exitosamente.',
            'data'    => $saved,
        ]);
    }
}
