<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class GetVoucherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'serie' => ['required', 'string'],
            'number' => ['required', 'int', 'gt:0'],
        ];
    }
}
