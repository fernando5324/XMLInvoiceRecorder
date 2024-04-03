<?php

namespace App\Http\Controllers\Vouchers\Voucher;

use App\Http\Requests\Vouchers\GetVoucherRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Response;

class GetVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(GetVoucherRequest $request): Response
    {
      
        $vouchers = $this->voucherService->getVoucher(
            $request->query('serie'),
            $request->query('number'),
            ($request->has('initial_date')) ? $request->query('initial_date') : '',
            ($request->has('end_date')) ? $request->query('end_date') : ''
        );
        
        return response([
            'data' => VoucherResource::collection($vouchers),
        ], 200);
    }
}
