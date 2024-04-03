<?php

use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use App\Http\Controllers\Vouchers\Voucher\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\Voucher\GetVoucherHandler;
use App\Http\Controllers\Vouchers\GetVoucherTotalMont;
use App\Http\Controllers\Vouchers\RestoreVoucherValues;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::post('/', StoreVouchersHandler::class);

        Route::get('/search', GetVoucherHandler::class);
        Route::delete('/delete/', DeleteVoucherHandler::class);
        Route::get('/restore_values', RestoreVoucherValues::class);

        Route::get('/total_mont', GetVoucherTotalMont::class);
    }
);
