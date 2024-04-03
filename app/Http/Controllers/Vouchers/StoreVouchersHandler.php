<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Resources\Vouchers\VoucherResource;
use App\Listeners\SaveVoucher;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $xmlFiles = $request->file('files');

            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                $xmlContents[] = file_get_contents($xmlFile->getRealPath());
            }

            $user = auth()->user();

            $details['xmlContents'] = $xmlContents;
            $details['user'] = $user;

            $vouchers = dispatch(new SaveVoucher($details));

            return response([
                #'data' => VoucherResource::collection($vouchers),
                'message' => "Comprobantes registrados para ser guardados.",
            ], 201);
        } catch (Exception $exception) {
            return response(['message' => $exception->getMessage()], 400);
        }
    }
}
