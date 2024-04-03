<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Illuminate\Http\Response;

class GetVoucherTotalMont
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(): Response
    {
        $vouchersTotalMont = $this->voucherService->getVouchersTotalMont();

        return response($vouchersTotalMont, 200);
    }
}
