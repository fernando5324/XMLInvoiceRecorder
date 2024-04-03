<?php

namespace App\Http\Controllers\Vouchers\Voucher;

use App\Http\Requests\Vouchers\DeleteVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Response;

class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(DeleteVouchersRequest $request): Response
    {

        $id = $request->query('id');

        $result = $this->voucherService->deleteVoucher($id);

        if ($result->message != "") {
            return response(['message' => $result->message], 401);
        }

        return response(['message' => 'Comprobante '. $id.' eliminado.'], 200);

        
    }
}
