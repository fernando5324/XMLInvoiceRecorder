<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Illuminate\Http\Response;

class RestoreVoucherValues
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(): Response
    {
        $restoredItems = $this->voucherService->restoreVouchersValues();
        $meesage = "Ningun comprobante fue restaurado.";

        if (count($restoredItems) != 0) {
            $meesage = "Los siguientes comprobantes fueron restaurados: " . implode(" , ", $restoredItems);
        }

        return response($meesage, 200);

    }
}
